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

use fkooman\oauth\rs\RemoteResourceServer;
use fkooman\oauth\rs\RemoteResourceServerException;

class RemoteResourceServerTest extends PHPUnit_Framework_TestCase
{
    private $_dataPath;

    public function setUp()
    {
        $this->_dataPath = "file://" . dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "data/";
    }

    public function testBasicToken()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        $rs = new RemoteResourceServer($config);
        $introspection = $rs->verifyRequest(array("Authorization" => "Bearer 001"), array());
        $this->assertEquals("fkooman", $introspection->getSub());
        $this->assertEquals("testclient", $introspection->getClientId());
        $this->assertEquals(1766377846, $introspection->getExpiresAt());
        $this->assertEquals(1366376612, $introspection->getIssuedAt());
        $this->assertEquals("foo bar", $introspection->getScope());
        $this->assertEquals(array("urn:x-foo:service:access","urn:x-bar:privilege:admin"), $introspection->getEntitlement());
        $this->assertTrue($introspection->getActive());
    }

    public function testBasicTokenNoEntitlement()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        $rs = new RemoteResourceServer($config);
        $introspection = $rs->verifyRequest(array(), array("access_token" => "002"));
        $this->assertEquals("frko", $introspection->getSub());
        $this->assertEquals("testclient", $introspection->getClientId());
        $this->assertEquals(1766377846, $introspection->getExpiresAt());
        $this->assertEquals(1366376612, $introspection->getIssuedAt());
        $this->assertEquals("a b c", $introspection->getScope());
        $this->assertFalse($introspection->getEntitlement());
        $this->assertTrue($introspection->getActive());
    }

    public function testInvalidToken()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        try {
            $rs = new RemoteResourceServer($config);
            $introspection = $rs->verifyRequest(array(), array("access_token" => "003"));
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
            $this->assertEquals("invalid_token", $e->getMessage());
            $this->assertEquals("the token is not active", $e->getDescription());
            $this->assertEquals(401, $e->getResponseCode());
            $this->assertEquals('Bearer realm="Resource Server",error="invalid_token",error_description="the token is not active"', $e->getAuthenticateHeader());
        }
    }

    public function testInvalidIntrospectionResponse()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        try {
            $rs = new RemoteResourceServer($config);
            $introspection = $rs->verifyRequest(array("Authorization" => "Bearer 100"), array());
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
            $this->assertEquals("internal_server_error", $e->getMessage());
            $this->assertEquals("unexpected response from introspection endpoint", $e->getDescription());
            $this->assertEquals(500, $e->getResponseCode());
            $this->assertNull($e->getAuthenticateHeader());
        }
    }

    public function testNoJsonResponse()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        try {
            $rs = new RemoteResourceServer($config);
            $introspection = $rs->verifyRequest(array("Authorization" => "Bearer 101"), array());
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
            $this->assertEquals("internal_server_error", $e->getMessage());
            $this->assertEquals("unable to decode response from introspection endpoint", $e->getDescription());
            $this->assertEquals(500, $e->getResponseCode());
            $this->assertNull($e->getAuthenticateHeader());
        }
    }

    public function testMultipleBearerTokens()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        try {
            $rs = new RemoteResourceServer($config);
            $introspection = $rs->verifyRequest(array("Authorization" => "Bearer 003"), array("access_token" => "003"));
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
            $this->assertEquals("invalid_request", $e->getMessage());
            $this->assertEquals("more than one method for including an access token used", $e->getDescription());
            $this->assertEquals(400, $e->getResponseCode());
            $this->assertEquals('Bearer realm="Resource Server",error="invalid_request",error_description="more than one method for including an access token used"', $e->getAuthenticateHeader());
        }
    }

    public function testExt()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        $rs = new RemoteResourceServer($config);
        $introspection = $rs->verifyRequest(array("Authorization" => "Bearer 004"), array());
        $this->assertTrue($introspection->getActive());
        $this->assertEquals(array("uid" => array("admin"), "schacHomeOrganization" => array("localhost")), $introspection->getExt());
    }

    public function testNoBearerTokens()
    {
        $config = array(
            "introspectionEndpoint" => $this->_dataPath,
        );
        try {
            $rs = new RemoteResourceServer($config);
            $introspection = $rs->verifyRequest(array(), array());
            $this->assertTrue(FALSE);
        } catch (RemoteResourceServerException $e) {
            $this->assertEquals("no_token", $e->getMessage());
            $this->assertEquals("missing token", $e->getDescription());
            $this->assertEquals(401, $e->getResponseCode());
            $this->assertEquals('Bearer realm="Resource Server"', $e->getAuthenticateHeader());
        }
    }
}
