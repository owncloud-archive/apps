<?php
/**
 * ownCloud - user_saml
 *
 * @author Sixto Martin <smartin@yaco.es>
 * @copyright 2012 Yaco Sistemas // CONFIA
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
 * This class contains all hooks.
 */
class OC_USER_SAML_Hooks {

	static public function post_login($parameters) {
		$uid = '';
		$userid = $parameters['uid'];
		$samlBackend = new OC_USER_SAML();

		if ($samlBackend->auth->isAuthenticated()) {
			$attributes = $samlBackend->auth->getAttributes();

			$usernameFound = false;
			foreach($samlBackend->usernameMapping as $usernameMapping) {
				if (array_key_exists($usernameMapping, $attributes) && !empty($attributes[$usernameMapping][0])) {
					$usernameFound = true;
					$uid = $attributes[$usernameMapping][0];
					OCP\Util::writeLog('saml','Authenticated user '.$uid,OCP\Util::DEBUG);
					break;
				}
			}

			if ($usernameFound && $uid == $userid) {
				if ($samlBackend->updateUserData) {
					$attrs = get_user_attributes($uid, $samlBackend);
					update_user_data($uid, $attrs);
				}
				return true;
			}
		}
		return false;
	}

	static public function post_createUser($parameters) {
		$uid = $parameters['uid'];
		$samlBackend = new OC_USER_SAML();
		if (!$samlBackend->updateUserData) {
			// Ensure that user data will be filled atleast once
			$attrs = get_user_attributes($uid, $samlBackend);
			update_user_data($uid, $attrs, true);
		}
	}

	static public function logout($parameters) {
		$samlBackend = new OC_USER_SAML();
		if ($samlBackend->auth->isAuthenticated()) {
			OCP\Util::writeLog('saml', 'Executing SAML logout', OCP\Util::DEBUG);
			unset($_COOKIE["SimpleSAMLAuthToken"]);
			setcookie('SimpleSAMLAuthToken', '', time()-3600, \OC::$WEBROOT);
			setcookie('SimpleSAMLAuthToken', '', time()-3600, \OC::$WEBROOT . '/');
			$samlBackend->auth->logout();
		}
		return true;
	}
}

function get_user_attributes($uid, $samlBackend) {
	$attributes = $samlBackend->auth->getAttributes();
	$result = array();

	$result['email'] = '';
	foreach ($samlBackend->mailMapping as $mailMapping) {
		if (array_key_exists($mailMapping, $attributes) && !empty($attributes[$mailMapping][0])) {
			$result['email'] = $attributes[$mailMapping][0];
			break;
		}
	}

	$result['display_name'] = '';
	foreach ($samlBackend->displayNameMapping as $displayNameMapping) {
		if (array_key_exists($displayNameMapping, $attributes) && !empty($attributes[$displayNameMapping][0])) {
			$result['display_name'] = $attributes[$displayNameMapping][0];
			break;
		}
	}

	$result['groups'] = array();
	foreach ($samlBackend->groupMapping as $groupMapping) {
		if (array_key_exists($groupMapping, $attributes) && !empty($attributes[$groupMapping])) {
			$result['groups'] = array_merge($result['groups'], $attributes[$groupMapping]);
		}
	}
	if (empty($result['groups']) && !empty($samlBackend->defaultGroup)) {
		$result['groups'] = array($samlBackend->defaultGroup);
		OCP\Util::writeLog('saml','Using default group "'.$samlBackend->defaultGroup.'" for the user: '.$uid, OCP\Util::DEBUG);
	}
	$result['protected_groups'] = $samlBackend->protectedGroups;

	$result['quota'] = '';
	if (!empty($samlBackend->quotaMapping)) {
		foreach ($samlBackend->quotaMapping as $quotaMapping) {
			if (array_key_exists($quotaMapping, $attributes) && !empty($attributes[$quotaMapping][0])) {
				$result['quota'] = $attributes[$quotaMapping][0];
				break;
			}
		}
		OCP\Util::writeLog('saml','Current quota: "'.$result['quota'].'" for user: '.$uid, OCP\Util::DEBUG);
	}
	if (empty($result['quota']) && !empty($samlBackend->defaultQuota)) {
		$result['quota'] = $samlBackend->defaultQuota;
		OCP\Util::writeLog('saml','Using default quota ('.$result['quota'].') for user: '.$uid, OCP\Util::DEBUG);
	}

	return $result;	
}


function update_user_data($uid, $attributes=array(), $just_created=false) {
	OC_Util::setupFS($uid);
	OCP\Util::writeLog('saml','Updating data of the user: '.$uid, OCP\Util::DEBUG);
	if(isset($attributes['email'])) {
		update_mail($uid, $attributes['email']);
	}
	if (isset($attributes['groups'])) {
		update_groups($uid, $attributes['groups'], $attributes['protected_groups'], $just_created);
	}
	if (isset($attributes['display_name'])) {
		update_display_name($uid, $attributes['display_name']);
	}
	if (isset($attributes['quota'])) {
		update_quota($uid, $attributes['quota']);
	}
}	


function update_mail($uid, $email) {
	$config = \OC::$server->getConfig();
	if ($email != $config->getUserValue($uid, 'settings', 'email', '')) {
		$config->setUserValue($uid, 'settings', 'email', $email);
		OCP\Util::writeLog('saml','Set email "'.$email.'" for the user: '.$uid, OCP\Util::DEBUG);
	}
}


function update_groups($uid, $groups, $protectedGroups=array(), $just_created=false) {

	if(!$just_created) {
		$old_groups = OC_Group::getUserGroups($uid);
		foreach($old_groups as $group) {
			if(!in_array($group, $protectedGroups) && !in_array($group, $groups)) {
				OC_Group::removeFromGroup($uid,$group);
				OCP\Util::writeLog('saml','Removed "'.$uid.'" from the group "'.$group.'"', OCP\Util::DEBUG);
			}
		}
	}

	foreach($groups as $group) {
		if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $group)) {
			OCP\Util::writeLog('saml','Invalid group "'.$group.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OCP\Util::DEBUG);
		}
		else {
			if (!OC_Group::inGroup($uid, $group)) {
				if (!OC_Group::groupExists($group)) {
					OC_Group::createGroup($group);
					OCP\Util::writeLog('saml','New group created: '.$group, OCP\Util::DEBUG);
				}
				OC_Group::addToGroup($uid, $group);
				OCP\Util::writeLog('saml','Added "'.$uid.'" to the group "'.$group.'"', OCP\Util::DEBUG);
			}
		}
	}
}


function update_display_name($uid, $displayName) {
	OC_User::setDisplayName($uid, $displayName);
}

function update_quota($uid, $quota) {
	if (!empty($quota)) {
		\OCP\Config::setUserValue($uid, 'files', 'quota', \OCP\Util::computerFileSize($quota));
	}
}
