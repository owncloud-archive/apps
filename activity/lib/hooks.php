<?php

/**
 * ownCloud - Activities App
 *
 * @author Frank Karlitschek
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

		//Listen to delete file signal
		\OCP\Util::connectHook('OC_Filesystem', 'delete', "OCA\Activity\Hooks", "file_delete");

		//Listen to write file signal
		\OCP\Util::connectHook('OC_Filesystem', 'post_write', "OCA\Activity\Hooks", "file_write");

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
		$l = \OC_L10N::get('activity');
		if( self::$createhookfired ) {
			$params['path'] = self::$createhookfile;

			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($params['path'])));
			$subject = $l->t('%s created', array(substr($params['path'], 1)));
			Data::send('files', $subject, NULL, '', array(), $params['path'], $link, \OCP\User::getUser(), 3);

			if(substr($params['path'],0,8)=='/Shared/') {
				$uidOwner = \OC\Files\Filesystem::getOwner($params['path']);
				$realfile=substr($params['path'],7);
				$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($realfile)));
				$subject = $l->t('%s created by %s', array($realfile,\OCP\User::getUser()));
				Data::send('files', $subject, NULL, '', array(), $realfile, $link, $uidOwner, 8, Data::PRIORITY_HIGH);
			}
			self::$createhookfired = false;
			self::$createhookfile = '';
			
		} else {

			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($params['path'])));
			$subject = $l->t('%s changed', array(substr($params['path'], 1)));
			Data::send('files', $subject, NULL, '', array(), $params['path'], $link, \OCP\User::getUser(), 1);
		
			if(substr($params['path'],0,8)=='/Shared/') {
				$uidOwner = \OC\Files\Filesystem::getOwner($params['path']);
				$realfile=substr($params['path'],7);
				$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($realfile)));
				$subject = $l->t('%s changed by %s', array($realfile,\OCP\User::getUser()));
				Data::send('files', $subject, NULL, '', array(), $realfile, $link, $uidOwner, 6, Data::PRIORITY_HIGH);
			}
		}
		
	}

	/**
	 * @brief Store the delete hook events
	 * @param array $params The hook params
	 */
	public static function file_delete($params) {
		$l = \OC_L10N::get('activity');
		$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($params['path'])));
		$subject = $l->t('%s deleted', array(substr($params['path'], 1)));
		Data::send('files', $subject, NULL, '', array(), $params['path'], $link, \OCP\User::getUser(), 2);

		if(substr($params['path'],0,8)=='/Shared/') {
			$uidOwner = \OC\Files\Filesystem::getOwner($params['path']);
			$realfile=substr($params['path'],7);
			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($realfile)));
			$subject = $l->t('%s deleted by %s', array($realfile,\OCP\User::getUser()));
			Data::send('files', $subject, NULL, '', array(), $realfile, $link, $uidOwner, 7, Data::PRIORITY_HIGH);
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
	 * @brief Store the share events
	 * @param array $params The hook params
	 */
	public static function share($params) {
		$l = \OC_L10N::get('activity');
		if ($params['itemType'] === 'file' || $params['itemType'] === 'folder') {
	
			$link = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname($params['fileTarget'])));
			$link2 = \OCP\Util::linkToAbsolute('files', 'index.php', array('dir' => dirname('/Shared/'.$params['fileTarget'])));

			$sharedFrom = \OCP\User::getUser();
			$shareWith = $params['shareWith'];

			if(!empty($shareWith)) {
				$subject = $l->t('You shared %s with %s', array(substr($params['fileTarget'], 1), $shareWith));
				Data::send('files', $subject, NULL, '', array(), $params['fileTarget'], $link, \OCP\User::getUser(), 4, Data::PRIORITY_MEDIUM );
			
				$subject = $l->t('%s shared %s with you', array($sharedFrom, substr('/Shared'.$params['fileTarget'], 1)));
				Data::send('files', $subject, NULL, '', array(), '/Shared/'.$params['fileTarget'], $link2, $shareWith, 5, Data::PRIORITY_MEDIUM);
			} else {
				$subject = $l->t('You shared %s', array(substr($params['fileTarget'], 1)));
				Data::send('files', $subject, NULL, '', array(), $params['fileTarget'], $link, \OCP\User::getUser(), 4, Data::PRIORITY_MEDIUM );
			}
			
		}

	}


}
