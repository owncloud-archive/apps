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
 * Apache_Solr_Response Unit Test
 */
class Apache_Solr_ResponseTest extends PHPUnit_Framework_TestCase
{
	static public function get0Response($createDocuments = true, $collapseSingleValueArrays = true)
	{
		return new Apache_Solr_Response(Apache_Solr_HttpTransport_ResponseTest::get0Response(), $createDocuments, $collapseSingleValueArrays);
	}

	static public function get200Response($createDocuments = true, $collapseSingleValueArrays = true)
	{
		return new Apache_Solr_Response(Apache_Solr_HttpTransport_ResponseTest::get200Response(), $createDocuments, $collapseSingleValueArrays);
	}

	static public function get200ResponseWithDocuments($createDocuments = true, $collapseSingleValueArrays = true)
	{
		return new Apache_Solr_Response(Apache_Solr_HttpTransport_ResponseTest::get200ResponseWithDocuments(), $createDocuments, $collapseSingleValueArrays);
	}

	static public function get400Response($createDocuments = true, $collapseSingleValueArrays = true)
	{
		return new Apache_Solr_Response(Apache_Solr_HttpTransport_ResponseTest::get400Response(), $createDocuments, $collapseSingleValueArrays);
	}

	static public function get404Response($createDocuments = true, $collapseSingleValueArrays = true)
	{
		return new Apache_Solr_Response(Apache_Solr_HttpTransport_ResponseTest::get404Response(), $createDocuments, $collapseSingleValueArrays);
	}

	public function testConstuctorWithValidBodyAndHeaders()
	{
		$fixture = self::get200Response();

		// check that we parsed the HTTP status correctly
		$this->assertEquals(Apache_Solr_HttpTransport_ResponseTest::STATUS_CODE_200, $fixture->getHttpStatus());

		// check that we received the body correctly
		$this->assertEquals(Apache_Solr_HttpTransport_ResponseTest::BODY_200, $fixture->getRawResponse());

		// check that our defaults are correct
		$this->assertEquals(Apache_Solr_HttpTransport_ResponseTest::ENCODING_200, $fixture->getEncoding());
		$this->assertEquals(Apache_Solr_HttpTransport_ResponseTest::MIME_TYPE_200, $fixture->getType());
	}

	public function testConstructorWithBadBodyAndHeaders()
	{
		$fixture = self::get0Response();

		// check that our defaults are correct
		$this->assertEquals(0, $fixture->getHttpStatus());
		$this->assertEquals("UTF-8", $fixture->getEncoding());
		$this->assertEquals("text/plain", $fixture->getType());
	}

	public function testMagicGetWithValidBodyAndHeaders()
	{
		$fixture = self::get200Response();

		// test top level gets
		$this->assertType('stdClass', $fixture->responseHeader);
		$this->assertEquals(0, $fixture->responseHeader->status);
		$this->assertEquals(0, $fixture->responseHeader->QTime);

		$this->assertType('stdClass', $fixture->response);
		$this->assertEquals(0, $fixture->response->numFound);

		$this->assertTrue(is_array($fixture->response->docs));
		$this->assertEquals(0, count($fixture->response->docs));
	}

	/**
	 * @expectedException Apache_Solr_ParserException
	 */
	public function testMagicGetWith0Response()
	{
		$fixture = self::get0Response();

		// attempting to magic get a part of the response
		// should throw a ParserException
		$fixture->responseHeader;

		$this->fail("Expected Apache_Solr_ParserException was not raised");
	}

	/**
	 * @expectedException Apache_Solr_ParserException
	 */
	public function testMagicGetWith400Response()
	{
		$fixture = self::get400Response();

		// attempting to magic get a part of the response
		// should throw a ParserException
		$fixture->responseHeader;

		$this->fail("Expected Apache_Solr_ParserException was not raised");
	}

	/**
	 * @expectedException Apache_Solr_ParserException
	 */
	public function testMagicGetWith404Response()
	{
		$fixture = self::get404Response();

		// attempting to magic get a part of the response
		// should throw a ParserException
		$fixture->responseHeader;

		$this->fail("Expected Apache_Solr_ParserException was not raised");
	}

	public function testCreateDocuments()
	{
		$fixture = self::get200ResponseWithDocuments();

		$this->assertTrue(count($fixture->response->docs) > 0, 'There are not 1 or more documents, cannot test');
		$this->assertType('Apache_Solr_Document', $fixture->response->docs[0], 'The first document is not of type Apache_Solr_Document');
	}
	
	public function testDontCreateDocuments()
	{
		$fixture = self::get200ResponseWithDocuments(false);

		$this->assertTrue(count($fixture->response->docs) > 0, 'There are not 1 or more documents, cannot test');
		$this->assertType('stdClass', $fixture->response->docs[0], 'The first document is not of type stdClass');
	}
	
	public function testGetHttpStatusMessage()
	{
		$fixture = self::get200Response();
		
		$this->assertEquals("OK", $fixture->getHttpStatusMessage());
	}
	
	public function testMagicGetReturnsNullForUndefinedData()
	{
		$fixture = self::get200Response();
		
		$this->assertNull($fixture->doesnotexist);
	}
	
	public function testMagicIssetForDefinedProperty()
	{
		$fixture = self::get200Response();
		
		$this->assertTrue(isset($fixture->responseHeader));
	}
	
	public function testMagicIssetForUndefinedProperty()
	{
		$fixture = self::get200Response();
		
		$this->assertFalse(isset($fixture->doesnotexist));
	}
}
