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
 * Apache_Solr_Document Unit Test
 */
class Apache_Solr_DocumentTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Fixture used for testing
	 *
	 * @var Apache_Solr_Document
	 */
	private $_fixture;

	/**
	 * Setup for the fixture before each unit test - part of test case API
	 */
	protected function setup()
	{
		$this->_fixture = new Apache_Solr_Document();
	}

	/**
	 * Teardown after each unit test - part of test case API
	 */
	protected function tearDown()
	{
		unset($this->_fixture);
	}

	public function testDefaultStateAfterConstructor()
	{
		// document boost should be false
		$this->assertFalse($this->_fixture->getBoost());

		// document fields should be empty
		$this->assertEquals(0, count($this->_fixture->getFieldNames()));
		$this->assertEquals(0, count($this->_fixture->getFieldValues()));
		$this->assertEquals(0, count($this->_fixture->getFieldBoosts()));

		// document iterator should be empty
		$this->assertEquals(0, iterator_count($this->_fixture));
	}

	public function testSetAndGetField()
	{
		$field = 'field';
		$value = 'value';
		$boost = 0.5;

		// set the field
		$this->_fixture->setField($field, $value, $boost);

		$result = $this->_fixture->getField($field);

		// check the array values
		$this->assertTrue(is_array($result));
		$this->assertEquals($field, $result['name']);
		$this->assertEquals($value, $result['value']);
		$this->assertEquals($boost, $result['boost']);
	}

	public function testGetFieldReturnsFalseForNonExistentField()
	{
		$this->assertFalse($this->_fixture->getField('field'));
	}

	public function testMagicGetForFieldValues()
	{
		$field = 'field';
		$value = 'value';

		$this->_fixture->setField($field, $value);

		// test the __get value
		$this->assertEquals($value, $this->_fixture->{$field});
	}
	
	/**
	 * Added for issue #48 (http://code.google.com/p/solr-php-client/issues/detail?id=48)
	 */
	public function testMagicGetReturnsNullForNonExistentField()
	{
		$this->assertNull($this->_fixture->nonExistent);
	}

	public function testMagicSetForFieldValues()
	{
		$field = 'field';
		$value = 'value';

		// set field value with magic __set
		$this->_fixture->{$field} = $value;

		$fieldArray = $this->_fixture->getField($field);

		// set values
		$this->assertEquals($field, $fieldArray['name']);
		$this->assertEquals($value, $fieldArray['value']);
		$this->assertTrue($fieldArray['boost'] === false);
	}

	public function testMagicIssetForNonExistentField()
	{
		$this->assertFalse(isset($this->_fixture->field));
	}

	public function testMagicIssetForExistingField()
	{
		$field = 'field';
		$this->_fixture->{$field} = 'value';
		$this->assertTrue(isset($this->_fixture->{$field}));
	}

	public function testMagicUnsetForExistingField()
	{
		$field = 'field';

		$this->_fixture->{$field} = 'value';

		// now unset the field
		unset($this->_fixture->{$field});

		// now test that its unset
		$this->assertFalse(isset($this->_fixture->{$field}));
	}

	public function testMagicUnsetForNonExistingField()
	{
		$field = 'field';
		unset($this->_fixture->{$field});

		// now test that it still does not exist
		$this->assertFalse(isset($this->_fixture->{$field}));
	}

	public function testSetAndGetFieldBoostWithPositiveNumberSetsBoost()
	{
		$field = 'field';
		$boost = 0.5;

		$this->_fixture->setFieldBoost($field, $boost);

		// test the field boost
		$this->assertEquals($boost, $this->_fixture->getFieldBoost($field));
	}

	public function testSetAndGetFieldBoostWithZeroRemovesBoost()
	{
		$field = 'field';
		$boost = 0;

		$this->_fixture->setFieldBoost($field, $boost);

		// test the field boost
		$this->assertTrue($this->_fixture->getFieldBoost($field) === false);
	}

	public function testSetAndGetFieldBoostWithNegativeNumberRemovesBoost()
	{
		$field = 'field';
		$boost = -1;

		$this->_fixture->setFieldBoost($field, $boost);

		// test the field boost
		$this->assertTrue($this->_fixture->getFieldBoost($field) === false);
	}

	public function testSetAndGetFieldBoostWithNonNumberRemovesBoost()
	{
		$field = 'field';
		$boost = "i am not a number";

		$this->_fixture->setFieldBoost($field, $boost);

		// test the field boost
		$this->assertTrue($this->_fixture->getFieldBoost($field) === false);
	}

	public function testSetAndGetBoostWithPositiveNumberSetsBoost()
	{
		$boost = 0.5;
		$this->_fixture->setBoost($boost);

		// the boost should now be set
		$this->assertEquals($boost, $this->_fixture->getBoost());
	}

	public function testSetAndGetBoostWithZeroRemovesBoost()
	{
		$this->_fixture->setBoost(0);

		// should be boolean false
		$this->assertTrue($this->_fixture->getBoost() === false);
	}

	public function testSetAndGetBoostWithNegativeNumberRemovesBoost()
	{
		$this->_fixture->setBoost(-1);

		// should be boolean false
		$this->assertTrue($this->_fixture->getBoost() === false);
	}

	public function testSetAndGetBoostWithNonNumberRemovesBoost()
	{
		$this->_fixture->setBoost("i am not a number");

		// should be boolean false
		$this->assertTrue($this->_fixture->getBoost() === false);
	}

	public function testAddFieldCreatesMultiValueWhenFieldDoesNotExist()
	{
		$field = 'field';
		$value = 'value';

		$this->_fixture->addField($field, $value);

		// check that value is an array with correct values
		$fieldValue = $this->_fixture->{$field};

		$this->assertTrue(is_array($fieldValue));
		$this->assertEquals(1, count($fieldValue));
		$this->assertEquals($value, $fieldValue[0]);
	}

	/**
	 *	setMultiValue has been deprecated and defers to addField
	 *
	 *	@deprecated
	 */
	public function testSetMultiValueCreateMultiValueWhenFieldDoesNotExist()
	{
		$field = 'field';
		$value = 'value';

		$this->_fixture->setMultiValue($field, $value);

		// check that value is an array with correct values
		$fieldValue = $this->_fixture->{$field};

		$this->assertTrue(is_array($fieldValue));
		$this->assertEquals(1, count($fieldValue));
		$this->assertEquals($value, $fieldValue[0]);
	}

	public function testAddFieldCreatesMultiValueWhenFieldDoesExistAsSingleValue()
	{
		$field = 'field';
		$value1 = 'value1';
		$value2 = 'value2';

		// set first value as singular value
		$this->_fixture->{$field} = $value1;

		// add a second value with addField
		$this->_fixture->addField($field, $value2);

		// check that value is an array with correct values
		$fieldValue = $this->_fixture->{$field};

		$this->assertTrue(is_array($fieldValue));
		$this->assertEquals(2, count($fieldValue));
		$this->assertEquals($value1, $fieldValue[0]);
		$this->assertEquals($value2, $fieldValue[1]);
	}

	/**
	 *	setMultiValue has been deprecated and defers to addField
	 *
	 *	@deprecated
	 */
	public function testSetMultiValueCreatesMultiValueWhenFieldDoesExistAsSingleValue()
	{
		$field = 'field';
		$value1 = 'value1';
		$value2 = 'value2';

		// set first value as singular value
		$this->_fixture->{$field} = $value1;

		// add a second value with addField
		$this->_fixture->setMultiValue($field, $value2);

		// check that value is an array with correct values
		$fieldValue = $this->_fixture->{$field};

		$this->assertTrue(is_array($fieldValue));
		$this->assertEquals(2, count($fieldValue));
		$this->assertEquals($value1, $fieldValue[0]);
		$this->assertEquals($value2, $fieldValue[1]);
	}

	public function testAddFieldWithBoostSetsFieldBoost()
	{
		$field = 'field';
		$boost = 0.5;

		$this->_fixture->addField($field, 'value', $boost);

		// check the field boost
		$this->assertEquals($boost, $this->_fixture->getFieldBoost($field));
	}

	public function testAddFieldWithBoostMultipliesWithAPreexistingBoost()
	{
		$field = 'field';
		$boost = 0.5;

		// set a field with a boost
		$this->_fixture->setField($field, 'value1', $boost);

		// now add another value with the same boost
		$this->_fixture->addField($field, 'value2', $boost);

		// new boost should be $boost * $boost
		$this->assertEquals($boost * $boost, $this->_fixture->getFieldBoost($field));
	}

	public function testGetFieldNamesIsInitiallyEmpty()
	{
		$fieldNames = $this->_fixture->getFieldNames();

		$this->assertTrue(empty($fieldNames));
	}

	public function testGetFieldNamesAfterFieldIsSetIsNotEmpty()
	{
		$field = 'field';

		$this->_fixture->{$field} = 'value';
		$fieldNames = $this->_fixture->getFieldNames();

		$this->assertTrue(!empty($fieldNames));
		$this->assertEquals(1, count($fieldNames));
		$this->assertEquals($field, $fieldNames[0]);
	}

	public function testGetFieldValuesIsInitiallyEmpty()
	{
		$fieldValues = $this->_fixture->getFieldValues();

		$this->assertTrue(empty($fieldValues));
	}

	public function testGetFieldValuessAfterFieldIsSetIsNotEmpty()
	{
		$value = 'value';

		$this->_fixture->field = $value;
		$fieldValues = $this->_fixture->getFieldValues();

		$this->assertTrue(!empty($fieldValues));
		$this->assertEquals(1, count($fieldValues));
		$this->assertEquals($value, $fieldValues[0]);
	}

	public function testGetIteratorAfterFieldValueIsSet()
	{
		$field = 'field';
		$value = 'value';

		$this->_fixture->{$field} = $value;

		$itemCount = 0;

		foreach ($this->_fixture as $iteratedField => $iteratedValue)
		{
			++$itemCount;

			// test field and value
			$this->assertEquals($field, $iteratedField);
			$this->assertEquals($value, $iteratedValue);
		}

		// test number of iterations is 1
		$this->assertEquals(1, $itemCount);
	}

	public function testClearReturnsDocumentToDefaultState()
	{
		// set the document boost
		$this->_fixture->setBoost(0.5);

		// set a field
		$this->_fixture->someField = "some value";

		// clear the document to remove boost and fields
		$this->_fixture->clear();

		// document boost should now be false
		$this->assertFalse($this->_fixture->getBoost());

		// document fields should now be empty
		$this->assertEquals(0, count($this->_fixture->getFieldNames()));
		$this->assertEquals(0, count($this->_fixture->getFieldValues()));
		$this->assertEquals(0, count($this->_fixture->getFieldBoosts()));

		// document iterator should now be empty
		$this->assertEquals(0, iterator_count($this->_fixture));
	}
}