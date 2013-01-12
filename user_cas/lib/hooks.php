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
 * This class contains all hooks.
 */
class OC_USER_CAS_Hooks {

	static public function post_login($parameters) {
		$uid = $parameters['uid'];
		$casBackend = new OC_USER_CAS();

		if (phpCAS::isAuthenticated()) {
			$attributes = phpCAS::getAttributes();

			if (array_key_exists($casBackend->usernameMapping, $attributes) && $attributes[$casBackend->usernameMapping][0] == $uid) {
				if (array_key_exists($casBackend->mailMapping, $attributes)) {
					$cas_email = $attributes[$casBackend->mailMapping][0];
				}

				if (array_key_exists($casBackend->groupMapping, $attributes)) {
					$cas_groups = $attributes[$casBackend->groupMapping];
				}
				else if (!empty($casBackend->defaultGroup)) {
					$cas_groups = array($casBackend->defaultGroup);
					OC_Log::write('cas','Using default group "'.$casBackend->defaultGroup.'" for the user: '.$uid, OC_Log::DEBUG);
				}

				if (!OC_User::userExists($uid)) {
					if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $uid)) {
						OC_Log::write('cas','Invalid username "'.$uid.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OC_Log::DEBUG);
						return false;
					}
					else {
						$random_password = random_password();
						OC_Log::write('cas','Creating new user: '.$uid, OC_Log::DEBUG);
						OC_User::createUser($uid, $random_password);

						if(OC_User::userExists($uid)) {
							if (isset($cas_email)) {
								update_mail($uid, $cas_email);

							}
							if (isset($cas_groups)) {
								update_groups($uid, $cas_groups, $casBackend->protectedGroups, true);
							}
						}
					}
				}
				else {
					if ($casBackend->updateUserData) {
						OC_Log::write('cas','Updating data of the user: '.$uid,OC_Log::DEBUG);
						if(isset($cas_email)) {
							update_mail($uid, $cas_email);
						}
						if (isset($cas_groups)) {
							update_groups($uid, $cas_groups, $casBackend->protectedGroups, false);
						}
					}
				}
				return true;
			}
		}
		return false;
	}


	static public function logout($parameters) {
		$casBackend = new OC_USER_CAS();
		if (phpCAS::isAuthenticated()) {
			OC_Log::write('cas','Executing CAS logout: '.$parameters['uid'],OC_Log::DEBUG);
			phpCAS::logout();
		}
		return true;
	}

}


function update_mail($uid, $email) {
	if ($email != OC_Preferences::getValue($uid, 'settings', 'email', '')) {
		OC_Preferences::setValue($uid, 'settings', 'email', $email);
		OC_Log::write('cas','Set email "'.$email.'" for the user: '.$uid, OC_Log::DEBUG);
	}
}


function update_groups($uid, $groups, $protected_groups=array(), $just_created=false) {

	if(!$just_created) {
		$old_groups = OC_Group::getUserGroups($uid);
		foreach($old_groups as $group) {
			if(!in_array($group, $protected_groups) && !in_array($group, $groups)) {
				OC_Group::removeFromGroup($uid,$group);
				OC_Log::write('cas','Removed "'.$uid.'" from the group "'.$group.'"', OC_Log::DEBUG);
			}
		}
	}

	foreach($groups as $group) {
		if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $group)) {
			OC_Log::write('cas','Invalid group "'.$group.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OC_Log::DEBUG);
		}
		else {
			if (!OC_Group::inGroup($uid, $group)) {
				if (!OC_Group::groupExists($group)) {
					OC_Group::createGroup($group);
					OC_Log::write('cas','New group created: '.$group, OC_Log::DEBUG);
				}
				OC_Group::addToGroup($uid, $group);
				OC_Log::write('cas','Added "'.$uid.'" to the group "'.$group.'"', OC_Log::DEBUG);
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
