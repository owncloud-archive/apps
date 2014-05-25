<?php

/**
 * ownCloud - Django Authentification Backend
 *
 * @author Florian Reinhard
 * @copyright 2012-2014 Florian Reinhard <florian.reinhard@googlemail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once 'django_auth/3rdparty/phpsec.crypt.php';
require_once 'django_auth/lib/djangodatabase.php';

/**
* @brief Class providing django users to ownCloud
* @see http://www.djangoproject.com
*
* Authentification backend to authenticate agains a django webapplication using
* django.contrib.auth.
*/
class OC_USER_DJANGO extends OC_User_Backend {

	public function __construct ()
	{
		$this->db = Djangodatabase::getDatabase();
	}

	/**
	* @brief Create a new user
	* @param $uid The username of the user to create
	* @param $password The password of the new user
	* @returns true/false
	*
	* Creates a new user. Basic checking of username is done in OC_User
	* itself, not in its subclasses.
	*/
	public function createUser($uid, $password) {
		OCP\Util::writeLog('OC_User_Django', 'Use the django webinterface to create users',\OCP\Util::ERROR);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief delete a user
	* @param $uid The username of the user to delete
	* @returns true/false
	*
	* Deletes a user
	*/
	public function deleteUser( $uid ) {
		OCP\Util::writeLog('OC_User_Django', 'Use the django webinterface to delete users',\OCP\Util::ERROR);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief Set password
	* @param $uid The username
	* @param $password The new password
	* @returns true/false
	*
	* Change the password of a user
	*/
	public function setPassword($uid, $password) {
		OCP\Util::writeLog('OC_User_Django', 'Use the django webinterface to change passwords',\OCP\Util::ERROR);
		return OC_USER_BACKEND_NOT_IMPLEMENTED;
	}

	/**
	* @brief Helper function for checkPassword
	* @param $str The String to be searched
	* @param $sub The String to be found
	* @returns true/false
	*/
	private function beginsWith($str,$sub) {
		return ( substr( $str, 0, strlen( $sub ) ) === $sub );
	}

	/**
	* @brief Check if the password is correct
	* @param $uid The username
	* @param $password The password
	* @returns true/false
	*
	* Check if the password is correct without logging in the user
	*/
	public function checkPassword($uid, $password) {
		if (!$this->db) return false;

		$query  = $this->db->prepare( 'SELECT `username`, `password` FROM `auth_user` WHERE `username` =  ?' );
		if ($query->execute( array( $uid))) {
			$row = $query->fetch();
			if (!empty($row)) {
				$storedHash=$row['password'];
				if (self::beginsWith($storedHash, 'sha1')) {
					$chunks = preg_split('/\$/', $storedHash,3);
					$salt   = $chunks[1];
					$hash   = $chunks[2];

					if (sha1($salt.$password) === $hash)
						return $uid;
					else
						return false;
				}
				elseif (self::beginsWith($storedHash, 'md5')) {
					$chunks = preg_split('/\$/', $storedHash,3);
					$salt   = $chunks[1];
					$hash   = $chunks[2];
					if (md5($salt.$password) === $hash)
						return $uid;
					else
						return false;
				}
				elseif (self::beginsWith($storedHash, 'pbkdf2')) {
					$chunks = preg_split('/\$/', $storedHash,4);
					list(,$algorithm) = preg_split('/_/', $chunks[0]);
					$iter = $chunks[1];
					$salt = $chunks[2];
					$hash = $chunks[3];

					if ($algorithm === 'sha1') {
						$digest_size = 20;
					}
					elseif ($algorithm === 'sha256') {
						$digest_size = 32;
					}
					else {
						OCP\Util::writeLog('OC_User_Django', 'The given hash algorithm for pkdf2 is not supported: '.$chunks[0],\OCP\Util::ERROR);
						return false;
					}

					$pkdf2 = phpsecCrypt::pbkdf2($password, $salt, $iter, $digest_size, $algorithm);
					if ($pkdf2 and (base64_encode ($pkdf2) === $hash)) {
						return $uid;
					}
					else {
						return false;
					}
				}
				elseif (self::beginsWith($storedHash, 'bcrypt')) {
					// get the salt
					preg_match('/(bcrypt(_sha256)?)\$(\$[^\$]+\$\d+\$.{22})/', $storedHash, $matches);
					$hasher = $matches[1];
					$salt = $matches[3];

					if ($hasher === 'bcrypt')
					{
						// Truncate the password as the password hasher django uses does
						$password = substr($password, 0, 72);
					}
					elseif ($hasher === 'bcrypt_sha256')
					{
						// SHA256 the password prior to passing it to crypt, like the password hasher django uses does
						// works around the password truncation of
						$password = hash("sha256", $password);
					}
					else
					{
						OCP\Util::writeLog('OC_User_Django', 'The given hash algorithm is not supported: '.$hasher,\OCP\Util::ERROR);
						return false;
					}

					// build hash string as stored in the database and compare it
					if  ($hasher . "$" . crypt($password, $salt) === $storedHash)
						return $uid;
					return
						false;
				}
			}
			else {
				return false;
			}
		}
	}

	/**
	* @brief Get a list of all users
	* @returns array with all active usernames
	*
	* Get a list of all users.
	*/
	public function getUsers($search = '', $limit = 10, $offset = 0) {
		if (!$this->db) return array();

		$query  = $this->db->prepare( 'SELECT `id`, `username`, `is_active` FROM `auth_user` WHERE `is_active`=1 ORDER BY `username`' );
		$users  = array();
		if ($query->execute()) {
			while ( $row = $query->fetch()) {
				$users[] = $row['username'];
			}
		}
		return $users;
	}

	/**
	* @brief check if a user exists
	* @param string $uid the username
	* @return boolean
	*/
	public function userExists($uid) {
		if (!$this->db) return false;

		$query  = $this->db->prepare( 'SELECT `username` FROM `auth_user` WHERE `username` = ? AND `is_active`=1' );
		if ($query->execute( array( $uid ))) {
			$row = $query->fetch();
			return !empty($row);
		}
		return false;
	}
}
