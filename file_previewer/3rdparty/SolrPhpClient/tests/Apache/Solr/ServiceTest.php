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
 * Apache_Solr_Service Unit Test
 */
class Apache_Solr_ServiceTest extends Apache_Solr_ServiceAbstractTest
{
	public function getFixture()
	{
		return new Apache_Solr_Service();
	}
	
	public function getMockHttpTransportInterface()
	{
		return $this->getMock(
			'Apache_Solr_HttpTransport_Interface',
			array(
				'getDefaultTimeout',
				'setDefaultTimeout',
				'performGetRequest',
				'performHeadRequest',
				'performPostRequest',
			)
		);
	}
	
	//================================================================//
	// ATTEMPT TO MOVE THESE TO ServiceAbstractTest AT SOME POINT     //
	//   Apache_Solr_Service_Balancer will need functions added       //
	//================================================================//
	public function testGetHttpTransportWithDefaultConstructor()
	{
		$fixture = new Apache_Solr_Service();
		
		$httpTransport = $fixture->getHttpTransport();
		
		$this->assertInstanceOf('Apache_Solr_HttpTransport_Interface', $httpTransport, 'Default http transport does not implement interface');
		$this->assertInstanceOf('Apache_Solr_HttpTransport_FileGetContents', $httpTransport, 'Default http transport is not URL Wrapper implementation');
	}
	
	
	public function testSetHttpTransport()
	{
		$newTransport = new Apache_Solr_HttpTransport_Curl();
		$fixture = new Apache_Solr_Service();
		
		$fixture->setHttpTransport($newTransport);
		$httpTransport = $fixture->getHttpTransport();
		
		$this->assertInstanceOf('Apache_Solr_HttpTransport_Interface', $httpTransport);
		$this->assertInstanceOf('Apache_Solr_HttpTransport_Curl', $httpTransport);
		$this->assertEquals($newTransport, $httpTransport);
		
	}
	
	public function testSetHttpTransportWithConstructor()
	{
		$newTransport = new Apache_Solr_HttpTransport_Curl();
		
		$fixture = new Apache_Solr_Service('localhost', 8180, '/solr/', $newTransport);
		
		$fixture->setHttpTransport($newTransport);
		$httpTransport = $fixture->getHttpTransport();
		
		$this->assertInstanceOf('Apache_Solr_HttpTransport_Interface', $httpTransport);
		$this->assertInstanceOf('Apache_Solr_HttpTransport_Curl', $httpTransport);
		$this->assertEquals($newTransport, $httpTransport);
	}

	public function testGetCollapseSingleValueArraysWithDefaultConstructor()
	{
		$fixture = $this->getFixture();
		
		$this->assertTrue($fixture->getCollapseSingleValueArrays());
	}
	
	public function testSetCollapseSingleValueArrays()
	{
		$fixture = $this->getFixture();
		
		$fixture->setCollapseSingleValueArrays(false);
		$this->assertFalse($fixture->getCollapseSingleValueArrays());
	}
	
	public function testGetNamedListTreatmetnWithDefaultConstructor()
	{
		$fixture = $this->getFixture();
		
		$this->assertEquals(Apache_Solr_Service::NAMED_LIST_MAP, $fixture->getNamedListTreatment());
	}
	
