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
			self::add_hooks_for_files(self::$createhookfile, 3, '%s created', 8, '%s created by %s');

			self::$createhookfired = false;
			self::$createhookfile = '';
		} else {
			// Add to l10n: $l->t('%s changed');
			// Add to l10n: $l->t('%s changed by %s');
			self::add_hooks_for_files($params['path'], 1, '%s changed', 6, '%s changed by %s');
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
		self::add_hooks_for_files($params['path'], 2, '%s deleted', 7, '%1$s deleted by %2$s');
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
		$affected_users = self::getUserPathsFromFile($file_path);
		foreach ($affected_users as $user => $path) {
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
	public static function getUserPathsFromFile($path) {
		$uidOwner = \OC\Files\Filesystem::getOwner(dirname($path));
		$fileInfo = \OC\Files\Filesystem::getFileInfo($path);
		$sourceFileInfo = $fileInfo->getData();
		$file_path = substr($sourceFileInfo['path'], strlen('files'));

		return \OCP\Share::getUsersSharingFile($file_path, $uidOwner, true, true);
	}

	/**
	 * @brief Store the share events
	 * @param array $params The hook params
	 */
	public static function share($params) {

		if ($params['itemType'] === 'file' || $params['itemType'] === 'folder') {
			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($params['fileTarget'])));
			$link_shared = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname('/Shared/'.$params['fileTarget'])));

			$sharedFrom = \OCP\User::getUser();
			$shareWith = $params['shareWith'];

			if(!empty($shareWith)) {
				$subject = 'You shared %s with %s';// Add to l10n: $l->t('You shared %s with %s');
				Data::send('files', $subject, array(substr($params['fileTarget'], 1), $shareWith), '', array(), $params['fileTarget'], $link, \OCP\User::getUser(), 4, Data::PRIORITY_MEDIUM );

				$subject = '%s shared %s with you';// Add to l10n: $l->t('%s shared %s with you');
				Data::send('files', $subject, array($sharedFrom, substr('/Shared'.$params['fileTarget'], 1)), '', array(), '/Shared/'.$params['fileTarget'], $link_shared, $shareWith, 5, Data::PRIORITY_MEDIUM);
			} else {
				$subject = 'You shared %s';// Add to l10n: $l->t('You shared %s');
				Data::send('files', $subject, array(substr($params['fileTarget'], 1)), '', array(), $params['fileTarget'], $link, \OCP\User::getUser(), 4, Data::PRIORITY_MEDIUM );
			}
		}
	}
}
