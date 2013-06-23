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

class Test_OC_Files_Texteditor_Load extends \PHPUnit_Framework_TestCase {

	function setUp() {
		// mock OC_L10n
		$l10nMock = $this->getMock('\OC_L10N', array('t'), array(), '', false);
		$l10nMock->expects($this->any())
			->method('t')
			->will($this->returnArgument(0));
		$viewMock = $this->getMock('\OC\Files\View', array('isUpdateable', 'file_get_contents', 'getMimeType'), array(), '', false);
		$viewMock->expects($this->any())
			->method('isUpdateable')
			->will($this->returnValue(true));
		$viewMock->expects($this->any())
			->method('file_get_contents')
			->will($this->returnValue('test content'));
		$viewMock->expects($this->any())
			->method('getMimeType')
			->will($this->returnValue('text/plain'));
		$this->files = new \OCA\Texteditor\App($viewMock, $l10nMock);
	}

	/**
	 * @brief test loading of a file
	 */
	function testLoadFile() {
		$dir = '/';
		$filename = 'file.txt';
		$fileContent = '';

		$result = $this->editor->saveFile($dir, $filename, $contents);

		$expected = array(
			'success' => true,
			'data' => array(
				'filecontents' => 'test content',
				'writeable' => true,
				'mime' => 'text/plain',
				'opened' => '',//time(),
				)
			);

		$this->assertEquals($expected, $result);
	}

	/**
	 * @brief test loading of a file that doesnt exist
	 */
	function testLoadNonExistingFile() {
		$dir = '/';
		$filename = 'file.txt';
		$fileContent = '';

		$result = $this->editor->saveFile($dir, $filename, $contents);

		$expected = array(
			'success' => false,
			'message' => 'There was an error while opening the file',
			);

		$this->assertEquals($expected, $result);
	}

}