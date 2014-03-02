<?php

/**
 * ownCloud - Persona plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\User_persona;

class Validator {
	const VALIDATION_URL = 'https://verifier.login.persona.org/verify';
	const STATUS_SUCCESS = 'okay';
	
	protected static $_isPersona = false;
	protected static $_isAmbigous = false;
	
	/**
	 * Send JSON response on successful login
	 * @param String $uid
	 */
	public static function postlogin_hook($uid){
		if (!self::$_isPersona){
			return;
		}
		\OCP\Util::writeLog(App::APP_ID, 'Check ambigous ' , \OCP\Util::DEBUG);
		if (self::$_isAmbigous){
			//Reply with error and logout 
			\OCP\User::logout();
			\OCP\JSON::error(array('msg'=>'More than one user found'));
			exit();
			
		} else {
			\OCP\JSON::success(array('msg'=>'Access granted'));
			exit();
		}
	}
	
	/**
	 * Sets multiple users flag
	 * @return false
	 */
	public static function setAmbigous(){
		self::$_isAmbigous = true;
		return false;
	}

	/**
	 * Validates an assertion
	 * @param String $assertion
	 * @return String 
	 */
	public static function Validate($assertion) {
		self::$_isPersona = true;
		$data = array(
			'assertion' => $assertion,
			'audience' => \OCP\Util::getServerProtocol() . '://' . \OCP\Util::getServerHostName()
		);
		$response = self::_query($data);
		return self::_parseResponse($response);
	}

	/**
	 * Check if response has an email
	 * @param string $response
	 * @return string 
	 */
	protected static function _parseResponse($response) {
		$email = false;
		$parsedResponse = json_decode($response, true);
		if (isset($parsedResponse['status']) && $parsedResponse['status'] == self::STATUS_SUCCESS) {
			$email = @$parsedResponse['email'];
		}
		\OCP\Util::writeLog(App::APP_ID, 'Mozilla Persona login with email ' . ($email ? $email : 'empty'), \OCP\Util::DEBUG);
		return $email;
	}

	/**
	 * cUrl request
	 * @param String $data
	 * @return String
	 */
	protected static function _query($data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::VALIDATION_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cacert.pem');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);

		$error = curl_errno($ch);
		if ($error) {
			\OCP\Util::writeLog(App::APP_ID, 'Curl reports the error: ' . curl_error($ch), \OCP\Util::WARN);
		}

		curl_close($ch);

		return $response;
	}

}