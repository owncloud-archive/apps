<?php

/**
 * @see Zend_OpenId_Provider_User
 */
require_once "Zend/OpenId/Provider/User.php";

class OC_OpenIdProviderUserSession extends Zend_OpenId_Provider_User
{
	/**
	 * Stores information about logged in user in session data
	 *
	 * @param string $id user identity URL
	 * @return bool
	 */
	public function setLoggedInUser($id)
	{
		$_SESSION['user_openid_url'] = $id;
		return true;
	}

	/**
	 * Returns identity URL of logged in user or false
	 *
	 * @return mixed
	 */
	public function getLoggedInUser()
	{
		if( isset($_SESSION['user_openid_url'])){
			return $_SESSION['user_openid_url'];
		}
		return false;
	}

	/**
	 * Performs logout. Clears information about logged in user.
	 *
	 * @return bool
	 */
	public function delLoggedInUser()
	{
		unset($_SESSION['user_openid_url']);
		return true;
	}

}
