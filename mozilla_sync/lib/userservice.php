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
* @brief implementation of Mozilla Sync User Service
*
*/
class UserService extends Service
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
			Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			return false;
		}

		//
		// Map request to functions
		//
		if($this->urlParser->commandCount() == 0) {

			$syncUserHash = $this->urlParser->getUserName();

			switch(Utils::getRequestMethod()) {
				case 'GET': $this->findUser($syncUserHash); break;
				case 'PUT': $this->createUser($syncUserHash); break;
				case 'DELETE': $this->deleteUser($syncUserHash); break;
				default: Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			}
		}
		else if(($this->urlParser->commandCount() == 1) && (Utils::getRequestMethod() == 'POST')) {

			$syncUserHash = $this->urlParser->getUserName();
			$password = $this->urlParser->getCommand(0);

			$this->changePassword($syncUserHash, $password);
		}
		else if($this->urlParser->commandMatch('/node\/weave/')) {
			$this->getSyncServer();
		}
		else{
			Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
		}

		return true;
	}

	/**
	*  @brief Method for checking if user already exists
	*
	*  GET https://server/pathname/version/username
	*
	*  Returns 1 if the username is in use, 0 if it is available. The answer is in plain text.
	*
	*  Possible errors:
	*    503: there was an error getting the information
	*
	*  @param string $userName
	*/
	private function findUser($syncUserHash) {
		if(User::syncUserExists($syncUserHash)) {
			OutputData::write('1');
		}
		else{
			OutputData::write('0');
		}
		return true;
	}

	/**
	*  @brief Generate storage api server address respond
	*
	*  GET https://server/pathname/version/username/node/weave
	*
	*  Returns the Weave (aka Sync) Node that the client is located on. Sync-specific calls should be directed to that node.
	*  Return value: the node URL, an unadorned (not JSON) string.
	*  node may be ‘null’ if no node can be assigned at this time, probably due to sign up throttling.
	*
	*  Possible errors:
	*    503: there was an error getting a node | empty body
	*    404: user not found | empty body
	*/
	private function getSyncServer() {
		OutputData::write(Utils::getServerAddress());
		return true;
	}

	/**
	*  @brief Create new user
	*
	*  PUT https://server/pathname/version/username
	*
	*  Requests that an account be created for username.
	*
	*  The body is a JSON mapping and should include:
	*    password: the password to be associated with the account.
	*    e-mail: Email address associated with the account.
	*    captcha-challenge: The challenge string from the captcha.
	*    captcha-response: The response to the captcha.
	*
	*  An X-Weave-Secret can be provided containing a secret string known by the server.
	*  When provided, it will override the captcha. This is useful for testing and automation.
	*
	*  The server will return the lowercase username on success.
	*
	*  Possible errors:
	*    503: there was an error creating the reset code
	*    400: 4 (user already exists)
	*    400: 6 (Json parse failure)
	*    400: 12 (No email address on file)
	*    400: 7 (Missing password field)
	*    400: 9 (Requested password not strong enough)
	*    400: 2 (Incorrect or missing captcha)
	*
	*  @param string $userName
	*/
	private function createUser($syncUserHash) {

		$inputData = $this->getInputData();

		// JSON parse failure
		if(!$inputData->isValid()) {
			Utils::sendError(400, 6);
			return true;
		}

		// No password
		if(!$inputData->hasValue('password')) {
			Utils::sendError(400, 7);
			return true;
		}

		// No email
		if(!$inputData->hasValue('email')) {
			Utils::sendError(400, 12);
			return true;
		}

		// User already exists
		if(User::syncUserExists($syncUserHash)) {
			Utils::sendError(400, 4);
			return true;
		}

		// Create user
		if(User::createUser($syncUserHash, $inputData->getValue('password'), $inputData->getValue('email'))) {
			OutputData::write(strtolower($syncUserHash));
		}
		else{
			Utils::sendError(400, 12);
		}

		return true;
	}

	/**
	*  @brief Detete user
	*
	*  DELETE https://server/pathname/version/username
	*
	*  Deletes the user account.
	*  NOTE: Requires simple authentication with the username and password associated with the account.
	*
	*  Return value:
	*  0 on success
	*
	*  Possible errors:
	*    503: there was an error removing the user
	*    404: the user does not exist in the database
	*    401: authentication failed
	*
	*  @param string $userName
	*/
	private function deleteUser($syncUserHash) {

		if(User::syncUserExists($syncUserHash) == false) {
			Utils::changeHttpStatus(Utils::STATUS_NOT_FOUND);
			return true;
		}

		if(User::authenticateUser($syncUserHash) == false) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_USER);
			return true;
		}

		$userId = User::userHashToId($syncUserHash);
		if($userId == false) {
			Utils::changeHttpStatus(Utils::STATUS_INVALID_USER);
			return true;
		}

		if(Storage::deleteStorage($userId) == false) {
			Utils::changeHttpStatus(Utils::STATUS_MAINTENANCE);
			return true;
		}

		if(User::deleteUser($userId) == false) {
			Utils::changeHttpStatus(Utils::STATUS_MAINTENANCE);
			return true;
		}

		OutputData::write('0');
		return true;
	}

	/**
	*  @brief Change password
	*
	*  POST https://server/pathname/version/username/password
	*
	*  Changes the password associated with the account to the value specified in the POST body.
	*
	*  NOTE: Requires basic authentication with the username and (current) password associated with the account.
	*  The auth username must match the username in the path.
	*
	*  Alternately, a valid X-Weave-Password-Reset header can be used, if it contains a code previously obtained from the server.
	*
	*  Return values: “success” on success.
	*
	*  Possible errors:
	*    400: 7 (Missing password field)
	*    400: 10 (Invalid or missing password reset code)
	*    400: 9 (Requested password not strong enough)
	*    404: the user does not exists in the database
	*    503: there was an error updating the password
	*    401: authentication failed
	*/
	private function changePassword($syncUserHash, $password) {
		OutputData::write('success');
		return true;
	}
}
