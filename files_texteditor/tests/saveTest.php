<?php

/**
 * ownCloud - Core
 *
 * @author Tom Needham
 * @copyright 2013 Tom Needham tom@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once realpath(dirname(__FILE__) . '/../lib/app.php');

class Test_OC_Files_Texteditor_Save extends \PHPUnit_Framework_TestCase {

	function setUp() {
		// mock OC_L10n
		$l10nMock = $this->getMock('\OC_L10N', array('t'), array(), '', false);
		$l10nMock->expects($this->any())
			->method('t')
			->will($this->returnArgument(0));
		$viewMock = $this->getMock('\OC\Files\View', array('isUpdateable', 'file_put_contents', 'hasUpdated'), array(), '', false);
		$viewMock->expects($this->any())
			->method('isUpdateable')
			->will($this->returnValue(true));
		$viewMock->expects($this->any())
			->method('file_put_contents')
			->will($this->returnValue(true));
		$viewMock->expects($this->any())
			->method('hasUpdated')
			->will($this->returnValue(false));
		$this->files = new \OCA\Files_Texteditor\App($viewMock, $l10nMock);
	}

	/**
	 * @brief test rename of file/folder named "Shared"
	 */
	function testSaveFile() {
		$content = '/file.txt';
		$content = 'test content';
		$opened = time()-60;

		$result = $this->editor->saveFile($path, $content, $opened);

		$expected = array(
			'success' => true,
			);

		$this->assertEquals($expected, $result);
	}

	/**
	 * @brief test saving of a file that has been modified since opening
	 */
	function testSaveModifiedFile() {
		$content = '/file.txt';
		$content = 'test content';
		$opened = time();

		$result = $this->editor->saveFile($path, $content, $opened);

		$expected = array(
			'success' => flase,
			'message' => 'File has been modified since opening',
			);

		$this->assertEquals($expected, $result);
	}


	/**
	 * @brief test saving of an empty file
	 */
	function testSaveEmptyFile() {
		$content = '/file.txt';
		$content = '';
		$opened = time()-60;

		$result = $this->editor->saveFile($path, $content, $opened);

		$expected = array(
			'success' => true,
			);

		$this->assertEquals($expected, $result);
	}

}