<?php

/**
 *  Copyright 2013 François Kooman <fkooman@tuxed.net>
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

namespace fkooman\oauth\rs;

class RemoteResourceServer
{
    private $_config;

    public function __construct(array $c)
    {
        $this->_config = $c;
    }

    public function verifyAndHandleRequest()
    {
        try {
            $headerBearerToken = NULL;
            $queryBearerToken = NULL;

            // look for headers
            if (function_exists("apache_request_headers")) {
                $headers = apache_request_headers();
            } elseif (isset($_SERVER)) {
                $headers = $_SERVER;
            } else {
                $headers = array();
            }

            // look for query parameters
            $query = (isset($_GET) && is_array($_GET)) ? $_GET : array();

            return $this->verifyRequest($headers, $query);

        } catch (RemoteResourceServerException $e) {
            // send response directly to client, halt execution of calling script as well
            $e->setRealm($this->_getConfigParameter("realm", FALSE, "Resource Server"));
            header("HTTP/1.1 " . $e->getResponseCode());
            if (NULL !== $e->getAuthenticateHeader()) {
                // for "internal_server_error" responses no WWW-Authenticate header is set
                header("WWW-Authenticate: " . $e->getAuthenticateHeader());
            }
            header("Content-Type: application/json");
            die($e->getContent());
        }
    }

    public function verifyRequest(array $headers, array $query)
    {
        // extract token from authorization header
        $authorizationHeader = self::_getAuthorizationHeader($headers);
        $ah = FALSE !== $authorizationHeader ? self::_getTokenFromHeader($authorizationHeader) : FALSE;

        // extract token from query parameters
        $aq = self::_getTokenFromQuery($query);

        if (FALSE === $ah && FALSE === $aq) {
            // no token at all provided
            throw new RemoteResourceServerException("no_token", "missing token");
        }
        if (FALSE !== $ah && FALSE !== $aq) {
            // two tokens provided
            throw new RemoteResourceServerException("invalid_request", "more than one method for including an access token used");
        }
        if (FALSE !== $ah) {
            return $this->verifyBearerToken($ah);
        }
        if (FALSE !== $aq) {
            return $this->verifyBearerToken($aq);
        }
    }

    private static function _getAuthorizationHeader(array $headers)
    {
        $headerKeys = array_keys($headers);
        foreach (array("X-Authorization", "Authorization") as $h) {
            $keyPositionInArray = array_search(strtolower($h), array_map('strtolower', $headerKeys));
            if (FALSE === $keyPositionInArray) {
                continue;
            }

            return $headers[$headerKeys[$keyPositionInArray]];
        }

        return FALSE;
    }

    private static function _getTokenFromHeader($authorizationHeader)
    {
        if (0 !== strpos($authorizationHeader, "Bearer ")) {
            return FALSE;
        }

        return substr($authorizationHeader, 7);
    }

    private static function _getTokenFromQuery(array $queryParameters)
    {
        if (!isset($queryParameters) || empty($queryParameters['access_token'])) {
            return FALSE;
        }

        return $queryParameters['access_token'];
    }

    public function verifyBearerToken($token)
    {
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        if ( 1 !== preg_match('|^[[:alpha:][:digit:]-._~+/]+=*$|', $token)) {
            throw new RemoteResourceServerException("invalid_token", "the access token is not a valid b64token");
        }

        $introspectionEndpoint = $this->_getConfigParameter("introspectionEndpoint");
        $get = array("token" => $token);

        if (!function_exists("curl_init")) {
            throw new RemoteResourceServerException("internal_server_error", "php curl module not available");
        }

        $curlChannel = curl_init();
        if (FALSE === $curlChannel) {
            throw new RemoteResourceServerException("internal_server_error", "unable to initialize curl");
        }

        if (0 !== strpos($introspectionEndpoint, "file://")) {
            $separator = (FALSE === strpos($introspectionEndpoint, "?")) ? "?" : "&";
            $introspectionEndpoint .= $separator . http_build_query($get, null, "&");
        } else {
            // file cannot have query parameter, use accesstoken as JSON file instead
            $introspectionEndpoint .= $token . ".json";
        }

        $disableCertCheck = $this->_getConfigParameter("disableCertCheck", false, false);
        if (FALSE === curl_setopt_array($curlChannel, array (
            CURLOPT_URL => $introspectionEndpoint,
            //CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => $disableCertCheck ? 0 : 1,
            CURLOPT_SSL_VERIFYHOST => $disableCertCheck ? 0 : 2,
        ))) {
            throw new RemoteResourceServerException("internal_server_error", "unable to set curl options");
        }

        $output = curl_exec($curlChannel);

        if (FALSE === $output) {
            $error = curl_error($curlChannel);
            throw new RemoteResourceServerException("internal_server_error", sprintf("unable to contact introspection endpoint [%s]", $error));
        }

        $httpCode = curl_getinfo($curlChannel, CURLINFO_HTTP_CODE);
        curl_close($curlChannel);

        if (0 !== strpos($introspectionEndpoint, "file://")) {
            // not a file
            if (200 !== $httpCode) {
                throw new RemoteResourceServerException("internal_server_error", "unexpected response code from introspection endpoint");
            }
        }

        $data = json_decode($output, TRUE);
        $jsonError = json_last_error();
        if (JSON_ERROR_NONE !== $jsonError) {
            throw new RemoteResourceServerException("internal_server_error", "unable to decode response from introspection endpoint");
        }
        if (!is_array($data) || !isset($data['active']) || !is_bool($data['active'])) {
            throw new RemoteResourceServerException("internal_server_error", "unexpected response from introspection endpoint");
        }

        if (!$data['active']) {
            throw new RemoteResourceServerException("invalid_token", "the token is not active");
        }

        return new TokenIntrospection($data);
    }

    private function _getConfigParameter($key, $required = TRUE, $default = NULL)
    {
        if (!array_key_exists($key, $this->_config)) {
            if ($required) {
                throw new RemoteResourceServerException("internal_server_error", "missing required configuration parameter");
            } else {
                return $default;
            }
        }

        return $this->_config[$key];
    }
}
