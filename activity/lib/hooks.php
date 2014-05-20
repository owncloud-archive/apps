<?php

/**
 * ownCloud - Activities App
 *
 * @author Frank Karlitschek, Joas Schilling
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
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

namespace OCA\Activity;

/**
 * @brief The class to handle the filesystem hooks
 */
class Hooks {
	public static $createhookfired = false;
	public static $createhookfile = '';

	/**
	 * @brief Registers the filesystem hooks for basic filesystem operations.
	 * All other events has to be triggered by the apps.
	 */
	public static function register() {
		\OCP\Util::connectHook('OC_Filesystem', 'post_create', 'OCA\Activity\Hooks', 'file_create');
		\OCP\Util::connectHook('OC_Filesystem', 'post_update', 'OCA\Activity\Hooks', 'file_update');
		\OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\Activity\Hooks', 'file_delete');
		\OCP\Util::connectHook('OCP\Share', 'post_shared', 'OCA\Activity\Hooks', 'share');

		// hooking up the activity manager
		$am = \OC::$server->getActivityManager();
		$am->registerConsumer(function() {
			return new Consumer();
		});
	}

	/**
	 * @brief Store the create hook events
	 * @param array $params The hook params
	 */
	public static function file_create($params) {
		self::add_hooks_for_files($params['path'], Data::TYPE_SHARE_CREATED, 'created_self', 'created_by');
	}

	/**
	 * @brief Store the update hook events
	 * @param array $params The hook params
	 */
	public static function file_update($params) {
		self::add_hooks_for_files($params['path'], Data::TYPE_SHARE_CHANGED, 'changed_self', 'changed_by');
	}

	/**
	 * @brief Store the delete hook events
	 * @param array $params The hook params
	 */
	public static function file_delete($params) {
		self::add_hooks_for_files($params['path'], Data::TYPE_SHARE_DELETED, 'deleted_self', 'deleted_self');
	}

	/**
	 * Creates the entries for file actions on $file_path
	 *
	 * @param string $file_path        The file that is being changed
	 * @param int    $activity_type    The activity type
	 * @param string $subject          The subject for the actor
	 * @param string $subject_by       The subject for other users (with "by $actor")
	 */
	public static function add_hooks_for_files($file_path, $activity_type, $subject, $subject_by) {
		// Do not add activities for .part-files
		if (substr($file_path, -5) === '.part') {
			return;
		}

		$affectedUsers = self::getUserPathsFromPath($file_path);
		$filteredStreamUsers = self::filterUsersBySetting(array_keys($affectedUsers), 'stream', $activity_type);
		$filteredEmailUsers = self::filterUsersBySetting(array_keys($affectedUsers), 'email', $activity_type);

		foreach ($affectedUsers as $user => $path) {
			if (empty($filteredStreamUsers[$user]) && empty($filteredEmailUsers[$user])) {
				continue;
			}

			if ($user === \OCP\User::getUser()) {
				$user_subject = $subject;
				$user_params = array($path);
			} else {
				$user_subject = $subject_by;
				$user_params = array($path, \OCP\User::getUser());
			}
			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($path)));

			// Add activities to stream
			if (!empty($filteredStreamUsers[$user])) {
				Data::send('files', $user_subject, $user_params, '', array(), $path, $link, $user, $activity_type, Data::PRIORITY_HIGH);
			}

