<?php

/**
* ownCloud - files_antivirus
*
* @author Manuel Deglado
* @copyright 2012 Manuel Deglado manuel.delgado@ucr.ac.cr
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

namespace OCA\Files_Antivirus;

class Scanner {
	// null if not initialized
	// false if an error occurred
	// Scanner subclass if initialized
	protected static $instance = null;
	
	// Chunk size
	protected $chunkSize;
	
	// The file was not checked (e.g. because the AV daemon wasn't running).
	const SCANRESULT_UNCHECKED = -1;
	// The file was checked and found to be clean.
	const SCANRESULT_CLEAN = 0;
	// The file was checked and found to be infected.
	const SCANRESULT_INFECTED = 1;

	
	public function __construct($notUsed=false){
		$this->chunkSize = \OCP\Config::getAppValue('files_antivirus', 'av_chunk_size', '1024');
	}
	
	protected function getFileHandle($fileView, $filepath) {
		$fhandler = $fileView->fopen($filepath, "r");
		if(!$fhandler) {
			\OCP\Util::writeLog('files_antivirus', 'File could not be open.', \OCP\Util::ERROR);
			throw new \RuntimeException();
		}
		return $fhandler;
	}
	
	protected function getStatusByResponse($rawResponse, $status = null){
		if (is_null($status)){
			// clamd returns a string response in the format:
			// filename: OK
			// filename: <name of virus> FOUND
			// filename: <error string> ERROR

			if (preg_match('/.*: OK$/', $rawResponse)) {
				return self::SCANRESULT_CLEAN;
			} elseif (preg_match('/.*: (.*) FOUND$/', $rawResponse, $matches)) {
				$virus_name = $matches[1];
				\OCP\Util::writeLog('files_antivirus', 'Virus detected in file. Clamav reported the virus: '.$virus_name, \OCP\Util::WARN);
				return self::SCANRESULT_INFECTED;
			} else {
				// try to extract the error message from the response.
				preg_match('/.*: (.*) ERROR$/', $rawResponse, $matches);
				$error_string = $matches[1]; // the error message given by the daemon
				\OCP\Util::writeLog('files_antivirus', 'File could not be scanned. Clamscan reported: '.$error_string, \OCP\Util::WARN);
				return self::SCANRESULT_UNCHECKED;
			}
		} else {
			/**
			 * clamscan return values (documented from man clamscan)
			 *  0 : No virus found.
			 *  1 : Virus(es) found.
			 *  X : Error.
			 * TODO: add errors?
			*/
			switch($status) {
				case 0:
					\OCP\Util::writeLog('files_antivirus', 'Result CLEAN!', \OCP\Util::DEBUG);
					return self::SCANRESULT_CLEAN;

				case 1:
					$line = 0;
					$report = array();
					$rawResponse = explode("\n", $rawResponse);
					while ( strpos($rawResponse[$line], "--- SCAN SUMMARY ---") === FALSE ) {
						if (preg_match('/.*: (.*) FOUND$/', $rawResponse[$line], $matches)) {
							$report[] = $matches[1];
						}
						$line++;
					}
					\OCP\Util::writeLog('files_antivirus', 'Virus detected in file.  Clamscan reported: '.implode(', ', $report), \OCP\Util::WARN);
					return self::SCANRESULT_INFECTED;

				default:
					$statusObj = new Status();
					$description = $statusObj->getErrorDescription($status);
					\OCP\Util::writeLog('files_antivirus', 'File could not be scanned.  Clamscan reported: '.$description, \OCP\Util::WARN);
					return self::SCANRESULT_UNCHECKED;
			}
		}
	}




	public static function av_scan($path) {
		$path=$path[\OC\Files\Filesystem::signal_param_path];
		if ($path != '') {
			$files_view = \OCP\Files::getStorage("files");
			if ($files_view->file_exists($path)) {
				$result = self::clamav_scan($files_view, $path);
				switch($result) {
					case self::SCANRESULT_UNCHECKED:
						//TODO: Show warning to the user: The file can not be checked
						break;
					case self::SCANRESULT_INFECTED:
						//remove file
						$files_view->unlink($path);
						OCP\JSON::error(array("data" => array( "message" => "Virus detected! Can't upload the file." )));
						$email = OCP\Config::getUserValue(OCP\User::getUser(), 'settings', 'email', '');
						\OCP\Util::writeLog('files_antivirus', 'Email: '.$email, \OCP\Util::DEBUG);
						if (!empty($email) ) {
							$tmpl = new OCP\Template('files_antivirus', 'notification');
							$tmpl->assign('file', $path);
							$tmpl->assign('host', OCP\Util::getServerHost());
							$tmpl->assign('user', OCP\User::getDisplayName());
							$msg = $tmpl->fetchPage();
							$from = OCP\Util::getDefaultEmailAddress('security-noreply');
							OCP\Util::sendMail($email, OCP\User::getUser(), 'Malware detected', $msg, $from, 'ownCloud', 1);
						}
						exit();
						break;

					case self::SCANRESULT_CLEAN:
						//do nothing
						break;
				}
			}
		}
	}

	public static function clamav_scan($fileView, $filepath) {
		if (is_null(self::$instance)){
			try {
				self::getInstance();
			} catch (\Exception $e){
				self::$instance = false;
			}
		}
		
		$scanResult = self::SCANRESULT_UNCHECKED;
		if (self::$instance instanceof Scanner){
			try {
				$scanResult = self::$instance->scan($fileView, $filepath);
			} catch (\Exception $e){
				$scanResult = self::SCANRESULT_UNCHECKED;
			}
		}
			
		return $scanResult;
	}

	private static function getInstance(){
		$av_mode = \OCP\Config::getAppValue('files_antivirus', 'av_mode', 'executable');
		switch($av_mode) {
			case 'daemon':
				self::$instance = new Scanner_External(false);
				break;
			case 'socket':
				self::$instance = new Scanner_External(true);
				break;
			case 'executable':
				self::$instance = new Scanner_Local();
				break;
		}
	}

}
