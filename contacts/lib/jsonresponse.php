<?php
/**
 * @author Thomas Tanghus, Bart Visscher
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;

/**
 * A renderer for JSON calls
 */
class JSONResponse {

	public function __construct($params = array()) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' request: '.print_r($request, true), \OCP\Util::DEBUG);
		$this->data['data'] = $params;
		$this->addHeader('X-Content-Type-Options', 'nosniff');
		$this->addHeader('Content-type', 'application/json; charset=utf-8');
	}

	/**
	 * @var array default headers
	 */
	private $headers = array(
		'Cache-Control' => 'no-cache, must-revalidate'
	);

	/**
	 * @var string
	 */
	private $status = '200';

	/**
	 * @var \DateTime
	 */
	private $lastModified;


	/**
	 * @var string
	 */
	private $ETag;


	/**
	 * Caches the response
	 * @param int $cacheSeconds the amount of seconds that should be cached
	 * if 0 then caching will be disabled
	 */
	public function cacheFor($cacheSeconds) {

		if($cacheSeconds > 0) {
			$this->addHeader('Cache-Control', 'max-age=' . $cacheSeconds .
				', must-revalidate');
		} else {
			$this->addHeader('Cache-Control', 'no-cache, must-revalidate');
		}

	}


	/**
	 * Adds a new header to the response that will be called before the render
	 * function
	 * @param string $name The name of the HTTP header
	 * @param string $value The value, null will delete it
	 */
	public function addHeader($name, $value) {
		if(is_null($value)) {
			unset($this->headers[$name]);
		} else {
			$this->headers[$name] = $value;
		}
	}


	/**
	 * Returns the set headers
	 * @return array the headers
	 */
	public function getHeaders() {
		$mergeWith = array();

		if($this->lastModified) {
			$mergeWith['Last-Modified'] =
				$this->lastModified->format(\DateTime::RFC2822);
		}

		if($this->ETag) {
			$mergeWith['ETag'] = '"' . $this->ETag . '"';
		}

		return array_merge($mergeWith, $this->headers);
	}

	/**
	* Returns a full HTTP status message for an HTTP status code
	* Stolen from SabreDAV ;)
	*
	* @param int $code
	* @return string
	*/
	public function getStatusMessage($code, $httpVersion = '1.1') {

		$msg = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authorative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status', // RFC 4918
			208 => 'Already Reported', // RFC 5842
			226 => 'IM Used', // RFC 3229
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			400 => 'Bad request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot', // RFC 2324
			422 => 'Unprocessable Entity', // RFC 4918
			423 => 'Locked', // RFC 4918
			424 => 'Failed Dependency', // RFC 4918
			426 => 'Upgrade required',
			428 => 'Precondition required', // draft-nottingham-http-new-status
			429 => 'Too Many Requests', // draft-nottingham-http-new-status
			431 => 'Request Header Fields Too Large', // draft-nottingham-http-new-status
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version not supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage', // RFC 4918
			508 => 'Loop Detected', // RFC 5842
			509 => 'Bandwidth Limit Exceeded', // non-standard
			510 => 'Not extended',
			511 => 'Network Authentication Required', // draft-nottingham-http-new-status
	);

	return 'HTTP/' . $httpVersion . ' ' . $code . ' ' . $msg[$code];

	}

	/**
	 * Sets values in the data json array
	 * @param array|object $params an array or object which will be transformed
	 *                             to JSON
	 */
	public function setParams(array $params){
		$this->data['data'] = $params;
		$this->data['status'] = 'success';
	}

	/**
	 * Used to get the set parameters
	 * @return array the params
	 */
	public function getParams(){
		return $this->data['data'];
	}

	/**
	* Set response status
	* @param int $status a HTTP status code, see also the STATUS constants
	*/
	public function setStatus($status) {
		$this->status = $status;
	}


	/**
	 * Get response status
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @return string the etag
	 */
	public function getETag() {
		return $this->ETag;
	}


	/**
	 * @return string RFC2822 formatted last modified date
	 */
	public function getLastModified() {
		return $this->lastModified;
	}


	/**
	 * @param string $ETag
	 */
	public function setETag($ETag) {
		$this->ETag = $ETag;
	}


	/**
	 * @param \DateTime $lastModified
	 */
	public function setLastModified($lastModified) {
		$this->lastModified = $lastModified;
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
		header($this->getStatusMessage($this->getStatus()));
		foreach($this->getHeaders() as $name => $value) {
			header($name . ': ' . $value);
		}
		return json_encode($this->data);
	}


}