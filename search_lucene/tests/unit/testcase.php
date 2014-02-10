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

use OC\Files\Storage\Storage;
use OC\Files\View;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase {

	/**
	 * @var \OC\Files\Storage\Storage $storage
	 */
	private $storage;
	
	/**
	 *
	 * @var string $userName user name
	 */
	private $userName;

	/**
	 * @var \OC\Files\Cache\Scanner
	 */
	protected $scanner;

	//for search lucene
	public function setUp() {

		// reset backend
		\OC_User::clearBackends();
		\OC_User::useBackend('database');

		// create test user
		$this->userName = 'test';
		\OC_User::deleteUser($this->userName);
		\OC_User::createUser($this->userName, $this->userName);

		\OC_Util::tearDownFS();
		\OC_User::setUserId('');
		\OC\Files\Filesystem::tearDown();
		\OC_Util::setupFS($this->userName);
		\OC_User::setUserId($this->userName);

		$view = new \OC_FilesystemView('/' . $this->userName . '/files');

		// setup files
		$filesToCopy = array(
			'documents' => array(
				'document.pdf',
				'document.docx',
				'document.odt',
				'document.txt',
			),
			/*
			'music' => array(
				'projekteva-letitrain.mp3',
			),
			'photos' => array(
				'photo.jpg',
			),
			'videos' => array(
				'BigBuckBunny_320x180.mp4',
			),
			*/
		);
		$count = 0;
		foreach($filesToCopy as $folder => $files) {
			foreach($files as $file) {
				$imgData = file_get_contents(__DIR__ . '/data/' . $file);
				$view->mkdir($folder);
				$path = $folder . '/' . $file;
				$view->file_put_contents($path, $imgData);

				// set mtime to get fixed sorting with respect to recentFiles
				$count++;
				$view->touch($path, 1000 + $count);
			}
		}

		list($storage, $internalPath) = $view->resolvePath('');
		/** @var $storage Storage */
		$this->storage = $storage;
		$this->scanner = $storage->getScanner();

		// hookup scanner
		/*
		$this->scanner->listen('\OC\Files\Cache\Scanner', 'postScanFile', function($path, $storage) {
			$h = new Hooks();
			$h->postScanFile($path, $storage);
		});
		 */

		$this->scanner->scan('');
	}

	public function tearDown() {
		if (is_null($this->storage)) {
			return;
		}
		$cache = $this->storage->getCache();
		$ids = $cache->getAll();
		$permissionsCache = $this->storage->getPermissionsCache();
		$permissionsCache->removeMultiple($ids, \OC_User::getUser());
		$cache->clear();
	}
	
	protected function getFileId($path) {
		
		$view = new View('/' . $this->userName . '/files');
		$fileInfo = $view->getFileInfo($path);
		
		if (! empty($fileInfo)) {
			return $fileInfo['fileid'];
		}

		return null;
	}
}
