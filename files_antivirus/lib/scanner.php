<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class OC_Files_Antivirus_BackgroundScanner {
	public static function check() {
		$sql = 'SELECT `id`, `path`, `user`'
			.' FROM `*PREFIX*fscache`'
			.' LEFT JOIN `*PREFIX*files_antivirus` ON `*PREFIX*files_antivirus`.`fileid` = `*PREFIX*fscache`.`id`'
			.' WHERE `mimetype` != ? AND (`fileid` IS NULL OR `mtime` > `check_time`)';
		$stmt = OCP\DB::prepare($sql, 5);
		try {
			$result = $stmt->execute(array('httpd/unix-directory'));
			if (\OC_DB::isError($result)) {
				\OC_Log::write('files_antivirus', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($result), \OCP\Util::ERROR);
				return;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog('files_antivirus', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return;
		}
		while ($row = $result->fetchRow()) {
			$parts = explode('/', $row['path'], 4);
			$path = $parts[3];
			self::scan($row['id'], $row['user'], $path);
		}
	}

	public static function scan($id, $user, $path) {
		$root=OC_User::getHome($user).'/files/';
		$file = $root.$path;
		$result = OC_Files_Antivirus::clamav_scan($file);
		switch($result) {
			case CLAMAV_SCANRESULT_UNCHECKED:
				\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" from user "'.$user.'": is not checked', \OCP\Util::ERROR);
				break;
			case CLAMAV_SCANRESULT_INFECTED:
				\OCP\Util::writeLog('files_antivirus', 'File "'.$path.'" from user "'.$user.'": is infected', \OCP\Util::ERROR);
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
