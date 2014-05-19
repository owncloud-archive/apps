<?php
/**
 * Copyright (c) 2014 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


namespace OCA\Files_Antivirus\Scanner;

use OCA\Files_Antivirus\Status;

class Local extends \OCA\Files_Antivirus\Scanner{
	
	protected $avPath;
	
	public function __construct(){
		parent::__construct();
		
		// get the path to the executable
		$avPath = \OCP\Config::getAppValue('files_antivirus', 'av_path', '/usr/bin/clamscan');

		// check that the executable is available
		if (!file_exists($avPath)) {
			throw new \RuntimeException('The antivirus executable could not be found at '.$avPath);
		}
		
		$this->avPath = $avPath;
	} 

	protected function scan($fileView, $filepath) {
		$this->status = new Status();
		
		$fhandler = $this->getFileHandle($fileView, $filepath);
		\OCP\Util::writeLog('files_antivirus', 'Exec scan: '.$filepath, \OCP\Util::DEBUG);
		
		$avCmdOptions = \OCP\Config::getAppValue('files_antivirus', 'av_cmd_options', '');
		$shellArgs = explode(',', $avCmdOptions);
		$shellArgs = array_map(function($i){
				return escapeshellarg($i);
			},
			$shellArgs
		);
		
		$preparedArgs = '';
		if (count($shellArgs)){
			$preparedArgs = implode(' ', $shellArgs);
		}

		// using 2>&1 to grab the full command-line output.
		$cmd = escapeshellcmd($this->avPath) . " " . $preparedArgs ." - 2>&1";
		$descriptorSpec = array(
			0 => array("pipe","r"), // STDIN
			1 => array("pipe","w")  // STDOUT
		);
		
		$pipes = array();
		$process = proc_open($cmd, $descriptorSpec, $pipes);
		if (!is_resource($process)) {
			fclose($fhandler);
			throw new \RuntimeException('Error starting process');
		}

		// write to stdin
		$shandler = $pipes[0];

		while (!feof($fhandler)) {
			$chunk = fread($fhandler, $this->chunkSize);
			fwrite($shandler, $chunk);
		}

		fclose($shandler);
		fclose($fhandler);

		$output = stream_get_contents($pipes[1]);

		fclose($pipes[1]);

		$result = proc_close($process);

		$this->status->parseResponse($output, $result);
		
		return $this->status->getNumericStatus();
	}
	
}