	public function testSetNamedListTreatment()
	{
		$fixture = $this->getFixture();
		
		$fixture->setNamedListTreatment(Apache_Solr_Service::NAMED_LIST_FLAT);
		$this->assertEquals(Apache_Solr_Service::NAMED_LIST_FLAT, $fixture->getNamedListTreatment());
		
		$fixture->setNamedListTreatment(Apache_Solr_Service::NAMED_LIST_MAP);
		$this->assertEquals(Apache_Solr_Service::NAMED_LIST_MAP, $fixture->getNamedListTreatment());
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testSetNamedListTreatmentInvalidArgumentException()
	{
		$fixture = $this->getFixture();
		
		$fixture->setNamedListTreatment("broken");
	}
	
	//================================================================//
	// END SECTION OF CODE THAT SHOULD BE MOVED                       //
	//   Apache_Solr_Service_Balancer will need functions added       //
	//================================================================//
	

	public function testConstructorDefaultArguments()
	{
		$fixture = new Apache_Solr_Service();
		
		$this->assertInstanceOf('Apache_Solr_Service', $fixture);
	}

	public function testGetHostWithDefaultConstructor()
	{
		$fixture = new Apache_Solr_Service();
		$host = $fixture->getHost();
		
		$this->assertEquals("localhost", $host);
	}
	
	public function testSetHost()
	{
		$newHost = "example.com";
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHost($newHost);
		$host = $fixture->getHost();
		
		$this->assertEquals($newHost, $host);
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testSetEmptyHost()
	{
		$fixture = new Apache_Solr_Service();
		
		// should throw an invalid argument exception
		$fixture->setHost("");
	}
	
	public function testSetHostWithConstructor()
	{
		$newHost = "example.com";
		
		$fixture = new Apache_Solr_Service($newHost);
		$host = $fixture->getHost();
		
		$this->assertEquals($newHost, $host);
	}
	
	public function testGetPortWithDefaultConstructor()
	{
		$fixture = new Apache_Solr_Service();
		$port = $fixture->getPort();
		
		$this->assertEquals(8180, $port);
	}
	
	public function testSetPort()
	{
		$newPort = 12345;
		
		$fixture = new Apache_Solr_Service();
		$fixture->setPort($newPort);
		$port = $fixture->getPort();
		
		$this->assertEquals($newPort, $port);
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testSetPortWithInvalidArgument()
	{
		$fixture = new Apache_Solr_Service();
		
		$fixture->setPort("broken");
	}
	
	public function testSetPortWithConstructor()
	{
		$newPort = 12345;
		
		$fixture = new Apache_Solr_Service('locahost', $newPort);
		$port = $fixture->getPort();
		
		$this->assertEquals($newPort, $port);
	}
		
	public function testGetPathWithDefaultConstructor()
	{
		$fixture = new Apache_Solr_Service();
		$path = $fixture->getPath();
		
		$this->assertEquals("/solr/", $path);
	}
	
	public function testSetPath()
	{
		$newPath = "/new/path/";
		
		$fixture = new Apache_Solr_Service();
		$fixture->setPath($newPath);
		$path = $fixture->getPath();
		
		$this->assertEquals($path, $newPath);
	}
	
	public function testSetPathWillAddContainingSlashes()
	{
		$newPath = "new/path";
		$containedPath = "/{$newPath}/";
		
		$fixture = new Apache_Solr_Service();
		$fixture->setPath($newPath);
		$path = $fixture->getPath();
		
		$this->assertEquals($containedPath, $path, 'setPath did not ensure propertly wrapped with slashes');
	}
	
	public function testSetPathWithConstructor()
	{
		$newPath = "/new/path/";
		
		$fixture = new Apache_Solr_Service('localhost', 8180, $newPath);
		$path = $fixture->getPath();
		
		$this->assertEquals($newPath, $path);
	}
	
	
	public function testGetDefaultTimeoutCallsThroughToTransport()
	{
		$fixture = new Apache_Solr_Service();
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call
		$mockTransport->expects($this->once())->method('getDefaultTimeout');
		
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->getDefaultTimeout();
	}
	
	public function testSetDefaultTimeoutCallsThroughToTransport()
	{
		$timeout = 12345;
		$fixture = new Apache_Solr_Service();
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call
		$mockTransport->expects($this->once())->method('setDefaultTimeout')->with($this->equalTo($timeout));
		
		$fixture->setHttpTransport($mockTransport);		
		$fixture->setDefaultTimeout($timeout);
	}
	
	public function testPing()
	{
		$expectedUrl = "http://localhost:8180/solr/admin/ping";
		$expectedTimeout = 2;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performHeadRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		// call ping 
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		$time = $fixture->ping();
		
		$this->assertGreaterThan(0, $time);
	}
	
	public function testPingReturnsFalse()
	{
		$expectedUrl = "http://localhost:8180/solr/admin/ping";
		$expectedTimeout = 2;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performHeadRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get0Response()));
		
		// call ping 
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$this->assertFalse($fixture->ping());
	}
	
	public function testThreads()
	{
		$expectedUrl = "http://localhost:8180/solr/admin/threads?wt=json";
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performGetRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		// call threads
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		$fixture->threads();
	}
	
	/**
	 * @expectedException Apache_Solr_HttpTransportException
	 */
	public function testThreads404()
	{
		$expectedUrl = "http://localhost:8180/solr/admin/threads?wt=json";
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performGetRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get404Response()));
		
