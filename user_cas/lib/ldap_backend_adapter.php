<?php

/**
 * ownCloud - user_cas
 *
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @copyright Sixto Martin Garcia. 2012
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


/**
 * This class offers convenient access to the primary LDAP server used by the
 * LDAP user and group backend.
 *
 * This class come from another owncloud plugins : https://github.com/AndreasErgenzinger/user_shibboleth
 */


namespace OCA\user_cas\lib;

class LdapBackendAdapter extends \OCA\user_ldap\USER_LDAP {

	private $enabled;
	private $connected = false;
	private $connection;
	protected $usermanager;
	protected $access;


	function __construct() {
		$this->enabled = (\OCP\Config::getAppValue('user_cas', 'cas_link_to_ldap_backend', false) === 'on') &&
				 \OCP\App::isEnabled('user_cas')  && \OCP\App::isEnabled('user_ldap');
	}


	private function connect() {
		if (!$this->connected) {
			$prefix='s01';
			$this->ldap = new \OCA\user_ldap\lib\LDAP();
			$this->connection = new \OCA\user_ldap\lib\Connection($this->ldap,$prefix);
			$this->usermanager = new \OCA\user_ldap\lib\user\Manager(
				\OC::$server->getConfig(),
				new \OCA\user_ldap\lib\FilesystemHelper(),
				new \OCA\user_ldap\lib\LogWrapper(),
				\OC::$server->getAvatarManager(),
				new \OCP\Image()
			);
			$this->access = new \OCA\user_ldap\lib\Access($this->connection, $this->ldap, $this->usermanager);
			$this->connected = true;
		}
	}


	/**
	 * @brief returns true if and only if a user with the given uuid exists in the LDAP
	 * @param string a unique user identifier
	 * @return a boolean value
	 */
	public function uuidExists($uuid) {
		//check backend status
		if (!$this->enabled) {
			return false;
		}

		//check tables
		$query = \OCP\DB::prepare('SELECT COUNT(*) FROM *PREFIX*ldap_user_mapping WHERE owncloud_name = ?');
		$result = $query->execute(array($uuid));
		if (!\OCP\DB::isError($result)) {
			$count = $result->fetchAll(\PDO::FETCH_COLUMN, 0);
			if ($count[0] === 1) {
				return true;
			}
		}

		//check primary LDAP server
		$this->connect();
		$uuid = $this->access->escapeFilterPart($uuid);
		$filter = \OCP\Util::mb_str_replace(
			'%uid', $uuid, $this->access->connection->ldapLoginFilter, 'UTF-8');
		$result = $this->access->fetchListOfUsers($filter, $this->connection->ldapUuidAttribute);

		if (count($result) === 1 && $result[0]['count'] === 1) {
			return true;
		}
		return false;
	}


	public function getUuid($uid) {
		//check backend status
		if (!$this->enabled) {
			return false;
		}

		$this->connect();
		$uid = $this->access->escapeFilterPart($uid);

		//find out dn of the user name
		$filter = \OCP\Util::mb_str_replace(
			'%uid', $uid, $this->access->connection->ldapLoginFilter, 'UTF-8');
		$ldap_users = $this->access->fetchListOfUsers($filter, 'dn');
		if(count($ldap_users) < 1) {
			return false;
		}
		$dn = $ldap_users[0];

		//do we have a username for him/her?
		$ocname = $this->access->dn2username($dn);

		if($ocname) {
			\OCP\Config::setUserValue($ocname, 'user_ldap','firstLoginAccomplished', 1);
			return $ocname;
		}
		return false;
	}


	public function initializeUser($uuid) {
		//check backend status
		if (!$this->enabled) {
			return false;
		}

		$this->connect();
		$uuid = $this->access->escapeFilterPart($uuid);
		$filter = \OCP\Util::mb_str_replace(
			'%uid', $uuid, $this->access->connection->ldapLoginFilter, 'UTF-8');
		$users = $this->getUsers($filter, 'dn');
		if (count($users) === 1 && $users[0]['count'] === 1) {
			$dn = $users[0][0];
			$this->ldap->dn2ocname($dn);//creates table entries and folders
			return true;
		}
		return false;
	}

}
