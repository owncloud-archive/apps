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
		$uid = $parameters['uid'];
		$samlBackend = new OC_USER_SAML();

		if ($samlBackend->auth->isAuthenticated()) {
			$attributes = $samlBackend->auth->getAttributes();

			if (array_key_exists($samlBackend->usernameMapping, $attributes) && $attributes[$samlBackend->usernameMapping][0] == $uid) {

				$attributes = $samlBackend->auth->getAttributes();

				if (array_key_exists($samlBackend->mailMapping, $attributes)) {
					$saml_email = $attributes[$samlBackend->mailMapping][0];
				}

				if (array_key_exists($samlBackend->groupMapping, $attributes)) {
					$saml_groups = $attributes[$samlBackend->groupMapping];
				}
				else if (!empty($samlBackend->defaultGroup)) {
					$saml_groups = array($samlBackend->defaultGroup);
					OCP\Util::writeLog('saml','Using default group "'.$samlBackend->defaultGroup.'" for the user: '.$uid, OC_Log::DEBUG);
				}

				if (!OCP\User::userExists($uid)) {
					if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $uid)) {
						OCP\Util::writeLog('saml','Invalid username "'.$uid.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OC_Log::DEBUG);
						return false;
					}
					else {
						$random_password = random_password();
						OCP\Util::writeLog('saml','Creating new user: '.$uid, OC_Log::DEBUG);
						OC_User::createUser($uid, $random_password);

						if(OCP\User::userExists($uid)) {
							if (isset($saml_email)) {
								update_mail($uid, $saml_email);

							}
							if (isset($saml_groups)) {
								update_groups($uid, $saml_groups, $samlBackend->protectedGroups, true);
							}
						}
					}
				}
				else {
					if ($samlBackend->updateUserData) {
						OCP\Util::writeLog('saml','Updating data of the user: '.$uid,OC_Log::DEBUG);
						if(isset($saml_email)) {
							update_mail($uid, $saml_email);
						}
						if (isset($saml_groups)) {
							update_groups($uid, $saml_groups, $samlBackend->protectedGroups, false);
						}
					}
				}
				return true;
			}
		}
		return false;
	}


	static public function logout($parameters) {
		$samlBackend = new OC_USER_SAML();
		if ($samlBackend->auth->isAuthenticated()) {
			OCP\Util::writeLog('saml','Executing SAML logout: '.$parameters['uid'],OC_Log::DEBUG);
			$samlBackend->auth->logout();
		}
		return true;
	}

}


function update_mail($uid, $email) {
	if ($email != OCP\Config::getUserValue($uid, 'settings', 'email', '')) {
		OCP\Config::setUserValue($uid, 'settings', 'email', $email);
		OCP\Util::writeLog('saml','Set email "'.$email.'" for the user: '.$uid, OC_Log::DEBUG);
	}
}


function update_groups($uid, $groups, $protected_groups=array(), $just_created=false) {

	if(!$just_created) {
		$old_groups = OC_Group::getUserGroups($uid);
		foreach($old_groups as $group) {
			if(!in_array($group, $protected_groups) && !in_array($group, $groups)) {
				OC_Group::removeFromGroup($uid,$group);
				OCP\Util::writeLog('saml','Removed "'.$uid.'" from the group "'.$group.'"', OC_Log::DEBUG);
			}
		}
	}

	foreach($groups as $group) {
		if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $group)) {
			OCP\Util::writeLog('saml','Invalid group "'.$group.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OC_Log::DEBUG);
		}
		else {
			if (!OC_Group::inGroup($uid, $group)) {
				if (!OC_Group::groupExists($group)) {
					OC_Group::createGroup($group);
					OCP\Util::writeLog('saml','New group created: '.$group, OC_Log::DEBUG);
				}
				OC_Group::addToGroup($uid, $group);
				OCP\Util::writeLog('saml','Added "'.$uid.'" to the group "'.$group.'"', OC_Log::DEBUG);
			}
		}
	}
}


function random_password()
{
	$valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$length = 20;
	$num_valid_chars = strlen($valid_chars);

	for ($i = 0; $i < $length; $i++) {
		$random_pick = mt_rand(1, $num_valid_chars);
		$random_char = $valid_chars[$random_pick-1];
		$random_string .= $random_char;
	}
	return $random_string;
}
