<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class OC_Files_Antivirus_BackgroundScanner {
	public static function check() {
		// get mimetype code for directory
		$query = \OC_DB::prepare('SELECT `id` FROM `*PREFIX*mimetypes` WHERE `mimetype` = ?');
		$result = $query->execute(array('httpd/unix-directory'));
		if ($row = $result->fetchRow()) {
			$dir_mimetype = $row['id'];
		} else {
			$dir_mimetype = 0;
		}

		// locate files that are not checked yet
		$sql = 'SELECT `*PREFIX*filecache`.`fileid`, `path`, `storage`'
			.' FROM `*PREFIX*filecache`'
			.' LEFT JOIN `*PREFIX*files_antivirus` ON `*PREFIX*files_antivirus`.`fileid` = `*PREFIX*filecache`.`fileid`'
			.' WHERE `mimetype` != ? AND (`*PREFIX*files_antivirus`.`fileid` IS NULL OR `mtime` > `check_time`)';
		$stmt = OCP\DB::prepare($sql, 5);
		try {
			$result = $stmt->execute(array($dir_mimetype));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('files_antivirus', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('files_antivirus', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return;
		}
		// scan the found files
		while ($row = $result->fetchRow()) {
			self::scan($row['fileid'], $row['storage'], $row['path']);
		}
	}

	protected static function getStorage($numeric_id)
	{
		$query = \OC_DB::prepare('SELECT `id` FROM `*PREFIX*storages` WHERE `numeric_id` = ?');
		$result = $query->execute(array($numeric_id));
		if ($row = $result->fetchRow()) {
			$storage_id = $row['id'];
			if (strpos($storage_id, 'local::') === 0) {
				$arguments = array(
					'datadir' => substr($storage_id, 7),
				);
				return new \OC\Files\Storage\Local($arguments);
			}
		}
		return null;
	}

	public static function scan($id, $storage_id, $path) {
		$storage = self::getStorage($storage_id);
		$file = $storage->getLocalFile($path);
		$result = OC_Files_Antivirus::clamav_scan($file);
		switch($result) {
			case CLAMAV_SCANRESULT_UNCHECKED:
				\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" from user "'.$user.'": is not checked', \OCP\Util::ERROR);
				break;
			case CLAMAV_SCANRESULT_INFECTED:
				$infected_action = \OCP\Config::getAppValue('files_antivirus', 'infected_action', 'only_log');
				if ($infected_action == 'delete') {
					\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" from user "'.$user.'": is infected, file deleted', \OCP\Util::ERROR);
					unlink($file);
				}
				else {
					\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" from user "'.$user.'": is infected', \OCP\Util::ERROR);
				}
				break;
			case CLAMAV_SCANRESULT_CLEAN:
				//echo 'File "'.$path.'" from user "'.$user.'": is clean.'.$id."\n";
				$stmt = OCP\DB::prepare('INSERT INTO `*PREFIX*files_antivirus` (`fileid`, `check_time`) VALUES (?, ?)');
				try {
					$result = $stmt->execute(array($id, time()));
					if (\OC_DB::isError($result)) {
						\OC_Log::write('files_antivirus', __METHOD__. ', DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
						return;
					}
				} catch(\Exception $e) {
					\OCP\Util::writeLog('files_antivirus', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				}
				break;
		}
	}
}
