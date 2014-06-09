<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files_Archive;

use OC\Files\Cache\Scanner;
use OC\Files\Filesystem;
use OC\Files\Storage\Temporary;
use OC\Files\View;
use OCA\Files_Archive\Manager;

class Archive extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \OC\Files\View $view
	 */
	protected $view;

	protected $zip;

	public function setup() {
		$manager = new Manager(
			new View(''),
			Filesystem::getMountManager(),
			Filesystem::getLoader()
		);

		$storage = new Temporary(array());
		$mountPoint = '/' . uniqid() . '/';
		Filesystem::mount($storage, null, $mountPoint);
		$this->view = new View($mountPoint);
		$this->zip = \OC::$SERVERROOT . '/tests/data/data.zip';
		$fh = fopen($this->zip, 'rb');
		$this->view->file_put_contents('/data.zip', $fh);
		\OC_Hook::clear('OC_Filesystem', 'get_mountpoint');
		\OC_Hook::connect('OC_Filesystem', 'get_mountpoint', $manager, 'autoMount');
	}

	public function tearDown() {
		\OC_Hook::clear('OC_Filesystem', 'get_mountpoint');
	}

	public function testGetFileInfo() {
		$info = $this->view->getFileInfo('/data.zip');
		$this->assertEquals('application/zip', $info->getMimetype());
		$this->assertTrue($info->getStorage()->instanceOfStorage('\OCA\Files_Archive\Storage'));
	}

	public function testArchiveIsDir() {
		$this->assertTrue($this->view->is_dir('/data.zip'));
	}

	public function testGetDirInfo() {
		$content = $this->view->getDirectoryContent('/data.zip');
		$this->assertCount(3, $content);
	}

	public function testArchiveSizeNoScan() {
		$info = $this->view->getFileInfo('/data.zip');
		$this->assertEquals(filesize($this->zip), $info->getSize());
	}

	public function testArchiveSizeScan() {
		$info = $this->view->getFileInfo('/data.zip');
		/**
		 * @var \OCA\Files_Archive\Storage $storage
		 */
		$storage = $info->getStorage();
		$storage->getScanner()->scan('');
		$info = $this->view->getFileInfo('/data.zip');
		$this->assertEquals(filesize($this->zip), $info->getSize());
	}
}
