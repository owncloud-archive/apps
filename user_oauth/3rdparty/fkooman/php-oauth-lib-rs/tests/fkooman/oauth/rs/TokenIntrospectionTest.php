<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'vendor/autoload.php';

use fkooman\oauth\rs\TokenIntrospection;
use fkooman\oauth\rs\RemoteResourceServerException;

class TokenIntrospectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validTokenProvider
     */
    public function testTokenIntrospectionTest($token, $active, $expiresAt, $issuedAt, $scope, $entitlement, $clientId, $sub, $aud)
    {
        $i = new TokenIntrospection($token);
        $this->assertEquals($active, $i->getActive());
        $this->assertEquals($expiresAt, $i->getExpiresAt());
        $this->assertEquals($issuedAt, $i->getIssuedAt());
        $this->assertEquals($scope, $i->getScope());
        if (FALSE !== $i->getScope()) {
            $eScope = explode(" ", $i->getScope());
            $this->assertEquals($eScope, $i->getScopeAsArray());
            for ( $j = 0; $j < count($eScope); $j++) {
                $this->assertTrue($i->hasScope($eScope[$j]));
                $this->assertTrue($i->hasAnyScope(array($eScope[$j], "bogus")));
                $i->requireScope($eScope[$j]);
            }
        }
        $e = $i->getEntitlement();
        if (FALSE !== $e) {
            for ( $j = 0; $j < count($e); $j++) {
                $this->assertTrue($i->hasEntitlement($e[$j]));
                $i->requireEntitlement($e[$j]);
            }
        }
        $this->assertEquals($clientId, $i->getClientId());
        $this->assertEquals($sub, $i->getResourceOwnerId());
        $this->assertEquals($sub, $i->getSub());
        $this->assertEquals($aud, $i->getAud());
        try {
            $i->requireScope("bogus");
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
        }
        try {
            $i->requireEntitlement("bogus");
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
        }

        $this->assertFalse($i->hasAnyScope(array("foo")));
    }

    public function validTokenProvider()
    {
        $iat = time();
        $exp = $iat + 100;

        return array(
            array(
                array("active" => TRUE),
                TRUE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE
            ),

            array(
                array("active" => TRUE, "exp" => $exp, "iat" => $iat, "scope" => "read write", "client_id" => "foo", "sub" => "fkooman", "aud" => "foobar"),
                TRUE, $exp, $iat, "read write", FALSE, "foo", "fkooman", "foobar"
            ),

            array(
                array("active" => TRUE, "exp" => $exp, "iat" => $iat, "scope" => "read write", "x-entitlement" => array("manager", "owner", "user"), "client_id" => "foo", "sub" => "fkooman", "aud" => "foobar"),
                TRUE, $exp, $iat, "read write", array("manager", "owner", "user"), "foo", "fkooman", "foobar"
            ),
        );
    }

}
