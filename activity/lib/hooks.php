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
		//Listen to create file signal
		\OCP\Util::connectHook('OC_Filesystem', 'post_create', "OCA\Activity\Hooks", "file_create");

		//Listen to write file signal
		\OCP\Util::connectHook('OC_Filesystem', 'post_write', "OCA\Activity\Hooks", "file_write");

		//Listen to delete file signal
		\OCP\Util::connectHook('OC_Filesystem', 'delete', "OCA\Activity\Hooks", "file_delete");

		//Listen to share signal
		\OCP\Util::connectHook('OCP\Share', 'post_shared', "OCA\Activity\Hooks", "share");

		// hooking up the activity manager
		if (property_exists('OC', 'server')) {
			if (method_exists(\OC::$server, 'getActivityManager')) {
				$am = \OC::$server->getActivityManager();
				$am->registerConsumer(function() {
					return new Consumer();
				});
			}
		}
	}

	/**
	 * @brief Store the write hook events
	 * @param array $params The hook params
	 */
	public static function file_write($params) {
		if ( self::$createhookfired ) {
			// Add to l10n: $l->t('%s created');
			// Add to l10n: $l->t('%s created by %s');
			self::add_hooks_for_files(self::$createhookfile, Data::TYPE_SHARE_CREATED, '%s created', Data::TYPE_SHARE_CREATED_BY, '%s created by %s');

			self::$createhookfired = false;
			self::$createhookfile = '';
		} else {
			// Add to l10n: $l->t('%s changed');
			// Add to l10n: $l->t('%s changed by %s');
			self::add_hooks_for_files($params['path'], Data::TYPE_SHARE_CHANGED, '%s changed', Data::TYPE_SHARE_CHANGED_BY, '%s changed by %s');
		}
	}

	/**
	 * @brief Store the create hook events
	 * @param array $params The hook params
	 */
	public static function file_create($params) {
		// remember the create event for later consumption
		self::$createhookfired = true;
		self::$createhookfile = $params['path'];
	}

	/**
	 * @brief Store the delete hook events
	 * @param array $params The hook params
	 */
	public static function file_delete($params) {
		// Add to l10n: $l->t('%s deleted');
		// Add to l10n: $l->t('%s deleted by %s');
		self::add_hooks_for_files($params['path'], Data::TYPE_SHARE_DELETED, '%s deleted', Data::TYPE_SHARE_DELETED_BY, '%1$s deleted by %2$s');
	}

	/**
	 * Creates the entries for file actions on $file_path
	 *
	 * @param string $file_path        The file that is being changed
	 * @param int    $activity_type    The activity type for the actor
	 * @param string $subject          The subject for the actor
	 * @param int    $activity_type_by The activity type for other users (with "by $actor")
	 * @param string $subject_by       The subject for other users (with "by $actor")
	 */
	public static function add_hooks_for_files($file_path, $activity_type, $subject, $activity_type_by, $subject_by) {
		$affectedUsers = self::getUserPathsFromPath($file_path);
		foreach ($affectedUsers as $user => $path) {
			if ($user === \OCP\User::getUser()) {
				$user_subject = $subject;
				$user_type = $activity_type;
				$user_params = array($path);
			} else {
				$user_subject = $subject_by;
				$user_type = $activity_type_by;
				$user_params = array($path, \OCP\User::getUser());
			}

			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($path)));
			Data::send('files', $user_subject, $user_params, '', array(), $path, $link, $user, $user_type, Data::PRIORITY_HIGH);
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
		$uidOwner = \OC\Files\Filesystem::getOwner(dirname($path));
		$fileInfo = \OC\Files\Filesystem::getFileInfo($path);
		$sourceFileInfo = $fileInfo->getData();
		$file_path = substr($sourceFileInfo['path'], strlen('files'));

		return array($file_path, $uidOwner);
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
		$subject = 'You shared %s with %s';// Add to l10n: $l->t('You shared %s with %s');
		Data::send('files', $subject, array($file_path, $params['shareWith']), '', array(), $path, $link, $uidOwner, Data::TYPE_SHARED, Data::PRIORITY_MEDIUM );

		// New shared user
		$path = '/Shared' . $params['fileTarget'];
		$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
			'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
		));
		$subject = '%s shared %s with you';// Add to l10n: $l->t('%s shared %s with you');
		Data::send('files', $subject, array(\OCP\User::getUser(), $path), '', array(), $path, $link, $params['shareWith'], Data::TYPE_SHARED_BY, Data::PRIORITY_MEDIUM);
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
		$subject = 'You shared %s with group %s';// Add to l10n: $l->t('You shared %s with group %s');
		Data::send('files', $subject, array($file_path, $params['shareWith']), '', array(), $path, $link, $uidOwner, Data::TYPE_SHARED, Data::PRIORITY_MEDIUM );

		// Members of the new group
		$subject = '%s shared %s with you';// Add to l10n: $l->t('%s shared %s with you');
		$affectedUsers = array();
		foreach (\OC_Group::usersInGroup($params['shareWith']) as $user) {
			$affectedUsers[$user] = '/Shared' . $params['fileTarget'];
		}

		if (!empty($affectedUsers)) {
			// Check when there was a naming conflict and the target is different
			// for some of the users
			$query = \OC_DB::prepare('SELECT `share_with`, `file_target` FROM `*PREFIX*share` WHERE `parent` = ? ');
			$result = $query->execute(array($params['id']));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog('OCA\Activity\Hooks', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
			} else {
				while ($row = $result->fetchRow()) {
					$affectedUsers[$user] = '/Shared' . $row['file_target'];
				}
			}

			foreach ($affectedUsers as $user => $path) {
				$link = \OCP\Util::linkToAbsolute('files', 'index.php', array(
					'dir' => ($params['itemType'] === 'file') ? dirname($path) : $path,
				));

				Data::send('files', $subject, array(\OCP\User::getUser(), $path), '', array(), $path, $link, $user, Data::TYPE_SHARED_BY, Data::PRIORITY_MEDIUM);
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

		// Add to l10n: $l->t('You shared %s');
		$subject = 'You shared %s';

		Data::send('files', $subject, array($path), '', array(), $path, $link, \OCP\User::getUser(), Data::TYPE_SHARED, Data::PRIORITY_MEDIUM);
	}
}
