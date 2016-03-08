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

class TokenIntrospection
{
    private $_response;

    public function __construct(array $response)
    {
        if (!isset($response['active']) || !is_bool($response['active'])) {
            throw new RemoteResourceServerException("internal_server_error", "active key should be set and its value a boolean");
        }

        if (isset($response['exp']) && (!is_int($response['exp']) || 0 > $response['exp'])) {
            throw new RemoteResourceServerException("internal_server_error", "exp value must be positive integer");
        }

        if (isset($response['exp']) && (!is_int($response['iat']) || 0 > $response['iat'])) {
            throw new RemoteResourceServerException("internal_server_error", "iat value must be positive integer");
        }

        if (isset($response['iat'])) {
            if (time() < $response['iat']) {
                throw new RemoteResourceServerException("internal_server_error", "token issued in the future");
            }
        }

        if (isset($response['exp']) && isset($response['iat'])) {
            if ($response['exp'] < $response['iat']) {
                throw new RemoteResourceServerException("internal_server_error", "token expired before it was issued");
            }
        }

        if (isset($response['exp'])) {
            if (time() > $response['exp']) {
                throw new RemoteResourceServerException("invalid_token", "the token expired");
            }
        }

        if (isset($response['x-entitlement']) && !is_array($response['x-entitlement'])) {
            throw new RemoteResourceServerException("internal_server_error", "x-entitlement value must be array");
        }

        $this->_response = $response;
    }

    /**
     * REQUIRED.  Boolean indicator of whether or not the presented
     * token is currently active.
     */
    public function getActive()
    {
        return $this->_response['active'];
    }

    /**
     * OPTIONAL.  Integer timestamp, measured in the number of
     * seconds since January 1 1970 UTC, indicating when this token will
     * expire.
     */
    public function getExpiresAt()
    {
        return $this->_getKeyValue('exp');
    }

    /**
     * OPTIONAL.  Integer timestamp, measured in the number of
     * seconds since January 1 1970 UTC, indicating when this token was
     * originally issued.
     */
    public function getIssuedAt()
    {
        return $this->_getKeyValue('iat');
    }

    /**
     * OPTIONAL.  A space-separated list of strings representing the
     * scopes associated with this token, in the format described in
     * Section 3.3 of OAuth 2.0 [RFC6749].
     */
    public function getScope()
    {
        return $this->_getKeyValue('scope');
    }

    /**
     * OPTIONAL.  Client Identifier for the OAuth Client that
     * requested this token.
     */
    public function getClientId()
    {
        return $this->_getKeyValue('client_id');
    }

    /**
     * OPTIONAL.  Local identifier of the Resource Owner who authorized
     * this token.
     */
    public function getSub()
    {
        return $this->_getKeyValue('sub');
    }

    /**
     * OPTIONAL.  Service-specific string identifier or list of string
     * identifiers representing the intended audience for this token.
     */
    public function getAud()
    {
        return $this->_getKeyValue('aud');
    }

    /**
     * OPTIONAL.  Type of the token as defined in OAuth 2.0
     * section 5.1.
     */
    public function getTokenType()
    {
        return $this->_getKeyValue('token_type');
    }

    private function _getKeyValue($key)
    {
        return isset($this->_response[$key]) ? $this->_response[$key] : FALSE;
    }

    /* ADDITIONAL HELPER METHODS */
    public function getResourceOwnerId()
    {
        return $this->getSub();
    }

    public function getScopeAsArray()
    {
        return FALSE !== $this->getScope() ? explode(" ", $this->getScope()) : FALSE;
    }

    public function hasScope($scope)
    {
        return FALSE !== $this->getScopeAsArray() ? in_array($scope, $this->getScopeAsArray()) : FALSE;
    }

    public function requireScope($scope)
    {
        if (FALSE === $this->hasScope($scope)) {
            throw new RemoteResourceServerException("insufficient_scope", "no permission for this call with granted scope");
        }
    }

    public function requireAnyScope(array $scope)
    {
        if (FALSE === $this->hasAnyScope($scope)) {
            throw new RemoteResourceServerException("insufficient_scope", "no permission for this call with granted scope");
        }
    }

    /**
     * At least one of the scopes should be granted.
     *
     * @param  array $scope the list of scopes of which one should be granted
     * @return TRUE  when at least one of the requested scopes was granted,
     *         FALSE when none were granted.
     */
    public function hasAnyScope(array $scope)
    {
        foreach ($scope as $s) {
            if ($this->hasScope($s)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function getEntitlement()
    {
        return $this->_getKeyValue('x-entitlement');
    }

    public function hasEntitlement($entitlement)
    {
        return FALSE !== $this->getEntitlement() ? in_array($entitlement, $this->getEntitlement()) : FALSE;
    }

    public function requireEntitlement($entitlement)
    {
        if (FALSE === $this->hasEntitlement($entitlement)) {
            throw new RemoteResourceServerException("insufficient_entitlement", "no permission for this call with granted entitlement");
        }
    }

    public function getExt()
    {
        return $this->_getKeyValue('x-ext');
    }

}
