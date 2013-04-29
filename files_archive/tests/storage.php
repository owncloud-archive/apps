<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Storage;

\OC_App::loadApp('files_archive');

class Archive_Zip extends Storage {
	/**
	 * @var string tmpDir
	 */
	private $tmpFile;

	public function setUp() {
		$this->tmpFile=\OCP\Files::tmpFile('.zip');
		$this->instance=new \OC\Files\Storage\Archive(array('archive'=>$this->tmpFile));
	}

	public function tearDown() {
		unlink($this->tmpFile);
	}
}

class Archive_Tar extends Storage {
	/**
	 * @var string tmpDir
	 */
	private $tmpFile;

	public function setUp() {
		$this->tmpFile=\OCP\Files::tmpFile('.tar.gz');
		$this->instance=new \OC\Files\Storage\Archive(array('archive'=>$this->tmpFile));
	}

	public function tearDown() {
		unlink($this->tmpFile);
	}
}
