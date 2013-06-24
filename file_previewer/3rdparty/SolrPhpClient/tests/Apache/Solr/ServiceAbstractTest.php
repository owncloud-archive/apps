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
 * Provides base funcationality test for both Apache_Solr_Service and the
 * Apache_Solr_Service_Balancer classes. 
 */
abstract class Apache_Solr_ServiceAbstractTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Method that gets the appropriate instance for testing
	 */
	abstract public function getFixture();
	
	/**
	 * @dataProvider testEscapeDataProvider
	 */
	public function testEscape($input, $expectedOutput)
	{
		$fixture = $this->getFixture();
		
		$this->assertEquals($expectedOutput, $fixture->escape($input));
	}
	
	public function testEscapeDataProvider()
	{
		return array(
			array(
				"I should look the same",
				"I should look the same"
			),
			
			array(
				"(There) are: ^lots \\ && of spec!al charaters",
				"\\(There\\) are\\: \\^lots \\\\ \\&& of spec\\!al charaters"
			)
		);
	}
	
	/**
	 * @dataProvider testEscapePhraseDataProvider
	 */
	public function testEscapePhrase($input, $expectedOutput)
	{
		$fixture = $this->getFixture();
		
		$this->assertEquals($expectedOutput, $fixture->escapePhrase($input));
	}
	
	public function testEscapePhraseDataProvider()
	{
		return array(
			array(
				"I'm a simple phrase",
				"I'm a simple phrase"
			),
		
			array(
				"I have \"phrase\" characters",
				'I have \\"phrase\\" characters'
			)
		);
	}
	
	/**
	 * @dataProvider testPhraseDataProvider
	 */
	public function testPhrase($input, $expectedOutput)
	{
		$fixture = $this->getFixture();
		
		$this->assertEquals($expectedOutput, $fixture->phrase($input));
	}
	
	public function testPhraseDataProvider()
	{
		return array(
			array(
				"I'm a simple phrase",
				'"I\'m a simple phrase"'
			),
			
			array(
				"I have \"phrase\" characters",
				'"I have \\"phrase\\" characters"'
			)
		);
	}
	
	public function testGetCreateDocumentWithDefaultConstructor()
	{
		$fixture = $this->getFixture();
		
		$this->assertTrue($fixture->getCreateDocuments());
	}
	
	public function testSetCreateDocuments()
	{
		$fixture = $this->getFixture();
		
		$fixture->setCreateDocuments(false);
		
		$this->assertFalse($fixture->getCreateDocuments());
	}
}