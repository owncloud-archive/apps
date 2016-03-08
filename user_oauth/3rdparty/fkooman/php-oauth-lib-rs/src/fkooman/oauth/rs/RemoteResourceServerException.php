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

class RemoteResourceServerException extends \Exception
{
    private $description;
    private $responseCode;
    private $realm;

    public function __construct($message, $description, $code = 0, Exception $previous = null)
    {
       switch ($message) {
            case "no_token":
            case "invalid_token":
                $this->responseCode = 401;
                break;
            case "insufficient_scope":
            case "insufficient_entitlement":
                $this->responseCode = 403;
                break;
            case "internal_server_error":
                $this->responseCode = 500;
                break;
            case "invalid_request":
            default:
                $this->responseCode = 400;
                break;
        }

        $this->description = $description;
        $this->realm = "Resource Server";

        parent::__construct($message, $code, $previous);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setRealm($resourceServerRealm)
    {
        $this->realm = (is_string($resourceServerRealm) && !empty($resourceServerRealm)) ? $resourceServerRealm : "Resource Server";
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    public function getAuthenticateHeader()
    {
        $authenticateHeader = NULL;
        if (500 !== $this->responseCode) {
            if ("no_token" === $this->message) {
                // no authorization header is a special case, the client did not know
                // authentication was required, so tell it now without giving error message
                $authenticateHeader = sprintf('Bearer realm="%s"', $this->realm);
            } else {
                $authenticateHeader = sprintf('Bearer realm="%s",error="%s",error_description="%s"', $this->realm, $this->message, $this->description);
            }
        }

        return $authenticateHeader;
    }

    public function getContent()
    {
        return json_encode(array("error" => $this->message, "error_description" => $this->description));
    }

}
