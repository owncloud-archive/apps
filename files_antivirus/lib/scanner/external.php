<?php
/**
 * Copyright (c) 2014 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


namespace OCA\Files_Antivirus\Scanner;

use OCA\Files_Antivirus\Status;

class External extends \OCA\Files_Antivirus\Scanner {
	
	// Daemon/socket mode
	protected $useSocket;
	
	
	public function __construct($useSocket){
		parent::__construct($useSocket);
		$this->useSocket = $useSocket;
	}
	

	protected function scan($fileView, $filepath) {
		$this->status = new Status();
		
		if ($this->useSocket){
			$av_socket = \OCP\Config::getAppValue( 'files_antivirus', 'av_socket', '' );
			$shandler = stream_socket_client('unix://' . $av_socket, $errno, $errstr, 5);
			if (!$shandler) {
				throw new \RuntimeException('Cannot connect to "' . $av_socket . '": ' . $errstr . ' (code ' . $errno . ')');
			}
		} else {
			$av_host = \OCP\Config::getAppValue('files_antivirus', 'av_host', '');
			$av_port = \OCP\Config::getAppValue('files_antivirus', 'av_port', '');
			$shandler = ($av_host && $av_port) ? @fsockopen($av_host, $av_port) : false;
			if (!$shandler) {
				throw new \RuntimeException('The clamav module is not configured for daemon mode.');
			}
		}
		
		$fhandler = $this->getFileHandle($fileView, $filepath);
		\OCP\Util::writeLog('files_antivirus', 'Exec scan: '.$filepath, \OCP\Util::DEBUG);

		// request scan from the daemon
		fwrite($shandler, "nINSTREAM\n");
		while (!feof($fhandler)) {
			$chunk = fread($fhandler, $this->chunkSize);
			$chunk_len = pack('N', strlen($chunk));
			fwrite($shandler, $chunk_len.$chunk);
		}
		fwrite($shandler, pack('N', 0));
		$response = fgets($shandler);
		\OCP\Util::writeLog('files_antivirus', 'Response :: '.$response, \OCP\Util::DEBUG);
		fclose($shandler);
		fclose($fhandler);
		
		$this->status->parseResponse($response);
		
		return $this->status->getNumericStatus();
	}
	
}
