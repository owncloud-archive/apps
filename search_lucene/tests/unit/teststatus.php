<?php

/**
 * ownCloud search lucene
 *
 * @author Jörn Dreyer
 * @copyright 2014 Jörn Friedrich Dreyer jfd@butonic.de
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

namespace OCA\Search_Lucene\Tests\Unit;

use OCA\Search_Lucene\Status;


class TestStatus extends TestCase {

	/**
	 * @dataProvider statusDataProvider
	 */
	function testFromFileIdNull($fileName) {

		// preparation
		$fileId = $this->getFileId($fileName);
		$this->assertNotNull($fileId, 'Precondition failed: file id not found!');

		// run test
		$status = Status::fromFileId($fileId);

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status);
		$this->assertEquals($fileId, $status->getFileId());
		$this->assertEquals(null, $status->getStatus());
	}

	/**
	 * @dataProvider statusDataProvider
	 */
	function testMarkNew($fileName) {

		// preparation
		$fileId = $this->getFileId($fileName);
		$this->assertNotNull($fileId, 'Precondition failed: file id not found!');

		// run test
		$status = new Status($fileId);
		$status->markNew();

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status);
		$this->assertEquals($fileId, $status->getFileId());
		$this->assertEquals(Status::STATUS_NEW, $status->getStatus());

		//check after loading from db
		$status2 = Status::fromFileId($fileId);

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status2);
		$this->assertEquals($fileId, $status2->getFileId());
		$this->assertEquals(Status::STATUS_NEW, $status2->getStatus());
		
	}

	/**
	 * @dataProvider statusDataProvider
	 */
	function testMarkSkipped($fileName) {

		// preparation
		$fileId = $this->getFileId($fileName);
		$this->assertNotNull($fileId, 'Precondition failed: file id not found!');

		// run test
		$status = new Status($fileId);
		$status->markSkipped();

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status);
		$this->assertEquals($fileId, $status->getFileId());
		$this->assertEquals(Status::STATUS_SKIPPED, $status->getStatus());

		//check after loading from db
		$status2 = Status::fromFileId($fileId);

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status2);
		$this->assertEquals($fileId, $status2->getFileId());
		$this->assertEquals(Status::STATUS_SKIPPED, $status2->getStatus());
		
	}

	/**
	 * @dataProvider statusDataProvider
	 */
	function testMarkIndexed($fileName) {

		// preparation
		$fileId = $this->getFileId($fileName);
		$this->assertNotNull($fileId, 'Precondition failed: file id not found!');

		// run test
		$status = new Status($fileId);
		$status->markIndexed();

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status);
		$this->assertEquals($fileId, $status->getFileId());
		$this->assertEquals(Status::STATUS_INDEXED, $status->getStatus());

		//check after loading from db
		$status2 = Status::fromFileId($fileId);

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status2);
		$this->assertEquals($fileId, $status2->getFileId());
		$this->assertEquals(Status::STATUS_INDEXED, $status2->getStatus());
		
	}

	/**
	 * @dataProvider statusDataProvider
	 */
	function testMarkError($fileName) {

		// preparation
		$fileId = $this->getFileId($fileName);
		$this->assertNotNull($fileId, 'Precondition failed: file id not found!');

		// run test
		$status = new Status($fileId);
		$status->markError();

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status);
		$this->assertEquals($fileId, $status->getFileId());
		$this->assertEquals(Status::STATUS_ERROR, $status->getStatus());

		//check after loading from db
		$status2 = Status::fromFileId($fileId);

		$this->assertInstanceOf('OCA\Search_Lucene\Status', $status2);
		$this->assertEquals($fileId, $status2->getFileId());
		$this->assertEquals(Status::STATUS_ERROR, $status2->getStatus());
		
	}

	public function statusDataProvider() {
		return array(
			array('/documents/document.pdf'),
			array('/documents/document.docx'),
			array('/documents/document.odt'),
			array('/documents/document.txt'),
		);
	}
}
