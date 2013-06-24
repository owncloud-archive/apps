<?php
/**
 * Copyright (c) 2007-2011, Servigistics, Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of Servigistics, Inc. nor the names of
 *    its contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright Copyright 2007-2011 Servigistics, Inc. (http://servigistics.com)
 * @license http://solr-php-client.googlecode.com/svn/trunk/COPYING New BSD
 *
 * @package Apache
 * @subpackage Solr
 * @author Donovan Jimenez <djimenez@conduit-it.com>
 */

/**
 * Apache_Solr_HttpTransport_Abstract Unit Tests
 */
abstract class Apache_Solr_HttpTransport_AbstractTest extends PHPUnit_Framework_TestCase
{	
	const TIMEOUT = 2;
	
	// request our copyright file from googlecode for GET and HEAD
	const GET_URL = "http://solr-php-client.googlecode.com/svn/trunk/COPYING";
	const GET_RESPONSE_MIME_TYPE = 'text/plain';
	const GET_RESPONSE_ENCODING = 'UTF-8';
	const GET_RESPONSE_MATCH = 'Copyright (c) ';
	
	// post to the issue list page with a search for 'meh'
	const POST_URL = "http://code.google.com/p/solr-php-client/issues/list";
	const POST_DATA = "can=2&q=meh&colspec=ID+Type+Status+Priority+Milestone+Owner+Summary&cells=tiles";
	const POST_REQUEST_CONTENT_TYPE = 'application/x-www-form-urlencoded; charset=UTF-8';
	
	const POST_RESPONSE_MIME_TYPE = 'text/html';
	const POST_RESPONSE_ENCODING = 'UTF-8';
	//const POST_RESPONSE_MATCH = 'not sure';
	
	abstract public function getFixture();
	
	public function testGetDefaultTimeoutWithDefaultConstructor()
	{
		$fixture = $this->getFixture();
		$timeout = $fixture->getDefaultTimeout();
		
		$this->assertGreaterThan(0, $timeout);
	}
	
	public function testGetDefaultTimeoutSetToSixtyForBadValues()
	{
		// first set our default_socket_timeout ini setting
		$previousValue = ini_get('default_socket_timeout');
		ini_set('default_socket_timeout', 0);
		
		$fixture = $this->getFixture();
		$timeout = $fixture->getDefaultTimeout();
		
		// reset timeout
		ini_set('default_socket_timeout', $previousValue);
		
		$this->assertEquals(60, $timeout);
	}
	
	public function testSetDefaultTimeout()
	{
		$newTimeout = 1234;
		
		$fixture = $this->getFixture();
		$fixture->setDefaultTimeout($newTimeout);
		$timeout = $fixture->getDefaultTimeout();
		
		$this->assertEquals($newTimeout, $timeout);
	}
	
	public function testPerformGetRequest()
	{
		$fixture = $this->getFixture();
		$fixture->setDefaultTimeout(self::TIMEOUT);
		
		$response = $fixture->performGetRequest(self::GET_URL);
		
		$this->assertType('Apache_Solr_HttpTransport_Response', $response);
		
		$this->assertEquals(200, $response->getStatusCode(), 'Status code was not 200');
		$this->assertEquals(self::GET_RESPONSE_MIME_TYPE, $response->getMimeType(), 'mimetype was not correct');
		$this->assertEquals(self::GET_RESPONSE_ENCODING, $response->getEncoding(), 'character encoding was not correct');
		$this->assertStringStartsWith(self::GET_RESPONSE_MATCH, $response->getBody(), 'body did not start with match text');
	}
	
	public function testPerformGetRequestWithTimeout()
	{
		$fixture = $this->getFixture();
		$response = $fixture->performGetRequest(self::GET_URL, self::TIMEOUT);
		
		$this->assertType('Apache_Solr_HttpTransport_Response', $response);
		
		$this->assertEquals(200, $response->getStatusCode(), 'Status code was not 200');
		$this->assertEquals(self::GET_RESPONSE_MIME_TYPE, $response->getMimeType(), 'mimetype was not correct');
		$this->assertEquals(self::GET_RESPONSE_ENCODING, $response->getEncoding(), 'character encoding was not correct');
		$this->assertStringStartsWith(self::GET_RESPONSE_MATCH, $response->getBody(), 'body did not start with match text');
	}
	
