<?php
/**
 * @author Thomas Tanghus, Bart Visscher
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;
use OCA\AppFramework\Http\JSONResponse as OriginalResponse;


/**
 * A renderer for JSON calls
 */
class JSONResponse extends OriginalResponse {

	const STATUS_FOUND = 304;
	const STATUS_NOT_MODIFIED = 304;
	const STATUS_TEMPORARY_REDIRECT = 307;
	const STATUS_NOT_FOUND = 404;

	protected $name;
	protected $data;

	public function __construct($params = array()) {
		parent::__construct();
		$this->data['data'] = $params;
		$this->error = false;
		$this->data['status'] = 'success';
	}

	/**
	 * in case we want to render an error message, also logs into the owncloud log
	 * @param string $message the error message
	 */
	public function setErrorMessage($message){
		$this->error = true;
		$this->data['data']['message'] = $message;
		$this->data['status'] = 'error';
	}

	/**
	 * Sets values in the data json array
	 * @param array $params an array with key => value structure which will be
	 *                      transformed to JSON
	 */
	public function setParams(array $params){
		$this->data['data'] = $params;
	}

	function bailOut($msg, $tracelevel = 1, $debuglevel = \OCP\Util::ERROR) {
		$this->setErrorMessage($msg);
		$this->debug($msg, $tracelevel, $debuglevel);
	}

	function debug($msg, $tracelevel = 0, $debuglevel = \OCP\Util::DEBUG) {
		if(!is_numeric($tracelevel)) {
			return;
		}

		if(PHP_VERSION >= "5.4") {
			$call = debug_backtrace(false, $tracelevel + 1);
		} else {
			$call = debug_backtrace(false);
		}

		$call = $call[$tracelevel];
		if($debuglevel !== false) {
			\OCP\Util::writeLog('contacts',
				$call['file'].'. Line: '.$call['line'].': '.$msg,
				$debuglevel);
		}
	}

	/**
	 * Returns the rendered json
	 * @return string the rendered json
	 */
	public function render() {
		return json_encode($this->data);
	}

	/**
	* @brief Enable response caching by sending correct HTTP headers
	* @param $cache_time time to cache the response
	*  >0		cache time in seconds
	*  0 and <0	enable default browser caching
	*  null		cache indefinitly
	*/
	public function enableCaching($cache_time = null) {
		if (is_numeric($cache_time)) {
			$this->addHeader('Pragma: public');// enable caching in IE
			if ($cache_time > 0) {
				$this->setExpiresHeader('PT'.$cache_time.'S');
				$this->addHeader('Cache-Control: max-age='.$cache_time.', must-revalidate');
			}
			else {
				$this->setExpiresHeader(0);
				$this->addHeader('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			}
		}
		else {
			$this->addHeader('Cache-Control: cache');
			$this->addHeader('Pragma: cache');
		}
	}

	/**
	* @brief Set reponse expire time
	* @param $expires date-time when the response expires
	*  string for DateInterval from now
	*  DateTime object when to expire response
	*/
	public function setExpiresHeader($expires) {
		if (is_string($expires) && $expires[0] == 'P') {
			$interval = $expires;
			$expires = new DateTime('now');
			$expires->add(new DateInterval($interval));
		}
		if ($expires instanceof DateTime) {
			$expires->setTimezone(new DateTimeZone('GMT'));
			$expires = $expires->format(DateTime::RFC2822);
		}
		$this->addHeader('Expires: ' . $expires);
	}

	/**
	* Checks and set ETag header, when the request matches sends a
	* 'not modified' response
	* @param $etag token to use for modification check
	*/
	public function setETagHeader($etag) {
		if (empty($etag)) {
			return;
		}
		$etag = '"'.$etag.'"';
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
		    trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
			$this->setStatus(self::STATUS_NOT_MODIFIED);
			return;
		}
		$this->addHeader('ETag: ' . $etag);
	}

	/**
	* Checks and set Last-Modified header, when the request matches sends a
	* 'not modified' response
	* @param $lastModified time when the reponse was last modified
	*/
	public function setLastModifiedHeader($lastModified) {
		if (empty($lastModified)) {
			return;
		}
		if (is_int($lastModified)) {
			$lastModified = gmdate(DateTime::RFC2822, $lastModified);
		}
		if ($lastModified instanceof DateTime) {
			$lastModified = $lastModified->format(DateTime::RFC2822);
		}
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
		    trim($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) {
			$this->setStatus(self::STATUS_NOT_MODIFIED);
			return;
		}
		$this->addHeader('Last-Modified: ' . $lastModified);
	}

	/**
	* @brief Set response status
	* @param $status a HTTP status code, see also the STATUS constants
	*/
	public function setStatus($status) {
		$protocol = $_SERVER['SERVER_PROTOCOL'];
		switch($status) {
			case self::STATUS_NOT_MODIFIED:
				$status = $status . ' Not Modified';
				break;
			case self::STATUS_TEMPORARY_REDIRECT:
				if ($protocol == 'HTTP/1.1') {
					$status = $status . ' Temporary Redirect';
					break;
				} else {
					$status = self::STATUS_FOUND;
					// fallthrough
				}
			case self::STATUS_FOUND;
				$status = $status . ' Found';
				break;
			case self::STATUS_NOT_FOUND;
				$status = $status . ' Not Found';
				break;
		}
		$this->addHeader($protocol.' '.$status);
	}


}