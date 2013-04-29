<?php

/**
 *  Copyright 2012 François Kooman <fkooman@tuxed.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OAuth;

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

    /**
     * Verify the Authorization Bearer token.
     *
     * Note: this only works on Apache as the PHP function
     * "apache_request_headers" is used. On other web servers, or when using
     * a framework, please use the verifyAuthorizationHeader function instead
     * where you can directly specify the contents of the Authorization header.
     */
    public function verifyRequest()
    {
        $apacheHeaders = apache_request_headers();
        $headerKeys = array_keys($apacheHeaders);
        $keyPositionInArray = array_search(strtolower("Authorization"), array_map('strtolower', $headerKeys));
        $authorizationHeader = (FALSE !== $keyPositionInArray) ? $apacheHeaders[$headerKeys[$keyPositionInArray]] : NULL;
        $this->verifyAuthorization($authorizationHeader, $_GET);
    }

    public function verifyAuthorization($authorizationHeader = NULL, array $queryParameters = NULL)
    {
        // FIXME: only one authorization mechanism should be allowed
        if (NULL !== $authorizationHeader) {
            $this->verifyAuthorizationHeader($authorizationHeader);

            return;
        }
        if (array_key_exists('access_token', $queryParameters)) {
            $this->verifyQueryParameter($queryParameters);

            return;
        }
        $this->_handleException("no_token", "no access token provided");
    }

    public function verifyQueryParameter(array $queryParameters)
    {
        if (!array_key_exists('access_token', $queryParameters)) {
            $this->_handleException("no_token", "no access token in query parameter");
        }
        $this->verifyBearerToken($queryParameters['access_token']);
    }

    /**
     * Verify the Authorization Bearer token.
     *
     * @param $authorizationHeader The actual content of the Authorization
     * header, e.g.: "Bearer abcdef"
     */
    public function verifyAuthorizationHeader($authorizationHeader)
    {
        if (NULL === $authorizationHeader) {
            $this->_handleException("no_token", "no authorization header");
        }
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        $b64TokenRegExp = '(?:[[:alpha:][:digit:]-._~+/]+=*)';
        $result = preg_match('|^Bearer (?P<value>' . $b64TokenRegExp . ')$|', $authorizationHeader, $matches);
        if ($result === FALSE || $result === 0) {
            $this->_handleException("invalid_token", "the access token is malformed");
        }
        $accessToken = $matches['value'];
        $this->verifyBearerToken($accessToken);
    }

    public function verifyBearerToken($accessToken)
    {
        $getParameters = array();
        $getParameters["access_token"] = $accessToken;

        $curlChannel = curl_init();

        $tokenInfoUrl = $this->_getRequiredConfigParameter("tokenInfoEndpoint");
        if (0 !== strpos($tokenInfoUrl, "file://")) {
            $separator = (FALSE === strpos($tokenInfoUrl, "?")) ? "?" : "&";
            $tokenInfoUrl .= $separator . http_build_query($getParameters);
        } else {
            // file cannot have query parameter, use accesstoken as file instead
            $tokenInfoUrl .= $accessToken . ".json";
        }
        curl_setopt_array($curlChannel, array (
            CURLOPT_URL => $tokenInfoUrl,
            //CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));

        $output = curl_exec($curlChannel);

        if (FALSE === $output) {
            $error = curl_error($curlChannel);
            $this->_handleException("internal_server_error", "cURL error while talking to tokenInfoEndpoint: $error");
        }

        $httpCode = curl_getinfo($curlChannel, CURLINFO_HTTP_CODE);
        curl_close($curlChannel);

        if (0 !== strpos($tokenInfoUrl, "file://")) {
            // not a file
            if (200 !== $httpCode) {
                $this->_handleException("invalid_token", "the access token is not valid");
            }
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
        if (!array_key_exists('eduPersonEntitlement', $this->_resourceOwnerAttributes)) {
            return array();
        }

        return $this->_resourceOwnerAttributes['eduPersonEntitlement'];
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

    /**
     * At least one of the scopes should be granted
     *
     * @param array $scope the list of scopes of which one
     *                                       should be granted
     * @throws RemoteResourceServerException if not at least one of the provided
     *                                       scopes was granted
     */
    public function requireAnyScope(array $scope)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        foreach ($scope as $s) {
            if (TRUE === $this->hasScope($s)) {
                return;
            }
        }
        $this->_handleException("insufficient_scope", "no permission for this call with granted scope");
    }

    public function hasEntitlement($entitlement)
    {
        if (!$this->_isVerified) {
            $this->_handleException("internal_server_error", "verify method needs to be requested first");
        }
        if (!array_key_exists('eduPersonEntitlement', $this->_resourceOwnerAttributes)) {
            return FALSE;
        }

        return in_array($entitlement, $this->_resourceOwnerAttributes['eduPersonEntitlement']);
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

        $resourceServerRealm = array_key_exists("resourceServerRealm", $this->_config) ? $this->_config["resourceServerRealm"] : "Resource Server";

        $content = json_encode(array("error" => $message, "error_description" => $description));
        $authenticateHeader = NULL;

        if (500 !== $responseCode) {
            if ("no_token" === $message) {
                // no authorization header is a special case, the client did not know
                // authentication was required, so tell it now without giving error message
                $authenticateHeader = 'Bearer realm="' . $resourceServerRealm . '"';
            } else {
                $authenticateHeader = sprintf('Bearer realm="' . $resourceServerRealm . '",error="%s",error_description="%s"', $message, $description);
            }
        }

        // we can either throw an exception in case a framework is used, or just
        // directly handle the response to the client ourselves...
        if (array_key_exists("throwException", $this->_config) && $this->_config["throwException"]) {
            $e = new RemoteResourceServerException($message, $description);
            $e->setResponseCode($responseCode);
            $e->setAuthenticateHeader($authenticateHeader);
            $e->setContent($content);
            throw $e;
        } else {
            header("HTTP/1.1 " . $responseCode);
            header("WWW-Authenticate: " . $authenticateHeader);
            header("Content-Type: application/json");
            die($content);
        }
    }

}

class RemoteResourceServerException extends \Exception
{
    private $_description;
    private $_responseCode;
    private $_authenticateHeader;
    private $_content;

    public function __construct($message, $description, $code = 0, Exception $previous = null)
    {
        $this->_description = $description;
        parent::__construct($message, $code, $previous);
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setResponseCode($responseCode)
    {
        $this->_responseCode = $responseCode;
    }

    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    public function setAuthenticateHeader($authenticateHeader)
    {
        $this->_authenticateHeader = $authenticateHeader;
    }

    public function getAuthenticateHeader()
    {
        return $this->_authenticateHeader;
    }

    public function setContent($content)
    {
        $this->_content = $content;
    }

    public function getContent()
    {
        return $this->_content;
    }

}
