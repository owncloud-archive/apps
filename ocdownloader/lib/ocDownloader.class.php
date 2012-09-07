<?php

/**
* ownCloud - ocDownloader plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/**
 * This class manages ocDownloader with the database. 
 */
class OC_ocDownloader {
	
	/**
	 * Get user provider settings
	 * @return Array
	 */
	public static function getProvidersList() {
		$query = OC_DB::prepare("SELECT pr_id, pr_name, pr_logo FROM *PREFIX*ocdownloader_providers");
		$result = $query->execute()->fetchAll();
		if(count($result) > 0) {
			return $result;
		}
		return Array();
	}
	
	/**
	 * Get provider info
	 * @param $pr_id the provider id
	 * @return Array
	 */
	public static function getProvider($pr_id) {
		$query = OC_DB::prepare("SELECT pr_id, pr_name, pr_logo FROM *PREFIX*ocdownloader_providers WHERE pr_id = ?");
		$result = $query->execute(Array($pr_id))->fetchRow();
		if($result) {
			return $result;
		}
		return Array();
	}
	
	/**
	 * Add a provider (if not exists)
	 * @param $name Name of the provider
	 * @param $logo Logo of the provider
	 */
	public static function addProvider($name, $logo) {
		$query = OC_DB::prepare("SELECT pr_id FROM *PREFIX*ocdownloader_providers WHERE pr_name = ?");
		$result = $query->execute(Array($name))->fetchRow();
		if(!$result) {
			$query = OC_DB::prepare("INSERT INTO *PREFIX*ocdownloader_providers (pr_name,pr_logo) VALUES (?,?)");
			$query->execute(Array($name, $logo));
		}
	}
	
	/**
	 * Get User provider username and password
	 * @param $pr_id Provider id
	 * @return Array
	 */
	public static function getUserProviderInfo($pr_id) {
		$query = OC_DB::prepare("SELECT us_username, us_password FROM *PREFIX*ocdownloader_users_settings WHERE oc_uid = ? AND pr_fk = ?");
		$result = $query->execute(Array(OC_User::getUser(), $pr_id))->fetchRow();
		if($result) {
			return $result;
		}
		return Array();
	}
	
	/**
	 * Get a list of providers in the database
	 * @return Array
	 */
	public static function getUserProvidersList() {
		$query = OC_DB::prepare("SELECT p.pr_id, p.pr_name, u.us_id, u.us_username, u.us_password FROM *PREFIX*ocdownloader_providers p LEFT OUTER JOIN *PREFIX*ocdownloader_users_settings u ON p.pr_id = u.pr_fk WHERE u.oc_uid = ? OR u.oc_uid IS NULL");
		$result = $query->execute(Array(OC_User::getUser()))->fetchAll();
		if(count($result) > 0) {
			return $result;
		}
		return Array();
	}
	
	/**
	 * UPDATE user provider info
	 * @param $pr The provider id
	 * @param $username The user provider username
	 * @param $password The user provider password
	 */
	public static function updateUserInfo($pr,$username,$password) {
		$query = OC_DB::prepare("SELECT us_id FROM *PREFIX*ocdownloader_users_settings WHERE oc_uid = ? AND pr_fk = ?");
		$result = $query->execute(Array(OC_User::getUser(), $pr))->fetchRow();
		if($result) {
			$query = OC_DB::prepare("UPDATE *PREFIX*ocdownloader_users_settings SET us_username = ?, us_password = ? WHERE oc_uid = ? AND pr_fk = ?");
			$query->execute(Array($username, $password, OC_User::getUser(), $pr));
		}else{
			$query = OC_DB::prepare("INSERT INTO *PREFIX*ocdownloader_users_settings (oc_uid,pr_fk,us_username,us_password) VALUES (?,?,?,?)");
			$query->execute(Array(OC_User::getUser(), $pr, $username, $password));
		}
	}
	
	/**
	 * DELETE user provider info
	 * @param $pr The provider id
	 */
	public static function deleteUserInfo($pr) {
		$query = OC_DB::prepare("SELECT us_id FROM *PREFIX*ocdownloader_users_settings WHERE oc_uid = ? AND pr_fk = ?");
		$result = $query->execute(Array(OC_User::getUser(), $pr))->fetchAll();
		if(count($result) > 0) {
			$query = OC_DB::prepare("DELETE FROM *PREFIX*ocdownloader_users_settings WHERE us_id = ?");
			$query->execute(Array($result[0]['us_id']));
		}
	}
	
	/**
	 * Check app version. If version changes, add default records to table (Providers list)
	 * @param $v App version
	 * @return boolean
	 */
	public static function isUpToDate($v) {
		if(is_null($v))
			return FALSE;
			
		$query = OC_DB::prepare("SELECT conf_id FROM *PREFIX*ocdownloader_config WHERE conf_version = ? AND conf_key = ?");
		$result = $query->execute(Array((string)$v, 'init'))->fetchRow();
		if($result) {
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Initialize providers list
	 * @param $file Providers file list
	 */
	public static function initProviders($file) {
		$xml = new DOMDocument();
		$xml->load($file);
		
		$providers = $xml->getElementsByTagName('provider');
		foreach($providers as $provider) {
			$name_key = $provider->getElementsByTagName('name');
		  	$name_val = $name_key->item(0)->nodeValue;
			$logo_key = $provider->getElementsByTagName('logo');
		  	$logo_val = $logo_key->item(0)->nodeValue;
		  
		  	self::addProvider($name_val, $logo_val);
		}
		self::updateApplicationConfig('init', 'yes');
	}
	
	/**
	 * Insert or Update conf key for the actual version of the application
	 */
	public static function updateApplicationConfig($key, $val) {
		$query = OC_DB::prepare("SELECT conf_id FROM *PREFIX*ocdownloader_config WHERE conf_key = ?");
		$result = $query->execute(Array($key))->fetchRow();
		if($result) {
			$query = OC_DB::prepare("UPDATE *PREFIX*ocdownloader_config SET conf_version = ?, conf_val = ? WHERE conf_id = ?");
			$query->execute(Array((string)OC_Appconfig::getValue('ocdownloader', 'installed_version'), $val, $result['conf_id']));
		}else{
			$query = OC_DB::prepare("INSERT INTO *PREFIX*ocdownloader_config (conf_version,conf_key,conf_val) VALUES (?,?,?)");
			$query->execute(Array((string)OC_Appconfig::getValue('ocdownloader', 'installed_version'), $key, $val));
		}
	}
	
}
