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
 * Apache_Solr_HttpTransport_Response Unit Tests
 */
class Apache_Solr_HttpTransport_ResponseTest extends PHPUnit_Framework_TestCase
{
	// generated with the following query string: select?q=solr&wt=json
	const STATUS_CODE_200 = 200;
	const STATUS_MESSAGE_200 = "OK";
	const BODY_200 = '{"responseHeader":{"status":0,"QTime":0,"params":{"q":"solr","wt":"json"}},"response":{"numFound":0,"start":0,"docs":[]}}';
	const BODY_200_WITH_DOCUMENTS = '{"responseHeader":{"status":0,"QTime":0,"params":{"q":"*:*","wt":"json"}},"response":{"numFound":1,"start":0,"docs":[{"guid":"dev/2/products/45410/1236981","cit_domain":"products","cit_client":"2","cit_instance":"dev","cit_timestamp":"2010-10-06T18:16:51.573Z","product_code_t":["235784"],"product_id":[1236981],"dealer_id":[45410],"category_id":[1030],"manufacturer_id":[0],"vendor_id":[472],"catalog_id":[202]}]}}';
	const CONTENT_TYPE_200 = "text/plain; charset=utf-8";
	const MIME_TYPE_200 = "text/plain";
	const ENCODING_200 = "utf-8";

	// generated with the following query string: select?qt=standad&q=solr&wt=json
	// NOTE: the intentional mispelling of the standard in the qt parameter
	const STATUS_CODE_400 = 400;
	const STATUS_MESSAGE_400 = "Bad Request";
	const BODY_400 = '<html><head><title>Apache Tomcat/6.0.24 - Error report</title><style><!--H1 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:22px;} H2 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:16px;} H3 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:14px;} BODY {font-family:Tahoma,Arial,sans-serif;color:black;background-color:white;} B {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;} P {font-family:Tahoma,Arial,sans-serif;background:white;color:black;font-size:12px;}A {color : black;}A.name {color : black;}HR {color : #525D76;}--></style> </head><body><h1>HTTP Status 400 - unknown handler: standad</h1><HR size="1" noshade="noshade"><p><b>type</b> Status report</p><p><b>message</b> <u>unknown handler: standad</u></p><p><b>description</b> <u>The request sent by the client was syntactically incorrect (unknown handler: standad).</u></p><HR size="1" noshade="noshade"><h3>Apache Tomcat/6.0.24</h3></body></html>';
	const CONTENT_TYPE_400 = "text/html; charset=utf-8";
	const MIME_TYPE_400 = "text/html";
	const ENCODING_400 = "utf-8";
	
	// generated with the following query string: select?q=solr&wt=json on a core that does not exist
	const STATUS_CODE_404 = 404;
	const STATUS_MESSAGE_404 = "Not Found";
	const BODY_404 = '<html><head><title>Apache Tomcat/6.0.24 - Error report</title><style><!--H1 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:22px;} H2 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:16px;} H3 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:14px;} BODY {font-family:Tahoma,Arial,sans-serif;color:black;background-color:white;} B {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;} P {font-family:Tahoma,Arial,sans-serif;background:white;color:black;font-size:12px;}A {color : black;}A.name {color : black;}HR {color : #525D76;}--></style> </head><body><h1>HTTP Status 404 - /solr/doesnotexist/select</h1><HR size="1" noshade="noshade"><p><b>type</b> Status report</p><p><b>message</b> <u>/solr/doesnotexist/select</u></p><p><b>description</b> <u>The requested resource (/solr/doesnotexist/select) is not available.</u></p><HR size="1" noshade="noshade"><h3>Apache Tomcat/6.0.24</h3></body></html>';
	const CONTENT_TYPE_404 = "text/html; charset=utf-8";
	const MIME_TYPE_404 = "text/html";
	const ENCODING_404 = "utf-8";
		
	public static function get0Response()
	{
		return new Apache_Solr_HttpTransport_Response(null, null, null);
	}
	
	public static function get200Response()
	{
		return new Apache_Solr_HttpTransport_Response(self::STATUS_CODE_200, self::CONTENT_TYPE_200, self::BODY_200);
	}
	
	public static function get200ResponseWithDocuments()
	{
		return new Apache_Solr_HttpTransport_Response(self::STATUS_CODE_200, self::CONTENT_TYPE_200, self::BODY_200_WITH_DOCUMENTS);
	}
	
	public static function get400Response()
	{
		return new Apache_Solr_HttpTransport_Response(self::STATUS_CODE_400, self::CONTENT_TYPE_400, self::BODY_400);
	}
	
	public static function get404Response()
	{
		return new Apache_Solr_HttpTransport_Response(self::STATUS_CODE_404, self::CONTENT_TYPE_404, self::BODY_404);
	}
		
	public function testGetStatusCode()
	{
		$fixture = self::get200Response();
		
		$statusCode = $fixture->getStatusCode();
		
		$this->assertEquals(self::STATUS_CODE_200, $statusCode);
	}
	
	public function testGetStatusMessage()
	{
		$fixture = self::get200Response();
		
		$statusMessage = $fixture->getStatusMessage();
		
		$this->assertEquals(self::STATUS_MESSAGE_200, $statusMessage);
	}
	
	public function testGetStatusMessageWithUnknownCode()
	{
		$fixture = new Apache_Solr_HttpTransport_Response(499, null, null);
		
		$statusMessage = $fixture->getStatusMessage();
		$this->assertEquals("Unknown Status", $statusMessage);
	}
	
	public function testGetBody()
	{
		$fixture = self::get200Response();
		
		$body = $fixture->getBody();
		
		$this->assertEquals(self::BODY_200, $body);
	}
	
	public function testGetMimeType()
	{
		$fixture = self::get200Response();
		
		$mimeType = $fixture->getMimeType();
		
		$this->assertEquals(self::MIME_TYPE_200, $mimeType);
	}
	
	public function testGetEncoding()
	{
		$fixture = self::get200Response();
		
		$encoding = $fixture->getEncoding();
		
		$this->assertEquals(self::ENCODING_200, $encoding);
	}
	
	public function testGetStatusMessageWhenNotProvided()
	{
		// test 4 of the most common status code responses, probably don't need
		// to test all the codes we have
		
		$fixture = new Apache_Solr_HttpTransport_Response(null, null, null, null, null);
		$this->assertEquals("Communication Error", $fixture->getStatusMessage(), 'Did not get correct default status message for status code 0');
		
		$fixture = new Apache_Solr_HttpTransport_Response(200, null, null, null, null);
		$this->assertEquals("OK", $fixture->getStatusMessage(), 'Did not get correct default status message for status code 200');
		
		$fixture = new Apache_Solr_HttpTransport_Response(400, null, null, null, null);
		$this->assertEquals("Bad Request", $fixture->getStatusMessage(), 'Did not get correct default status message for status code 400');
		
		$fixture = new Apache_Solr_HttpTransport_Response(404, null, null, null, null);
		$this->assertEquals("Not Found", $fixture->getStatusMessage(), 'Did not get correct default status message for status code 404');
	}
}