	public function testPerformHeadRequest()
	{
		$fixture = $this->getFixture();
		$fixture->setDefaultTimeout(self::TIMEOUT);
		
		$response = $fixture->performHeadRequest(self::GET_URL);
		
		// we should get everything the same as a get, except the body
		$this->assertType('Apache_Solr_HttpTransport_Response', $response);
		
		$this->assertEquals(200, $response->getStatusCode(), 'Status code was not 200');
		$this->assertEquals(self::GET_RESPONSE_MIME_TYPE, $response->getMimeType(), 'mimetype was not correct');
		$this->assertEquals(self::GET_RESPONSE_ENCODING, $response->getEncoding(), 'character encoding was not correct');
		$this->assertEquals("", $response->getBody(), 'body was not empty');
	}
	
	public function testPerformHeadRequestWithTimeout()
	{
		$fixture = $this->getFixture();
		$response = $fixture->performHeadRequest(self::GET_URL, self::TIMEOUT);
		
		// we should get everything the same as a get, except the body
		$this->assertType('Apache_Solr_HttpTransport_Response', $response);
		
		$this->assertEquals(200, $response->getStatusCode(), 'Status code was not 200');
		$this->assertEquals(self::GET_RESPONSE_MIME_TYPE, $response->getMimeType(), 'mimetype was not correct');
		$this->assertEquals(self::GET_RESPONSE_ENCODING, $response->getEncoding(), 'character encoding was not correct');
		$this->assertEquals("", $response->getBody(), 'body was not empty');
	}
	
	public function testPerformPostRequest()
	{
		$fixture = $this->getFixture();
		$fixture->setDefaultTimeout(self::TIMEOUT);
		
		$response = $fixture->performPostRequest(self::POST_URL, self::POST_DATA, self::POST_REQUEST_CONTENT_TYPE);
		
		$this->assertType('Apache_Solr_HttpTransport_Response', $response);
		
		$this->assertEquals(200, $response->getStatusCode(), 'Status code was not 200');
		$this->assertEquals(self::POST_RESPONSE_MIME_TYPE, $response->getMimeType(), 'mimetype was not correct');
		$this->assertEquals(self::POST_RESPONSE_ENCODING, $response->getEncoding(), 'character encoding was not correct');
		//$this->assertStringStartsWith(self::POST_RESPONSE_MATCH, $response->getBody(), 'body did not start with match text');
	}
	
	public function testPerformPostRequestWithTimeout()
	{
		$fixture = $this->getFixture();
		$response = $fixture->performPostRequest(self::POST_URL, self::POST_DATA, self::POST_REQUEST_CONTENT_TYPE, self::TIMEOUT);
		
		$this->assertType('Apache_Solr_HttpTransport_Response', $response);
		
		$this->assertEquals(200, $response->getStatusCode(), 'Status code was not 200');
		$this->assertEquals(self::POST_RESPONSE_MIME_TYPE, $response->getMimeType(), 'mimetype was not correct');
		$this->assertEquals(self::POST_RESPONSE_ENCODING, $response->getEncoding(), 'character encoding was not correct');
		//$this->assertStringStartsWith(self::POST_RESPONSE_MATCH, $response->getBody(), 'body did not start with match text');
	}
		
	/**
	 * Test one session doing multiple requests in multiple orders
	 */
	public function testMultipleRequests()
	{
		// initial get request
		$this->testPerformGetRequest();
		
		// head following get
		$this->testPerformHeadRequest();
		
		// post following head
		$this->testPerformPostRequest();
		
		// get following post
		$this->testPerformGetRequest();
		
		// post following get
		$this->testPerformPostRequest();
	
		// head following post
		$this->testPerformHeadRequest();
		
		// get following post
		$this->testPerformGetRequest();		
	}
}