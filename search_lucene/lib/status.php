<?php


namespace OCA\Search_Lucene;

/**
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class Status {

	const STATUS_NEW = 'N';
	const STATUS_INDEXED = 'I';
	const STATUS_SKIPPED = 'S';
	const STATUS_ERROR = 'E';
	
	private $fileId;
	private $status;

	public function __construct($fileId, $status = null) {
		$this->fileId = $fileId;
		$this->status = $status;
	}
	public static function fromFileId($fileId) {
		$status = self::get($fileId);
		if ($status) {
			return new Status($fileId, $status);
		} else {
			return new Status($fileId, null);
		}
	}
	// always write status to db immediately
	public function markNew() {
		$this->status = self::STATUS_NEW;
		return $this->store();
	}
	public function markIndexed() {
		$this->status = self::STATUS_INDEXED;
		return $this->store();
	}
	public function markSkipped() {
		$this->status = self::STATUS_SKIPPED;
		return $this->store();
	}
	public function markError() {
		$this->status = self::STATUS_ERROR;
		return $this->store();
	}
	private function store() {
		$savedStatus = self::get($this->fileId);
		if ($savedStatus) {
			return self::update($this->fileId, $this->status);
		} else {
			return self::insert($this->fileId, $this->status);
		}
	}
	
	private static function get($fileId) {
		$query = \OC_DB::prepare('
			SELECT `status`
			FROM `*PREFIX*lucene_status`
			WHERE `fileid` = ?
		');
		$result = $query->execute(array($fileId));
		$row = $result->fetchRow();
		if ($row) {
			return $row['status'];
		} else {
			return null;
		}
	}
	private static function insert($fileId, $status) {
		$query = \OC_DB::prepare('
			INSERT INTO `*PREFIX*lucene_status` VALUES (?,?)
		');
		return $query->execute(array($fileId, $status));
	}
	private static function update($fileId, $status) {
		$query = \OC_DB::prepare('
			UPDATE `*PREFIX*lucene_status`
			SET `status` = ?
			WHERE `fileid` = ?
		');
		return $query->execute(array($status, $fileId));
	}
}
