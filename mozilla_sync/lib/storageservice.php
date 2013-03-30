<?php

/**
* ownCloud
*
* @author Michal Jaskurzynski
* @copyright 2012 Michal Jaskurzynski mjaskurzynski@gmail.com
*
*/

namespace OCA_mozilla_sync;

/**
* @brief implementation of Mozilla Sync Storage Service
*
*/
class StorageService extends Service
{
	public function __construct($urlParser, $inputData = null) {
		$this->urlParser = $urlParser;
		$this->inputData = $inputData;
	}

	/**
	* @brief Run service
	*/
	public function run() {
		//
		// Check if given url is valid
		//
		if(!$this->urlParser->isValid()) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_DATA);
			return false;
		}

		$syncUserHash = $this->urlParser->getUserName();

		if(User::authenticateUser($syncUserHash) == false) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_USER);
			return false;
		}

		$userId = User::userHashToId($syncUserHash);
		if($userId == false) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_USER);
			return false;
		}

		Storage::deleteOldWbo();

		//
		// Map request to functions
		//

		// Info case: https://server/pathname/version/username/info/
		if( ($this->urlParser->commandCount() == 2) &&
				($this->urlParser->getCommand(0) == 'info')) {

			if(Utils::getRequestMethod() != 'GET') {
				Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
				return false;
			}
			switch($this->urlParser->getCommand(1)) {
				case 'collections': $this->getInfoCollections($userId); break;
				default: Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			}

		}
		// Storage case: https://server/pathname/version/username/storage/
		else if( ($this->urlParser->commandCount() == 1) &&
				($this->urlParser->getCommand(0) == 'storage')) {

			switch(Utils::getRequestMethod()) {
				case 'DELETE': $this->deleteStorage($userId); break;
				default: Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			}

		}
		// Collection case: https://server/pathname/version/username/storage/collection
		else if( ($this->urlParser->commandCount() == 2) &&
				($this->urlParser->getCommand(0) == 'storage')) {

			$collectionName = $this->urlParser->getCommand(1);
			$modifiers = $this->urlParser->getCommandModifiers(1);

			$collectionId = Storage::collectionNameToIndex($userId, $collectionName);

			switch(Utils::getRequestMethod()) {
				case 'GET': $this->getCollection($userId, $collectionId, $modifiers); break;
				case 'POST': $this->postCollection($userId, $collectionId); break;
				case 'DELETE': $this->deleteCollection($userId, $collectionId, $modifiers); break;
				default: Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			}

		}
		// Wbo case: https://server/pathname/version/username/storage/collection/id
		else if( ($this->urlParser->commandCount() == 3) &&
				($this->urlParser->getCommand(0) == 'storage')) {

			$collectionName = $this->urlParser->getCommand(1);
			$wboId = $this->urlParser->getCommand(2);

			$collectionId = Storage::collectionNameToIndex($userId, $collectionName);

			switch(Utils::getRequestMethod()) {
				case 'GET': $this->getWBO($userId, $collectionId, $wboId); break;
				case 'PUT': $this->putWBO($userId, $collectionId, $wboId); break;
				case 'DELETE': $this->deleteWBO($userId, $collectionId, $wboId); break;
				default: Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			}

		}
		else{
			Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
		}

		return true;
	}

	/**
	* @brief Returns a hash of collections associated with the account, along with the last modified timestamp for each collection.
	*
	* HTTP request: GET https://server/pathname/version/username/info/collections
	*
	* Example:
	*
	* HTTP/1.0 200 OK
	* Server: PasteWSGIServer/0.5 Python/2.6.6
	* Date: Sun, 25 Mar 2012 16:29:21 GMT
	* Content-Type: application/json
	* Content-Length: 227
	* X-Weave-Records: 9
	* X-Weave-Timestamp: 1332692961.71
	*
	* {"passwords": 1332607246.46, "tabs": 1332607246.93, "clients": 1332607162.28,
	* "crypto": 1332607162.21, "forms": 1332607170.80, "meta": 1332607246.96,
	* "bookmarks": 1332607162.45, "prefs": 1332607246.72, "history": 1332607245.16}
	*
	* @param integer $userId
	* @return bool true if success
	*/
	private function getInfoCollections($userId) {

		$query = \OCP\DB::prepare( 'SELECT `name`,
																		(SELECT max(`modified`) FROM `*PREFIX*mozilla_sync_wbo`
																			WHERE `*PREFIX*mozilla_sync_wbo`.`collectionid` = `*PREFIX*mozilla_sync_collections`.`id`
																		) AS `modified`
															FROM `*PREFIX*mozilla_sync_collections` WHERE `userid` = ?');
		$result = $query->execute( array($userId) );

		if($result == false) {
			return false;
		}

		$resultArray = array();

		while (($row = $result->fetchRow())) {

			// Skip empty collections
			if($row['modified'] == null) {
				continue;
			}

			$key = $row['name'];
			$value = $row['modified'];

			$resultArray[$key] = $value;
		}

		OutputData::write( $resultArray );
		return true;
	}

	/**
	* NOT IMPLEMENTED!!
	*
	* Will return 404 HTTP status
	*
	* HTTP request: GET https://server/pathname/version/username/info/collection_usage
	*
	* Returns a hash of collections associated with the account,
	* along with the data volume used for each (in KB).
	*/
	//TODO: collection usage

	/**
	* NOT IMPLEMENTED!!
	*
	* Will return 404 HTTP status
	*
	* HTTP request: GET https://server/pathname/version/username/info/collection_counts
	*
	* Returns a hash of collections associated with the account,
	* along with the total number of items in each collection.
	*/
	//TODO: collection counts

	/**
	* NOT IMPLEMENTED!!
	*
	* Will return 404 HTTP status
	*
	* HTTP request: GET https://server/pathname/version/username/info/quota
	*
	* Returns a list containing the user’s current usage and quota (in KB).
	* The second value will be null if no quota is defined.
	*/
	//TODO: quota

	/**
	* @brief Returns a list of the WBO ids contained in a collection
	*
	* HTTP request: GET https://server/pathname/version/username/storage/collection
	*
	* This request has additional optional parameters:
	*
	* ids:             returns the ids for objects in the collection that are in the provided comma-separated list.
	*
	* full:            if defined, returns the full WBO, rather than just the id.
	*
	*
	* predecessorid:   returns the ids for objects in the collection that are directly preceded by the id given.
	*                  Usually only returns one result.
	*
	* parentid:        returns the ids for objects in the collection that are the children of the parent id given.
	*
	*
	* older:           returns only ids for objects in the collection that have been last modified before the date given.
	*
	* newer:           returns only ids for objects in the collection that have been last modified since the date given.
	*
	* index_above:     if defined, only returns items with a higher sortindex than the value specified.
	*
	* index_below:     if defined, only returns items with a lower sortindex than the value specified.
	*
	*
	* limit:           sets the maximum number of ids that will be returned.
	*
	* offset:          skips the first n ids. For use with the limit parameter (required) to paginate through a result set.
	*
	* sort:            sorts the output.
	*                     ‘oldest’ - Orders by modification date (oldest first)
	*                     ‘newest’ - Orders by modification date (newest first)
	*                     ‘index’ - Orders by the sortindex descending (highest weight first)
	*
	* WARNING!!
	*
	* In full record mode, data are send in separate arrays, for example:
	*    {"id":"test1","modified":1234}
	*    {"id":"test2","modified":12345}
	*
	* In id only mode, identificators are send in one array, for example:
	*    ["qqweeqw","testid","nexttestid"]
	*
	* @param integer $userId
	* @param integer $collectionId
	* @param array $modifiers
	* @return bool true if success
	*/
	private function getCollection($userId, $collectionId, &$modifiers) {

		$queryArgs = array();

		// full or id modifier
		$queryFields = '';
		if(isset($modifiers['full'])) {
			$queryFields = '`payload`, `name` AS `id`, `modified`, `parentid`, `predecessorid`, `sortindex`, `ttl`';
		}
		else{
			$queryFields = '`name` AS `id`';
		}

		$whereString = 'WHERE `collectionid` = ?';
		array_push($queryArgs, $collectionId);

		$whereString .= Storage::modifiersToString($modifiers, $queryArgs);

		$query = \OCP\DB::prepare( 'SELECT ' . $queryFields . ' FROM `*PREFIX*mozilla_sync_wbo` ' . $whereString );
		$result = $query->execute( $queryArgs );

		if($result == false) {
			return false;
		}

		// array used in id only request
		$resultIdArray = array();

		$hasData = false;

		while (($row = $result->fetchRow())) {
			$hasData = true;
			if(isset($modifiers['full'])) {
				OutputData::write($row);
			}
			else{
				$resultIdArray[] = $row['id'];
			}
		}

		// No data
		if($hasData == false) {
			Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			return true;
		}

		if(!isset($modifiers['full'])) {
			OutputData::write($resultIdArray);
		}

		return true;
	}

	/**
	* @brief Save array of wbo
	*
	* HTTP request: POST https://server/pathname/version/username/storage/collection
	*
	* Takes an array of WBOs in the request body and iterates over them,
	* effectively doing a series of atomic PUTs with the same timestamp.
	*
	* example response:
	*   {"failed": {}, "modified": 1341650217.16, "success": ["VQYhVASVcpVI"]}
	*
	* @param integer $userId
	* @param integer $collectionId
	* @return bool true if success
	*/
	private function postCollection($userId, $collectionId) {
		//print 'postCollection';
		$inputData = $this->getInputData();
		if( (!$inputData->isValid()) &&
				(count($inputData->getInputArray()) > 0)) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_DATA);
			return false;
		}

		$modifiedTime = Utils::getMozillaTimestamp();

		$resultArray["modified"] = $modifiedTime;

		$successArray = array();
		$failedArray = array();

		for($i = 0; $i < count($inputData->getInputArray()); $i++) {
			$result = Storage::saveWBO($userId,
																						$modifiedTime,
																						$collectionId,
																						$inputData[$i]);
			if($result == true) {
				$successArray[] = $inputData[$i]['id'];
			}
			else{
				$failedArray[] = $inputData[$i]['id'];
			}
		}

		$resultArray["success"] = $successArray;
		$resultArray["failed"] = $failedArray;

		OutputData::write($resultArray);
		return true;
	}

	/**
	* @brief Deletes the collection and all contents
	*
	* HTTP request: DELETE https://server/pathname/version/username/storage/collection
	*
	* Additional request parameters may modify the selection of which items to delete @see getCollection
	*
	* @param integer $userId
	* @param integer $collectionId
	* @param array $modifiers
	* @return bool true if success
	*/
	private function deleteCollection($userId, $collectionId, &$modifiers) {

		$queryArgs = array();

		$whereString = 'WHERE `collectionid` = ?';
		array_push($queryArgs, $collectionId);

		$whereString .= Storage::modifiersToString($modifiers, $queryArgs);

		$query = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*mozilla_sync_wbo` ' . $whereString );
		$result = $query->execute( $queryArgs );

		if($result == false) {
			return false;
		}

		$query = \OCP\DB::prepare( 'SELECT 1 FROM `*PREFIX*mozilla_sync_wbo` WHERE `collectionid` = ?' );
		$result = $query->execute( array($collectionId) );

		// No wbo found, delete colection
		if($result->fetchRow() == false) {

			$query = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*mozilla_sync_collections` WHERE `id` = ?' );
			$result = $query->execute( array($collectionId) );

			if($result == false) {
				return false;
			}
		}

		OutputData::write(Utils::getMozillaTimestamp());
		return true;
	}

	/**
	* $brief Returns the WBO in the collection corresponding to the requested id
	*
	* HTTP request: GET https://server/pathname/version/username/storage/collection/id
	*
	* @param integer $userId
	* @param integer $collectionId
	* @param integer $wboId
	* @return bool true if success
	*/
	private function getWBO($userId, $collectionId, $wboId) {
		$query = \OCP\DB::prepare( 'SELECT `sortindex`, `payload`, `name` AS `id`, `modified` FROM `*PREFIX*mozilla_sync_wbo`
															WHERE `collectionid` = ? AND `name` = ?');
		$result = $query->execute( array($collectionId, $wboId) );

		if($result == false) {
			return false;
		}

		$row=$result->fetchRow();
		if($row == false) {
			Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			return true;
		}

		OutputData::write($row);
		return true;
	}

	/**
	* @brief Adds the WBO defined in the request body to the collection
	*
	* HTTP request: PUT https://server/pathname/version/username/storage/collection/id
	*
	* If the WBO does not contain a payload, it will only update the provided metadata fields on an already defined object.
	* The server will return the timestamp associated with the modification.
	*
	* @param integer $userId
	* @param integer $collectionId
	* @param integer $wboId
	* @return bool true if success
	*/
	private function putWBO($userId, $collectionId, $wboId) {
		$inputData = $this->getInputData();
		if( (!$inputData->isValid()) &&
				(count($inputData->getInputArray()) == 1)) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_DATA);
			return false;
		}

		$modifiedTime = Utils::getMozillaTimestamp();

		if(isset($inputData['modified'])) {
			$modifiedTime = $inputData['modified'];
		}

		$result = Storage::saveWBO($userId,
			$modifiedTime,
			$collectionId,
			$inputData->getInputArray());

		if($result == false) {
			return false;
		}

		OutputData::write($modifiedTime);
	}

	/**
	* @brief Deletes the WBO at the location given
	*
	* HTTP request: DELETE https://server/pathname/version/username/storage/collection/id
	*
	* @param integer $userId
	* @param integer $collectionId
	* @param integer $wboId
	* @return bool true if success
	*/
	private function deleteWBO($userId, $collectionId, $wboId) {

		$result = Storage::deleteWBO($userId, $collectionId, $wboId);

		if($result == false) {
			return false;
		}

		OutputData::write(Utils::getMozillaTimestamp());
		return true;
	}

	/**
	* @brief Deletes all records for the user
	*
	* HTTP request: DELETE https://server/pathname/version/username/storage
	*
	* Will return a precondition error unless an X-Confirm-Delete header is included.
	*
	* All delete requests return the timestamp of the action.
	*
	* @param integer $userId
	* @return bool true if success
	*/
	private function deleteStorage($userId) {

		if(!isset($_SERVER['HTTP_X_CONFIRM_DELETE'])) {
			return false;
		}

		$result = Storage::deleteStorage($userId);

		if($result == false) {
			return false;
		}

		OutputData::write(Utils::getMozillaTimestamp());
		return true;
	}

}

