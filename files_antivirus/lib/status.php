<?php
/**
 * Copyright (c) 2014 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Antivirus;

class Status {
	// The file was not checked (e.g. because the AV daemon wasn't running).
	const SCANRESULT_UNCHECKED = -1;
	// The file was checked and found to be clean.
	const SCANRESULT_CLEAN = 0;
	// The file was checked and found to be infected.
	const SCANRESULT_INFECTED = 1;
	
	protected $descriptions = array();
	
	public function __construct(){
		$this->descriptions = array(
				    40 => "Unknown option passed.",
				    50 => "Database initialization error.",
				    52 => "Not supported file type.",
				    53 => "Can't open directory.",
				    54 => "Can't open file. (ofm)",
				    55 => "Error reading file. (ofm)",
				    56 => "Can't stat input file / directory.",
				    57 => "Can't get absolute path name of current working directory.",
				    58 => "I/O error, please check your file system.",
				    62 => "Can't initialize logger.",
				    63 => "Can't create temporary files/directories (check permissions).",
				    64 => "Can't write to temporary directory (please specify another one).",
				    70 => "Can't allocate memory (calloc).",
				    71 => "Can't allocate memory (malloc).",
		);
	}
	
	
	
	public function getScanResult($status, $output){
		
	}

	public function getErrorDescription($code){
		if (array_key_exists($code, $this->descriptions)){
			return $this->descriptions[$code];
		} else {
			return 'unknown error';
		}
	}
	
	
	public static function processScan($result, $output){
	}
}