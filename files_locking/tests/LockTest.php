<?php
/**
 * @author Clark Tomlinson  <clark@owncloud.com>
 * @since 9/24/14, 11:20 AM
 * @link http:/www.clarkt.com
 * @copyright Clark Tomlinson © 2014
 *
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Locking\Tests;


use OCA\Files_Locking\Lock;

class LockTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var Lock
	 */
	private $fileLock;


	public function setup() {
		\OCP\App::checkAppEnabled('files_locking');
		$this->fileLock = new Lock(__DIR__ . '/data/test.txt');
	}

	public function testObtainReadLockAndRelease() {
		$this->assertTrue(\Test_Helper::invokePrivate($this->fileLock, 'obtainReadLock'));
		$this->assertTrue($this->fileLock->release('read'));
	}

	public function testObtainWriteLockAndRelease() {
		$this->assertTrue(\Test_Helper::invokePrivate($this->fileLock, 'obtainWriteLock'));
		$this->assertTrue($this->fileLock->release('write'));
	}

	public function testLockLockFile() {
		$this->assertTrue(\Test_Helper::invokePrivate($this->fileLock, 'lockLockFile', array('test.txt')));
	}

	public function testReleaseAll() {
		$this->assertTrue(\Test_Helper::invokePrivate($this->fileLock, 'releaseAll'));
	}


	/**
	 * @expectedException \OCP\Files\LockNotAcquiredException
	 */
	public function testDoubleLock() {
		$lock1 = $this->fileLock;
		$lock2 = new Lock(__DIR__ . '/data/test.txt');
		$lock1->addLock(Lock::WRITE);
		$lock2->addLock(Lock::WRITE);

	}
}
 