			// Add activity to mail queue
			if (isset($filteredEmailUsers[$user])) {
				Data::storeMail('files', $user_subject, $user_params, $user, $activity_type, time() + $filteredEmailUsers[$user]);
			}
		}
	}

	/**
	 * Returns a "username => path" map for all affected users
	 *
	 * @param string $path
	 * @return array
	 */
	public static function getUserPathsFromPath($path) {
		list($file_path, $uidOwner) = self::getSourcePathAndOwner($path);
		return \OCP\Share::getUsersSharingFile($file_path, $uidOwner, true, true);
	}

	/**
	 * Return the source
	 *
	 * @param string $path
	 * @return array
	 */
	public static function getSourcePathAndOwner($path) {
		$uidOwner = \OC\Files\Filesystem::getOwner($path);

		if ($uidOwner != \OCP\User::getUser()) {
			\OC\Files\Filesystem::initMountPoints($uidOwner);
			$info = \OC\Files\Filesystem::getFileInfo($path);
			$ownerView = new \OC\Files\View('/'.$uidOwner.'/files');
			$path = $ownerView->getPath($info['fileid']);
		}

		return array($path, $uidOwner);
	}

	/**
	 * @brief Manage sharing events
	 * @param array $params The hook params
	 */
	public static function share($params) {
		if ($params['itemType'] === 'file' || $params['itemType'] === 'folder') {
			if ($params['shareWith']) {
				if ($params['shareType'] == \OCP\Share::SHARE_TYPE_USER) {
					self::shareFileOrFolderWithUser($params);
				} else if ($params['shareType'] == \OCP\Share::SHARE_TYPE_GROUP) {
					self::shareFileOrFolderWithGroup($params);
				}
			} else {
				self::shareFileOrFolder($params);
			}
		}
	}

	/**
	 * @brief Sharing a file or folder with a user
	 * @param array $params The hook params
	 */
	public static function shareFileOrFolderWithUser($params) {
		$file_path = \OC\Files\Filesystem::getPath($params['fileSource']);
		list($path, $uidOwner) = self::getSourcePathAndOwner($file_path);

		// Folder owner
		$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
			'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
		));

		// Add activity to stream
		if (Data::getUserSetting($uidOwner, 'stream', Data::TYPE_SHARED)) {
			Data::send('files', 'shared_user_self', array($file_path, $params['shareWith']), '', array(), $path, $link, $uidOwner, Data::TYPE_SHARED, Data::PRIORITY_MEDIUM );
		}
		// Add activity to mail queue
		if (Data::getUserSetting($uidOwner, 'email', Data::TYPE_SHARED)) {
			$latestSend = time() + Data::getUserSetting($uidOwner, 'setting', 'batchtime');
			Data::storeMail('files', 'shared_user_self', array($file_path, $params['shareWith']), $uidOwner, Data::TYPE_SHARED, $latestSend);
		}

		// New shared user
		$path = $params['fileTarget'];
		$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
			'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
		));

		// Add activity to stream
		if (Data::getUserSetting($params['shareWith'], 'stream', Data::TYPE_SHARED)) {
			Data::send('files', 'shared_with_by', array($path, \OCP\User::getUser()), '', array(), $path, $link, $params['shareWith'], Data::TYPE_SHARED, Data::PRIORITY_MEDIUM);
		}
		// Add activity to mail queue
		if (Data::getUserSetting($uidOwner, 'email', Data::TYPE_SHARED)) {
			$latestSend = Data::getUserSetting($params['shareWith'], 'setting', 'batchtime') + time();
			Data::storeMail('files', 'shared_with_by', array($path, \OCP\User::getUser()), $params['shareWith'], Data::TYPE_SHARED, $latestSend);
		}
	}

	/**
	 * @brief Sharing a file or folder with a group
	 * @param array $params The hook params
	 */
	public static function shareFileOrFolderWithGroup($params) {
		$file_path = \OC\Files\Filesystem::getPath($params['fileSource']);
		list($path, $uidOwner) = self::getSourcePathAndOwner($file_path);

		// Folder owner
		$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
			'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
		));

		// Add activity to stream
		if (Data::getUserSetting($uidOwner, 'stream', Data::TYPE_SHARED)) {
			Data::send('files', 'shared_group_self', array($file_path, $params['shareWith']), '', array(), $path, $link, $uidOwner, Data::TYPE_SHARED, Data::PRIORITY_MEDIUM );
		}
		// Add activity to mail queue
		if (Data::getUserSetting($uidOwner, 'email', Data::TYPE_SHARED)) {
			$latestSend = time() + Data::getUserSetting($uidOwner, 'setting', 'batchtime');
			Data::storeMail('files', 'shared_group_self', array($file_path, $params['shareWith']), $uidOwner, Data::TYPE_SHARED, $latestSend);
		}

		// Members of the new group
		$affectedUsers = array();
		$usersInGroup = \OC_Group::usersInGroup($params['shareWith']);
		foreach ($usersInGroup as $user) {
			$affectedUsers[$user] = $params['fileTarget'];
		}

		if (!empty($affectedUsers)) {
			$filteredStreamUsersInGroup = self::filterUsersBySetting($usersInGroup, 'stream', Data::TYPE_SHARED);
			$filteredEmailUsersInGroup = self::filterUsersBySetting($usersInGroup, 'email', Data::TYPE_SHARED);

			// Check when there was a naming conflict and the target is different
			// for some of the users
			$query = \OC_DB::prepare('SELECT `share_with`, `file_target` FROM `*PREFIX*share` WHERE `parent` = ? ');
			$result = $query->execute(array($params['id']));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog('OCA\Activity\Hooks::shareFileOrFolderWithGroup', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
			} else {
				while ($row = $result->fetchRow()) {
					$affectedUsers[$row['share_with']] = $row['file_target'];
				}
			}

			foreach ($affectedUsers as $user => $path) {
				if (empty($filteredStreamUsersInGroup[$user]) && empty($filteredEmailUsersInGroup[$user])) {
					continue;
				}

				$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
					'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
				));

				// Add activity to stream
				if (!empty($filteredStreamUsersInGroup[$user])) {
					Data::send('files', 'shared_with_by', array($path, \OCP\User::getUser()), '', array(), $path, $link, $user, Data::TYPE_SHARED, Data::PRIORITY_MEDIUM);
				}

				// Add activity to mail queue
				if (!empty($filteredEmailUsersInGroup[$user])) {
					$latestSend = time() + $filteredEmailUsersInGroup[$user];
					Data::storeMail('files', 'shared_with_by', array($path, \OCP\User::getUser()), $user, Data::TYPE_SHARED, $latestSend);
				}
			}
		}
	}

	/**
	 * @brief Sharing a file or folder via link/public
	 * @param array $params The hook params
	 */
	public static function shareFileOrFolder($params) {
		$path = \OC\Files\Filesystem::getPath($params['fileSource']);
		$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
			'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
		));

		if (Data::getUserSetting(\OCP\User::getUser(), 'stream', Data::TYPE_SHARED)) {
			Data::send('files', 'shared_link_self', array($path), '', array(), $path, $link, \OCP\User::getUser(), Data::TYPE_SHARED, Data::PRIORITY_MEDIUM);
		}
	}

	/**
	 * Filters the given user array by their notification setting
	 *
	 * @param array $users
	 * @param string $method
	 * @param string $type
	 * @return array Returns a "username => b:true" Map for method = stream
	 *               Returns a "username => i:batchtime" Map for method = email
	 */
	public static function filterUsersBySetting($users, $method, $type) {
		if (empty($users) || !is_array($users)) return array();

		$filteredUsers = array();

		$chunked_users = array_chunk($users, 50, true);
		$placeholders_50 = implode(',', array_fill(0, 50, '?'));

		foreach ($chunked_users as $chunk) {
			$placeholders = (sizeof($chunk) == 50) ? $placeholders_50 : implode(',', array_fill(0, sizeof($chunk), '?'));

			$query = \OCP\DB::prepare(
				'SELECT `userid`, `configvalue` '
				. ' FROM `*PREFIX*preferences` '
				. ' WHERE `appid` = ? AND `configkey` = ? AND `userid` IN (' . $placeholders . ')');
			$result = $query->execute(array_merge(array(
				'activity',
				'notify_' . $method . '_' . $type,
			), $chunk));

			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog('OCA\Activity\Hooks::filterUsersBySetting', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
			} else {
				while ($row = $result->fetchRow()) {
					if ($row['configvalue']) {
						$filteredUsers[$row['userid']] = true;
					}
					unset($users[array_search($row['userid'], $chunk)]);
				}
			}
		}

		// Get the batch time setting from the database
		if ($method == 'email') {
			$chunkedFilteredUsers = array_chunk(array_keys($filteredUsers), 50);
			foreach ($chunkedFilteredUsers as $chunk) {
				$placeholders = (sizeof($chunk) == 50) ? $placeholders_50 : implode(',', array_fill(0, sizeof($chunk), '?'));

				$query = \OCP\DB::prepare(
					'SELECT `userid`, `configvalue` '
					. ' FROM `*PREFIX*preferences` '
					. ' WHERE `appid` = ? AND `configkey` = ? AND `userid` IN (' . $placeholders . ')');
				$result = $query->execute(array_merge(array(
					'activity',
					'notify_setting_batchtime',
				), $chunk));

				if (\OCP\DB::isError($result)) {
					\OCP\Util::writeLog('OCA\Activity\Hooks::filterUsersBySetting', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
				} else {
					while ($row = $result->fetchRow()) {
						$filteredUsers[$row['userid']] = $row['configvalue'];
					}
				}
			}
		}

		if (!empty($users)) {
			// If the setting is enabled by default,
			// we add all users that didn't set the preference yet.
			if (\OCA\Activity\Data::getUserDefaultSetting($method, $type)) {
				foreach ($users as $user) {
					if ($method == 'stream') {
						$filteredUsers[$user] = true;
					} else {
						$filteredUsers[$user] = \OCA\Activity\Data::getUserDefaultSetting('setting', 'batchtime');
					}
				}
			}
		}

		return $filteredUsers;
	}
}
