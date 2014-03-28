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
	
	// Last scan status
	protected $status;
	
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
	

	public static function av_scan($path) {
		$path = $path[\OC\Files\Filesystem::signal_param_path];
		if ($path != '') {
			$files_view = \OCP\Files::getStorage("files");

			// check if path is a directory
			if($files_view->is_dir($path))
				return;

			// we should have a file to work with, and the file shouldn't
			// be empty
			$fileExists = $files_view->file_exists($path);
			if ($fileExists && $files_view->filesize($path) > 0) {
				$fileStatus = self::scanFile($files_view, $path);
				$result = $fileStatus->getNumericStatus();
				switch($result) {
					case Status::SCANRESULT_UNCHECKED:
						//TODO: Show warning to the user: The file can not be checked
						break;
					case Status::SCANRESULT_INFECTED:
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

					case Status::SCANRESULT_CLEAN:
						//do nothing
						break;
				}
			}
		}
	}

	public static function scanFile($fileView, $filepath) {
		$instance = self::getInstance();

		if ($instance instanceof Scanner){
			try {
				$instance->scan($fileView, $filepath);
			} catch (\Exception $e){
			}
		}
		
		return self::getStatus();
	}
	
	
	protected static function getStatus(){
		$instance = self::getInstance();
		if ($instance->status instanceof Status){
			return $instance->status;
		}
		return new Status();
	}

	
	private static function getInstance(){
		if (is_null(self::$instance)){
			try {
				$avMode = \OCP\Config::getAppValue('files_antivirus', 'av_mode', 'executable');
				switch($avMode) {
					case 'daemon':
						self::$instance = new Scanner_External(false);
						break;
					case 'socket':
						self::$instance = new Scanner_External(true);
						break;
					case 'executable':
						self::$instance = new Scanner_Local();
						break;
					default:
						self::$instance = false;
						\OCP\Util::writeLog('files_antivirus', 'Unknown mode: ' . $avMode, \OCP\Util::WARN);
						break;
				}
			} catch (\Exception $e){
				self::$instance = false;
			}
		}
		
		return self::$instance;
	}

}
