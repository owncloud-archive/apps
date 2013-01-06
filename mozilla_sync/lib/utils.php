<?php

/**
* ownCloud
*
* @author Michal Jaskurzynski
* @copyright 2012 Michal Jaskurzynski mjaskurzynski@gmail.com
*
*/

namespace OCA_mozilla_sync;

class Utils
{

	const STATE_TEST            = 1;
	const STATE_NORMAL          = 0;

	static private $state       = self::STATE_NORMAL;

	/**
	* Mozilla sync status codes:
	*
	* 200 The request was processed successfully.
	*
	* 400 The request itself or the data supplied along with the request is invalid.
	*     The response contains a numeric code indicating the reason for why the request was rejected.
	*     See Response codes for a list of valid response codes.
	*
	* 401 The username and password are invalid on this node.
	*     This may either be caused by a node reassignment or by a password change.
	*     The client should check with the auth server whether the user’s node has changed.
	*     If it has changed, the current sync is to be aborted and should be retried against the new node.
	*     If the node hasn’t changed, the user’s password was changed.
	*
	* 404 The requested resource could not be found.
	*     This may be returned for GET and DELETE requests, for non-existent records and empty collections.
	*
	* 503 Indicates, in conjunction with the Retry-After header, that the server is undergoing maintenance.
	*     The client should not attempt another sync for the number of seconds specified in the header value.
	*     The response body may contain a JSON string describing the server’s status or error.
	*/
	const STATUS_OK                = 200;
	const STATUS_INVALID_DATA      = 400;
	const STATUS_INVALID_USER      = 401;
	const STATUS_NOT_FOUND         = 404;
	const STATUS_MAINTENANCE       = 503;

	static public $lastStatus      = self::STATUS_OK;
	static public $requestMethod   = 'GET';
	static public $testTime;

	/**
	* @brief Change application state to test
	*/
	static public function setTestState() {
		self::$testTime = self::getMozillaTimestamp();
		self::$state = self::STATE_TEST;
	}

	/**
	* @brief Returns true if application is in normal state
	*
	* @return boolean
	*/
	static public function isNormalState() {
		return self::$state === self::STATE_NORMAL;
	}

	/**
	* @brief Returns true if application is in test state
	*
	* @return boolean
	*/
	static public function isTestState() {
		return self::$state === self::STATE_TEST;
	}

	/**
	* @brief Change Http response code
	*
	* @param integer $statusCode
	*/
	public static function changeHttpStatus($statusCode) {

	$message = '';
	switch($statusCode) {
		case 404: $message = 'Not Found'; break;
		case 400: $message = 'Bad Request'; break;
		case 500: $message = 'Internal Server Error'; break;
		case 503: $message = 'Service Unavailable'; break;
	}

	if(self::isNormalState()) {
		header('HTTP/1.0 ' . $statusCode . ' ' . $message);
	}
	else{
		self::$lastStatus = $statusCode;
	}
	}

	/**
	* @brief Change Http response code and send additional Mozilla sync status code
	*
	* @param integer $httpStatusCode
	* @param integer $syncErrorCode
	*/
	public static function sendError($httpStatusCode, $syncErrorCode) {
		self::changeHttpStatus($httpStatusCode);
		OutputData::write($syncErrorCode);
	}

	public static function getRequestMethod() {
		if(self::isNormalState()) {
			return $_SERVER['REQUEST_METHOD'];
		}
		else{
			return self::$requestMethod;
		}
	}

	/**
	* @brief Generate Mozilla sync timestamp for time synchronization
	*/
	public static function generateMozillaTimestamp() {
		header('X-Weave-Timestamp: ' . self::getMozillaTimestamp());
	}
	/**
	* @brief Get current time in Mozilla sync format
	*
	* @return number
	*/
	public static function getMozillaTimestamp() {
		if(self::isNormalState()) {
			return round(microtime(true),2);
		}
		else{
			return self::$testTime;
		}
	}

	/**
	* @brief Returns server address for Mozilla Sync Service
	*
	* @return string
	*/
	public static function getServerAddress() {
		return \OCP\Util::linkToRemote('mozilla_sync');
	}

	/**
	* @brief Change $_GET array to url parser string
	*
	* Modifiers are passsed to this script via separete fields, for example:
	* Array
	* (
	*    [service] => storageapi
	*    [url] => 1.1/12345/storage/history
	*    [full] => 1
	*    [sort] => index
	*    [limit] => 20
	* )
	*
	* There is need to convert to UrlParser input string:
	* 1.1/12345/storage/history?full=1&sort=index&limit=20
	*
	* @return string
	*/
	public static function prepareUrl() {
		unset($_GET['url']);
		unset($_GET['service']);

		$modifiers = '';

		if(count($_GET) > 0) {
			$first = true;
			foreach($_GET as $key => $value)
			{
				if($first) {
					$modifiers .= '?';
					$first = false;
				}
				else{
					$modifiers .= '&';
				}
				$modifiers .= $key . '=' . $value;
			}
		}

		return $modifiers;
	}

	/**
	* @brief Returns Url of sync request
	*
	* @return string
	*/
	public static function getSyncUrl() {
		$url = self::getUrl();
		if(self::getServiceType() === 'userapi') {
			$url = str_replace('/user/','', $url);
		}

		return $url;
	}

	private static function getUrl() {
		$url = \OCP\Util::getRequestUri();
		$url = str_replace('//','/',$url);

		$pos = strpos($url, 'mozilla_sync');
		if($pos === false) {
			return false;
		}
		$pos += strlen('mozilla_sync');

		$url = substr($url, $pos);

		return $url;
	}

	/**
	* @brief Returns type of sync service: storage or userapi
	*
	* @return string
	*/
	public static function getServiceType() {
		if(strpos(self::getUrl(), '/user/') === 0) {
			return 'userapi';
		}

		return 'storageapi';
	}
}