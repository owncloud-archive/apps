<?php
/**
 * Copyright (c) 2014 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Antivirus;

class Status {
	
	/*
	 *  The file was not checked (e.g. because the AV daemon wasn't running).
	 */
	const SCANRESULT_UNCHECKED = -1;

	/*
	 *  The file was checked and found to be clean.
	 */
	const SCANRESULT_CLEAN = 0;

	/*
	 *  The file was checked and found to be infected.
	 */
	const SCANRESULT_INFECTED = 1;

	/*
	 * Rule needs to be validated by the code returned by scanner
	 */
	const STATUS_TYPE_CODE = 1;
	
	/*
	 * Rule needs to be validated by the output returned by scanner
	 */
	const STATUS_TYPE_MATCH = 2;
	
	
	/*
	 * Should be SCANRESULT_UNCHECKED | SCANRESULT_INFECTED | SCANRESULT_CLEAN
	 */
	protected $numericStatus;
	
	/*
	 * Virus name or error message
	 */
	protected $details = "";
	
	public function __construct(){
		$this->numericStatus = self::SCANRESULT_UNCHECKED;
	}
	
	public function getNumericStatus(){
		return $this->numericStatus;
	}
	
	public function getDetails(){
		return $this->details;
	}
	
	public function parseResponse($rawResponse, $result = null){
		$matches = array();
		
		if (is_null($result)){ // Daemon or socket mode
			// Load rules
			$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*files_antivirus_status` WHERE `status_type`=? and `status`=?');
			
			try{
				$infectedResult = $query->execute(array(self::STATUS_TYPE_MATCH, self::SCANRESULT_INFECTED));
				if (\OCP\DB::isError($infectedResult)) {
					\OCP\Util::writeLog('files_antivirus', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($infectedResult), \OCP\Util::ERROR);
					return;
				}
				$infectedRules = $infectedResult->fetchAll();
				
				$uncheckedResult = $query->execute(array(self::STATUS_TYPE_MATCH, self::SCANRESULT_UNCHECKED));
				if (\OCP\DB::isError($uncheckedResult)) {
					\OCP\Util::writeLog('files_antivirus', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($uncheckedResult), \OCP\Util::ERROR);
					return;
				}
				$uncheckedRules = $uncheckedResult->fetchAll();
				
				$cleanResult = $query->execute(array(self::STATUS_TYPE_MATCH, self::SCANRESULT_CLEAN));
				if (\OCP\DB::isError($cleanResult)) {
					\OCP\Util::writeLog('files_antivirus', __METHOD__. 'DB error: ' . \OC_DB::getErrorMessage($cleanResult), \OCP\Util::ERROR);
					return;
				}
				$cleanRules = $cleanResult->fetchAll();
			
			} catch (\Exception $e){
				\OCP\Util::writeLog('files_antivirus', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return;
			}
			
			$infectedRules = $infectedRules ? $infectedRules : array();
			$uncheckedRules = $uncheckedRules ? $uncheckedRules : array();
			$cleanRules = $cleanRules ? $cleanRules : array();
			
			$isMatched = false;

			// order: clean, infected, try to guess error
			$allRules = array_merge($cleanRules, $infectedRules, $uncheckedRules);			
			foreach ($allRules as $rule){
				if (preg_match($rule['match'], $rawResponse, $matches)){
					$isMatched = true;
					$this->numericStatus = $rule['status'];
					if ($rule['status']==self::SCANRESULT_CLEAN){
						$this->details = '';
					} else {
						$this->details = isset($matches[1]) ? $matches[1] : 'unknown';
					}
					break;
				}
			}
			
			if (!$isMatched){
				$this->numericStatus = self::SCANRESULT_UNCHECKED;
				$this->details = 'unknown';
			}
			
		} else { // Executable mode
			$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*files_antivirus_status` WHERE `status_type`=? and `result`=?');
			$queryResult = $query->execute(array(self::STATUS_TYPE_CODE, $result));
			$scanStatus = $queryResult->fetchRow();
			if (is_array($scanStatus) && count($scanStatus)){
				$this->numericStatus = $scanStatus['status'];
			}
			
			switch($this->numericStatus) {
				case self::SCANRESULT_INFECTED:
					$report = array();
					$rawResponse = explode("\n", $rawResponse);
					
					foreach ($rawResponse as $line){	
						if (preg_match('/.*: (.*) FOUND\s*$/', $line, $matches)) {
							$report[] = $matches[1];
						}
					}
					$this->details = implode(', ', $report);
					
					break;
				case self::SCANRESULT_UNCHECKED:
					$this->details = isset($scanStatus['description']) ? $scanStatus['description'] : 'Unknown error' ;
			}
		}
		
		//Log
		switch($this->numericStatus) {
				case self::SCANRESULT_CLEAN:
					\OCP\Util::writeLog('files_antivirus', 'Result CLEAN!', \OCP\Util::DEBUG);
					break;
				case self::SCANRESULT_INFECTED:
					\OCP\Util::writeLog('files_antivirus', 'Virus(es) found: '.$this->details, \OCP\Util::WARN);
					break;
				default:
					\OCP\Util::writeLog('files_antivirus', 'File could not be scanned. Details: ' . $this->details, \OCP\Util::WARN);
		}
	}

	public static function init(){
		$descriptions = array(
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 0,
				'match' => '',
				'description' => "",
				'status' => self::SCANRESULT_CLEAN
			),

			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 1,
				'match' => '',
				'description' => "",
				'status' => self::SCANRESULT_INFECTED
			),
		
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 40,
				'match' => '',
				'description' => "Unknown option passed.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 50,
				'match' => '',
				'description' => "Database initialization error.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 52,
				'match' => '',
				'description' => "Not supported file type.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 53,
				'match' => '',
				'description' => "Can't open directory.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 54,
				'match' => '',
				'description' => "Can't open file. (ofm)",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 55,
				'match' => '',
				'description' => "Error reading file. (ofm)",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 56,
				'match' => '',
				'description' => "Can't stat input file / directory.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 57,
				'match' => '',
				'description' => "Can't get absolute path name of current working directory.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 58,
				'match' => '',
				'description' => "I/O error, please check your file system.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 62,
				'match' => '',
				'description' => "Can't initialize logger.",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 63,
				'match' => '',
				'description' => "Can't create temporary files/directories (check permissions).",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 64,
				'match' => '',
				'description' => "Can't write to temporary directory (please specify another one).",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 70,
				'match' => '',
				'description' => "Can't allocate memory (calloc).",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_CODE,
				'result' => 71,
				'match' => '',
				'description' => "Can't allocate memory (malloc).",
				'status' => self::SCANRESULT_UNCHECKED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_MATCH,
				'result' => 0,
				'match' => '/.*: OK$/',
				'description' => '',
				'status' => self::SCANRESULT_CLEAN
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_MATCH,
				'result' => 0,
				'match' => '/.*: (.*) FOUND$/',
				'description' => '',
				'status' => self::SCANRESULT_INFECTED
			),
			
			array(
				'group_id' => 0,
				'status_type' => self::STATUS_TYPE_MATCH,
				'result' => 0,
				'match' => '/.*: (.*) ERROR$/',
				'description' => '',
				'status' => self::SCANRESULT_UNCHECKED
			),
		);
		
		$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*files_antivirus_status` (`group_id`, `status_type`, `result`, `match`, `description`, `status`) VALUES (?,?,?,?,?,?)');
		foreach ($descriptions as $description){
			$query->execute(array_values($description));
		}
	}
	
}