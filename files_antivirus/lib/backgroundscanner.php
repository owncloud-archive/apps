<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Antivirus;

class BackgroundScanner {
	public static function check() {
		// get mimetype code for directory
		$query = \OCP\DB::prepare('SELECT `id` FROM `*PREFIX*mimetypes` WHERE `mimetype` = ?');
		$result = $query->execute(array('httpd/unix-directory'));
		if ($row = $result->fetchRow()) {
			$dir_mimetype = $row['id'];
		} else {
			$dir_mimetype = 0;
		}
		// locate files that are not checked yet
		$sql = 'SELECT `*PREFIX*filecache`.`fileid`, `path`, `*PREFIX*storages`.`id`'
			.' FROM `*PREFIX*filecache`'
			.' LEFT JOIN `*PREFIX*files_antivirus` ON `*PREFIX*files_antivirus`.`fileid` = `*PREFIX*filecache`.`fileid`'
			.' JOIN `*PREFIX*storages` ON `*PREFIX*storages`.`numeric_id` = `*PREFIX*filecache`.`storage`'
			.' WHERE `mimetype` != ?'
			.' AND (`*PREFIX*storages`.`id` LIKE ? OR `*PREFIX*storages`.`id` LIKE ?)'
			.' AND (`*PREFIX*files_antivirus`.`fileid` IS NULL OR `mtime` > `check_time`)'
			.' AND `path` LIKE ?';
		$stmt = \OCP\DB::prepare($sql, 5);
		try {
			$result = $stmt->execute(array($dir_mimetype, 'local::%', 'home::%', 'files/%'));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog('files_antivirus', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('files_antivirus', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return;
		}

		$serverContainer = \OC::$server;
		/** @var $serverContainer \OCP\IServerContainer */
		$root = $serverContainer->getRootFolder();

		// scan the found files
		while ($row = $result->fetchRow()) {
			$file = $root->getById($row['fileid']); // this should always work ...
			if (!empty($file)) {
				$file = $file[0];
				$storage = $file->getStorage();
				$path = $file->getInternalPath();
				self::scan($file->getId(), $path, $storage);
			} else {
				// ... but sometimes it doesn't, try to get the storage
				$storage = self::getStorage($serverContainer, $row['id']);
				if ($storage !== null && $storage->is_dir('')) {
					self::scan($row['fileid'], $row['path'], $storage);
				} else {
					\OCP\Util::writeLog('files_antivirus', 'Can\'t get \OCP\Files\File for id "'.$row['fileid'].'"', \OCP\Util::ERROR);
				}
			}
		}
	}

	/*
	* This function is a hack, it doesn't work if the $storage_id is a hash.
	*/
	protected static function getStorage($serverContainer, $storage_id) {
		if (strpos($storage_id, 'local::') === 0) {
			$arguments = array(
				'datadir' => substr($storage_id, 7),
			);
			return new \OC\Files\Storage\Local($arguments);
		}
		if (strpos($storage_id, 'home::') === 0) {
			$userid = substr($storage_id, 6);
			$user = $serverContainer->getUserManager()->get($userid);
			$arguments = array(
				'user' => $user,
			);
			return new \OC\Files\Storage\Home($arguments);
		}
		return null;
	}

	public static function scan($id, $path, $storage) {
		$fileStatus = Scanner::scanFile($storage, $path);
		$result = $fileStatus->getNumericStatus();
		
		//TODO: Fix undefined $user here
		switch($result) {
			case Status::SCANRESULT_UNCHECKED:
				\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" with id "'.$id.'": is not checked', \OCP\Util::ERROR);
				break;
			case Status::SCANRESULT_INFECTED:
				$infected_action = \OCP\Config::getAppValue('files_antivirus', 'infected_action', 'only_log');
				if ($infected_action == 'delete') {
					\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" with id "'.$id.'": is infected, file deleted', \OCP\Util::ERROR);
					$storage->unlink($path);
				}
				else {
					\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" with id "'.$id.'": is infected', \OCP\Util::ERROR);
				}
				break;
			case Status::SCANRESULT_CLEAN:
				try {
					$stmt = \OCP\DB::prepare('DELETE FROM `*PREFIX*files_antivirus` WHERE `fileid` = ?');
					$result = $stmt->execute(array($id));
					if (\OCP\DB::isError($result)) {
						\OCP\Util::writeLog('files_antivirus', __METHOD__. ', DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
						return;
					}
					$stmt = \OCP\DB::prepare('INSERT INTO `*PREFIX*files_antivirus` (`fileid`, `check_time`) VALUES (?, ?)');
					$result = $stmt->execute(array($id, time()));
					if (\OCP\DB::isError($result)) {
						\OCP\Util::writeLog('files_antivirus', __METHOD__. ', DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
						return;
					}
				} catch(\Exception $e) {
					\OCP\Util::writeLog('files_antivirus', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				}
				break;
		}
	}
}
