<?php

class RemoteResourceServer
{
    private $_config;

    private $_grantedScope;
    private $_resourceOwnerId;
    private $_resourceOwnerAttributes;
    private $_isVerified;

    public function __construct(array $c)
    {
        $this->_config = $c;

        $this->_resourceOwnerId = NULL;
        $this->_grantedScope = NULL;
        $this->_resourceOwnerAttributes = NULL;
        $this->_isVerified = FALSE;
    }

    public function verifyRequest()
    {
        $apacheHeaders = apache_request_headers();
        $headerKeys = array_keys($apacheHeaders);
        $keyPositionInArray = array_search(strtolower("Authorization"), array_map('strtolower', $headerKeys));
        $authorizationHeader = (FALSE !== $keyPositionInArray) ? $apacheHeaders[$headerKeys[$keyPositionInArray]] : NULL;
        $this->verifyAuthorizationHeader($authorizationHeader);
    }

    public function verifyAuthorizationHeader($authorizationHeader)
    {
        if (NULL === $authorizationHeader) {
            $this->_handleException("no_token", "no authorization header in the request");
        }
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        $b64TokenRegExp = '(?:[[:alpha:][:digit:]-._~+/]+=*)';
        $result = preg_match('|^Bearer (?P<value>' . $b64TokenRegExp . ')$|', $authorizationHeader, $matches);
        if ($result === FALSE || $result === 0) {
            $this->_handleException("invalid_token", "the access token is malformed");
        }
        $accessToken = $matches['value'];

        $getParameters = array();
        $getParameters["access_token"] = $accessToken;

        $curlChannel = curl_init();
        curl_setopt_array($curlChannel, array (
            CURLOPT_URL => $this->_getRequiredConfigParameter("tokenInfoEndpoint") . "?" . http_build_query($getParameters),
            //CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));

        $output = curl_exec($curlChannel);
        $httpCode = curl_getinfo($curlChannel, CURLINFO_HTTP_CODE);
        curl_close($curlChannel);

        if (200 !== $httpCode) {
            $this->_handleException("invalid_token", "the access token is not valid");
        }

        $token = json_decode($output, TRUE);
        if (NULL === $token) {
            $this->_handleException("internal_server_error", "unable to decode the token response from authorization server");
        }

        if ($token['expires_in'] < 0) {
            $this->_handleException("invalid_token", "the access token expired");
        }

        $this->_resourceOwnerId = $token['user_id'];
        $this->_grantedScope = $token['scope'];
        $this->_resourceOwnerAttributes = $token['attributes'];

        $this->_isVerified = TRUE;
    }

    public function getResourceOwnerId()
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }

        return $this->_resourceOwnerId;
    }

    public function getScope()
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        if (NULL === $this->_grantedScope) {
            return array();
        }

        return explode(" ", $this->_grantedScope);
    }

    public function getEntitlement()
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        if (!array_key_exists("entitlement", $this->_resourceOwnerAttributes)) {
            return array();
        }

        return $this->_resourceOwnerAttributes['entitlement'];
    }

    public function hasScope($scope)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        if (NULL === $this->_grantedScope) {
            return FALSE;
        }
        $grantedScope = explode(" ", $this->_grantedScope);
        if (in_array($scope, $grantedScope)) {
            return TRUE;
        }

        return FALSE;
    }

    public function requireScope($scope)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        if (FALSE === $this->hasScope($scope)) {
            $this->_handleException("insufficient_scope", "no permission for this call with granted scope");
        }
    }

    public function hasEntitlement($entitlement)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        if (!array_key_exists("entitlement", $this->_resourceOwnerAttributes)) {
            return FALSE;
        }

        return in_array($entitlement, $this->_resourceOwnerAttributes['entitlement']);
    }

    public function requireEntitlement($entitlement)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }

        if (FALSE === $this->hasEntitlement($entitlement)) {
            $this->_handleException("insufficient_entitlement", "no permission for this call with granted entitlement");
        }
    }

    public function getAttributes()
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }

        return $this->_resourceOwnerAttributes;
    }

    public function getAttribute($key)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        $attributes = $this->getAttributes();

        return array_key_exists($key, $attributes) ? $attributes[$key] : NULL;
    }

    private function _getRequiredConfigParameter($key)
    {
        if (!array_key_exists($key, $this->_config)) {
            $this->_handleException("internal_server_error", "no config parameter '$key'");
        }

        return $this->_config[$key];
    }

    private function _handleException($message, $description)
    {
       switch ($message) {
            case "no_token":
            case "invalid_token":
                $responseCode = 401;
                break;
            case "insufficient_scope":
            case "insufficient_entitlement":
                $responseCode = 403;
                break;
            case "internal_server_error":
                $responseCode = 500;
                break;
            case "invalid_request":
            default:
                $responseCode = 400;
                break;
        }
        header("HTTP/1.1 " . $responseCode);

        if (500 === $responseCode) {
            echo json_encode(array("error" => $message, "error_description" => $description));
        } else {
            if ("no_token" === $message) {
                // no authorization header is a special case, the client did not know
                // authentication was required, so tell it now without giving error message
                $hdr = 'Bearer realm="Resource Server"';
            } else {
                $hdr = sprintf('Bearer realm="Resource Server",error="%s",error_description="%s"', $message, $description);
            }
            header("WWW-Authenticate: $hdr");
            echo json_encode(array("error" => $message, "error_description" => $description));
        }
        // stop executing everything, we are done here
        die();
    }

}
