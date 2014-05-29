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
			if (isset($_POST['dirToken'])){
				//Public upload case
				$filesView = \OC\Files\Filesystem::getView();
			} else {
				$filesView = \OCP\Files::getStorage("files");
			}
			
			if (!is_object($filesView)){
				\OCP\Util::writeLog('files_antivirus', 'Can\'t init filesystem view', \OCP\Util::WARN);
				return;
			}

			// check if path is a directory
			if($filesView->is_dir($path)){
				return;
			}

			// we should have a file to work with, and the file shouldn't
			// be empty
			$fileExists = $filesView->file_exists($path);
			if ($fileExists && $filesView->filesize($path) > 0) {
				$fileStatus = self::scanFile($filesView, $path);
				$result = $fileStatus->getNumericStatus();
				switch($result) {
					case Status::SCANRESULT_UNCHECKED:
						//TODO: Show warning to the user: The file can not be checked
						break;
					case Status::SCANRESULT_INFECTED:
						//remove file
						$filesView->unlink($path);
						Notification::sendMail($path);
						$message = \OCP\Util::getL10N('files_antivirus')->t("Virus detected! Can't upload the file %s", array(basename($path)));
						\OCP\JSON::error(array("data" => array( "message" => $message)));
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
				\OCP\Util::writeLog('files_antivirus', $e->getMessage(), \OCP\Util::ERROR);
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
						self::$instance = new \OCA\Files_Antivirus\Scanner\External(false);
						break;
					case 'socket':
						self::$instance = new \OCA\Files_Antivirus\Scanner\External(true);
						break;
					case 'executable':
						self::$instance = new \OCA\Files_Antivirus\Scanner\Local();
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