		// call threads
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		$fixture->threads();
	}
	
	public function testAdd()
	{
		$postData = "does not have to be valid";
		
		$expectedUrl = "http://localhost:8180/solr/update?wt=json";
		$expectedTimeout = false;
		$expectedPostData = $postData;
		$expectedContentType = "text/xml; charset=UTF-8"; // default for _sendRawPost
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($postData), $this->equalTo($expectedContentType), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		// call add
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		$fixture->add($postData);
	}
	
	/**
	 * @expectedException Apache_Solr_HttpTransportException
	 */
	public function testAdd400()
	{
		$postData = "does not have to be valid";
		
		$expectedUrl = "http://localhost:8180/solr/update?wt=json";
		$expectedTimeout = false;
		$expectedPostData = $postData;
		$expectedContentType = "text/xml; charset=UTF-8"; // default for _sendRawPost
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($postData), $this->equalTo($expectedContentType), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get400Response()));
		
		// call add
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		$fixture->add($postData);
	}
	
	public function testAddDocument()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
				
				// raw post
				$this->equalTo('<add allowDups="false" overwritePending="true" overwriteCommitted="true"><doc></doc></add>'),
				
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
				
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$document = new Apache_Solr_Document();
		
		$fixture->addDocument($document);
	}
	
	public function testAddDocumentWithNonDefaultParameters()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
				
				// raw post
				$this->equalTo('<add allowDups="true" overwritePending="false" overwriteCommitted="false" commitWithin="3600"><doc></doc></add>'),
				
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
				
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$document = new Apache_Solr_Document();
		
		$fixture->addDocument($document, true, false, false, 3600);
	}
	
	public function testAddDocumentWithFields()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
			
				// raw post
				$this->equalTo('<add allowDups="false" overwritePending="true" overwriteCommitted="true"><doc><field name="guid">global unique id</field><field name="field">value</field><field name="multivalue">value 1</field><field name="multivalue">value 2</field></doc></add>'),
			
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
			
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$document = new Apache_Solr_Document();

		$document->guid = "global unique id";
		$document->field = "value";
		$document->multivalue = array("value 1", "value 2");
		
		$fixture->addDocument($document);
	}
	
	public function testAddDocumentWithFieldBoost()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
		
				// raw post
				$this->equalTo('<add allowDups="false" overwritePending="true" overwriteCommitted="true"><doc><field name="guid">global unique id</field><field name="field" boost="2">value</field><field name="multivalue" boost="3">value 1</field><field name="multivalue">value 2</field></doc></add>'),
		
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
		
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$document = new Apache_Solr_Document();

		$document->guid = "global unique id";
		
		$document->field = "value";
		$document->setFieldBoost('field', 2);
		
		$document->multivalue = array("value 1", "value 2");
		$document->setFieldBoost('multivalue', 3);
		
		$fixture->addDocument($document);
	}
	
	public function testAddDocumentWithDocumentBoost()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
		
				// raw post
				$this->equalTo('<add allowDups="false" overwritePending="true" overwriteCommitted="true"><doc boost="2"><field name="guid">global unique id</field><field name="field">value</field></doc></add>'),
		
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
		
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$document = new Apache_Solr_Document();
		$document->setBoost(2);

		$document->guid = "global unique id";
		$document->field = "value";
		
		$fixture->addDocument($document);
	}
	
	public function testAddDocuments()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
		
				// raw post
				$this->equalTo('<add allowDups="false" overwritePending="true" overwriteCommitted="true"><doc></doc><doc></doc></add>'),
		
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
		
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$documents = array(
			new Apache_Solr_Document(),
			new Apache_Solr_Document()
		);
		
		$fixture->addDocuments($documents);
	}
	
	public function testAddDocumentsWithNonDefaultParameters()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
				
				// raw post
				$this->equalTo('<add allowDups="true" overwritePending="false" overwriteCommitted="false" commitWithin="3600"><doc></doc><doc></doc></add>'),
				
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
				
				// timeout
				$this->equalTo(false)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$documents = array(
			new Apache_Solr_Document(),
			new Apache_Solr_Document()
		);
		
		$fixture->addDocuments($documents, true, false, false, 3600);
	}
	
	public function testCommit()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
				
				// raw post
				$this->equalTo('<commit expungeDeletes="false" waitFlush="true" waitSearcher="true" />'),
				
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
				
				// timeout
				$this->equalTo(3600)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->commit();
	}
	
	public function testCommitWithNonDefaultParameters()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				// url
				$this->equalTo('http://localhost:8180/solr/update?wt=json'),
				
				// raw post
				$this->equalTo('<commit expungeDeletes="true" waitFlush="false" waitSearcher="false" />'),
				
				// content type
				$this->equalTo('text/xml; charset=UTF-8'),
				
				// timeout
				$this->equalTo(7200)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->commit(true, false, false, 7200);
	}
	
	public function testDelete()
	{
		$postData = "does not have to be valid";
		
		$expectedUrl = "http://localhost:8180/solr/update?wt=json";
		$expectedTimeout = 3600; // default for delete
		$expectedPostData = $postData;
		$expectedContentType = "text/xml; charset=UTF-8"; // default for _sendRawPost
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($postData), $this->equalTo($expectedContentType), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		// call add
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		$fixture->delete($postData);
	}
	
	public function testDeleteById()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->deleteById("does not exist");
	}
	
	public function testDeleteByMultipleIds()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->deleteByMultipleIds(array(1, 2, 3));
	}
	
	public function testDeleteByQuery()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->deleteByQuery("*:*");
	}
	
	public function testExtracts()
	{
		$extractFile = __FILE__;
		
		$expectedUrl = "http://localhost:8180/solr/update/extract?resource.name=ServiceTest.php&wt=json&json.nl=map";
		$expectedPostData = file_get_contents($extractFile);
		$expectedContentType = 'application/octet-stream'; // default for extract
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedPostData), $this->equalTo($expectedContentType), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extract($extractFile);
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testExtractWithInvalidParams()
	{
		$extractFile = __FILE__;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();

		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extract($extractFile, "invalid");
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testExtractFromStringWithInvalidParams()
	{
		$extractFileData = "does not matter what it is";
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();

		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extractFromString($extractFileData, "invalid");
	}
	
	public function testExtractsWithNullParams()
	{
		$extractFile = __FILE__;
		
		$expectedUrl = "http://localhost:8180/solr/update/extract?resource.name=ServiceTest.php&wt=json&json.nl=map";
		$expectedPostData = file_get_contents($extractFile);
		$expectedContentType = 'application/octet-stream'; // default for extract
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedPostData), $this->equalTo($expectedContentType), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extract($extractFile, null);
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testExtractWithEmptyFile()
	{
		$extractFile = "iDontExist.txt";
				
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
				
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extract($extractFile);
	}
	
	public function testExtractsWithDocument()
	{
		$extractFile = __FILE__;
		
		$expectedUrl = "http://localhost:8180/solr/update/extract?resource.name=ServiceTest.php&wt=json&json.nl=map&boost.field=2&literal.field=literal+value";
		$expectedPostData = file_get_contents($extractFile);
		$expectedContentType = 'application/octet-stream'; // default for extract
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with($this->equalTo($expectedUrl), $this->equalTo($expectedPostData), $this->equalTo($expectedContentType), $this->equalTo($expectedTimeout))
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$literals = new Apache_Solr_Document();
		$literals->field = "literal value";
		$literals->setFieldBoost('field', 2);
		
		$fixture->extract($extractFile, null, $literals);
	}
	
	public function testExtractWithUrlDefers()
	{
		$extractUrl = "http://example.com";
		
		$expectedUrl = "http://localhost:8180/solr/update/extract?resource.name=http%3A%2F%2Fexample.com&wt=json&json.nl=map";
		$expectedPostData = Apache_Solr_HttpTransport_ResponseTest::BODY_200;
		$expectedContentType = 'application/octet-stream'; // default for extract
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performGetRequest')
			->with(
				$this->equalTo($extractUrl)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				$this->equalTo($expectedUrl),
				$this->equalTo($expectedPostData),
				$this->equalTo($expectedContentType),
				$this->equalTo($expectedTimeout)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extract($extractUrl);
	}
	
	public function testExtractFromUrl()
	{
		$extractUrl = "http://example.com";
		
		$expectedUrl = "http://localhost:8180/solr/update/extract?resource.name=http%3A%2F%2Fexample.com&wt=json&json.nl=map";
		$expectedPostData = Apache_Solr_HttpTransport_ResponseTest::BODY_200;
		$expectedContentType = 'application/octet-stream'; // default for extract
		$expectedTimeout = false;
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performGetRequest')
			->with(
				$this->equalTo($extractUrl)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->with(
				$this->equalTo($expectedUrl),
				$this->equalTo($expectedPostData),
				$this->equalTo($expectedContentType),
				$this->equalTo($expectedTimeout)
			)
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extractFromUrl($extractUrl);
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testExtractFromUrlWithInvalidParams()
	{
		$extractUrl = "http://example.com";
		
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
			
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->extractFromUrl($extractUrl, "invalid");
	}
	
	public function testOptimize()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_Service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->optimize();
	}
	
	public function testSearch()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performGetRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->search("solr");
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testSearchWithInvalidParams()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->search("solr", 0, 10, "invalid");
		
		$this->fail("Should have through InvalidArgumentException");
	}
	
	public function testSearchWithEmptyParams()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performGetRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->search("solr", 0, 10, null);
	}
	
	public function testSearchWithPostMethod()
	{
		// set a mock transport
		$mockTransport = $this->getMockHttpTransportInterface();
		
		// setup expected call and response
		$mockTransport->expects($this->once())
			->method('performPostRequest')
			->will($this->returnValue(Apache_Solr_HttpTransport_ResponseTest::get200Response()));
		
		$fixture = new Apache_Solr_service();
		$fixture->setHttpTransport($mockTransport);
		
		$fixture->search("solr", 0, 10, array(), Apache_Solr_Service::METHOD_POST);
	}
	
	/**
	 * @expectedException Apache_Solr_InvalidArgumentException
	 */
	public function testSearchWithInvalidMethod()
	{
		$fixture = new Apache_Solr_service();
		
		$fixture->search("solr", 0, 10, array(), "INVALID METHOD");
	}
}