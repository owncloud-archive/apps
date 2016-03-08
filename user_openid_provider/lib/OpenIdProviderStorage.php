<?php

/**
 * @see Zend_OpenId_Provider_Storage
 */
require_once "Zend/OpenId/Provider/Storage.php";

class OC_OpenIdProviderStorage extends Zend_OpenId_Provider_Storage
{
	/**
	 * Stores information about session identified by $handle
	 *
	 * @param string $handle association handle
	 * @param string $macFunc HMAC function (sha1 or sha256)
	 * @param string $secret shared secret
	 * @param string $expires expiration UNIX time
	 * @return bool
	 */
	public function addAssociation($handle, $macFunc, $secret, $expires)
	{
		$name = 'assoc_' . md5($handle);
		$data = serialize(array($handle, $macFunc, base64_encode($secret), $expires));
		OCP\Config::setAppValue('user_openid_provider', $name, $data);
	}

	/**
	 * Gets information about association identified by $handle
	 * Returns true if given association found and not expired and false
	 * otherwise
	 *
	 * @param string $handle assiciation handle
	 * @param string &$macFunc HMAC function (sha1 or sha256)
	 * @param string &$secret shared secret
	 * @param string &$expires expiration UNIX time
	 * @return bool
	 */
	public function getAssociation($handle, &$macFunc, &$secret, &$expires)
	{
		$name = 'assoc_' . md5($handle);
		$data = OCP\Config::getAppValue('user_openid_provider', $name);
		if (!empty($data)) {
			list($storedHandle, $macFunc, $storedSecret, $expires) = unserialize($data);
			$secret = base64_decode($storedSecret);
			if ($handle === $storedHandle && $expires > time()) {
				return true;
			} else {
				$this->delAssociation($handle);
				return false;
			}
		}
		return false;
	}

	/**
	 * Removes information about association identified by $handle
	 *
	 * @param string $handle assiciation handle
	 * @return bool
	 */
	public function delAssociation($handle)
	{
		$name = 'assoc_' . md5($handle);
		$appConfig = \OC::$server->getAppConfig();
		$appConfig->deleteKey('user_openid_provider', $name);

		return true;
	}

	/**
	 * Register new user with given $id and $password
	 * Returns true in case of success and false if user with given $id already
	 * exists
	 *
	 * @param string $id user identity URL
	 * @param string $password encoded user password
	 * @return bool
	 */
	public function addUser($id, $password)
	{
		throw new ErrorException('Not implemented.');
	}

	/**
	 * Returns the username from given $id
	 *
	 * @param string $id user identity URL
	 * @return string
	 */
	protected function getUsernameFromId($id)
	{
		return substr($id, strrpos($id, '/')+2);
	}

	/**
	 * Returns true if user with given $id exists and false otherwise
	 *
	 * @param string $id user identity URL
	 * @return bool
	 */
	public function hasUser($id)
	{
		$userName=$this->getUsernameFromId($id);
		return OCP\User::userExists($userName);
	}

	/**
	 * Verify if user with given $id exists and has specified $password
	 *
	 * @param string $id user identity URL
	 * @param string $password user password
	 * @return bool
	 */
	public function checkUser($id, $password)
	{
		throw new ErrorException('Not implemented.');
	}

	/**
	 * Removes information about specified user
	 *
	 * @param string $id user identity URL
	 * @return bool
	 */
	public function delUser($id)
	{
		throw new ErrorException('Not implemented.');
	}

	/**
	 * Returns array of all trusted/untrusted sites for given user identified
	 * by $id
	 *
	 * @param string $id user identity URL
	 * @return array
	 */
	public function getTrustedSites($id)
	{
		$username = $this->getUsernameFromId($id);
		$data = OCP\Config::getUserValue($username, 'user_openid_provider', 'trusted_sites');
		$sites = array();
		if (!empty($data)) {
			$sites = unserialize($data);
		}
		return $sites;
	}

	/**
	 * Stores information about trusted/untrusted site for given user
	 *
	 * @param string $id user identity URL
	 * @param string $site site URL
	 * @param mixed $trusted trust data from extension or just a boolean value
	 */
	public function addSite($id, $site, $trusted)
	{
		$username = $this->getUsernameFromId($id);
		$data = OCP\Config::getUserValue($username, 'user_openid_provider', 'trusted_sites');
		$sites = array();
		if (!empty($data)) {
			$sites = unserialize($data);
		}
		if ($trusted === null) {
			unset($sites[$site]);
		} else {
			$sites[$site] = $trusted;
		}
		$data = serialize($sites);
		OCP\Config::setUserValue($username, 'user_openid_provider', 'trusted_sites', $data);
	}
}
