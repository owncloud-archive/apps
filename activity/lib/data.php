<?php

/**
 * ownCloud - Activity App
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
 * @brief Class for managing the data in the activities
 */
class Data
{
	const PRIORITY_VERYLOW 	= 10;
	const PRIORITY_LOW	= 20;
	const PRIORITY_MEDIUM	= 30;
	const PRIORITY_HIGH	= 40;
	const PRIORITY_VERYHIGH	= 50;

	const TYPE_SHARED = 'shared';
	const TYPE_SHARE_EXPIRED = 'share_expired';
	const TYPE_SHARE_UNSHARED = 'share_unshared';

	const TYPE_SHARE_CREATED = 'file_created';
	const TYPE_SHARE_CHANGED = 'file_changed';
	const TYPE_SHARE_DELETED = 'file_deleted';
	const TYPE_SHARE_RESHARED = 'file_reshared';

	const TYPE_SHARE_DOWNLOADED = 'file_downloaded';
	const TYPE_SHARE_UPLOADED = 'file_uploaded';

	const TYPE_STORAGE_QUOTA_90 = 'storage_quota_90';
	const TYPE_STORAGE_FAILURE = 'storage_failure';

	public static function getNotificationTypes($l) {
		return array(
			\OCA\Activity\Data::TYPE_SHARED => $l->t('A file or folder has been <strong>shared</strong>'),
//			\OCA\Activity\Data::TYPE_SHARE_UNSHARED => $l->t('Previously shared file or folder has been <strong>unshared</strong>'),
//			\OCA\Activity\Data::TYPE_SHARE_EXPIRED => $l->t('Expiration date of shared file or folder <strong>expired</strong>'),
			\OCA\Activity\Data::TYPE_SHARE_CREATED => $l->t('A new file or folder has been <strong>created</strong> in a shared folder'),
			\OCA\Activity\Data::TYPE_SHARE_CHANGED => $l->t('A file or folder has been <strong>changed</strong> in a shared folder'),
			\OCA\Activity\Data::TYPE_SHARE_DELETED => $l->t('A file or folder has been <strong>deleted</strong> from a shared folder'),
//			\OCA\Activity\Data::TYPE_SHARE_RESHARED => $l->t('A file or folder has been <strong>reshared</strong>'),
//			\OCA\Activity\Data::TYPE_SHARE_DOWNLOADED => $l->t('A file or folder shared via link has been <strong>downloaded</strong>'),
//			\OCA\Activity\Data::TYPE_SHARE_UPLOADED => $l->t('A file has been <strong>uploaded</strong> into a folder shared via link'),
//			\OCA\Activity\Data::TYPE_STORAGE_QUOTA_90 => $l->t('<strong>Storage usage</strong> is at 90%%'),
//			\OCA\Activity\Data::TYPE_STORAGE_FAILURE => $l->t('An <strong>external storage</strong> has an error'),
		);
	}

	public static function getUserDefaultSetting($method, $type) {
		$settings = self::getUserDefaultSettings($method);
		return in_array($type, $settings);
	}

	public static function getUserDefaultSettings($method) {
		$settings = array();
		switch ($method) {
			case 'stream':
				$settings[] = Data::TYPE_SHARE_CREATED;
				$settings[] = Data::TYPE_SHARE_CHANGED;
				$settings[] = Data::TYPE_SHARE_DELETED;
//				$settings[] = Data::TYPE_SHARE_RESHARED;
//
//				$settings[] = Data::TYPE_SHARE_DOWNLOADED;

			case 'email':
				$settings[] = Data::TYPE_SHARED;
//				$settings[] = Data::TYPE_SHARE_EXPIRED;
//				$settings[] = Data::TYPE_SHARE_UNSHARED;
//
//				$settings[] = Data::TYPE_SHARE_UPLOADED;
//
//				$settings[] = Data::TYPE_STORAGE_QUOTA_90;
//				$settings[] = Data::TYPE_STORAGE_FAILURE;
		}

		return $settings;
	}

	/**
	 * @brief Send an event into the activity stream
	 * @param string $app The app where this event is associated with
	 * @param string $subject A short description of the event
	 * @param string $message A longer description of the event
	 * @param string $file The file including path where this event is associated with. (optional)
	 * @param string $link A link where this event is associated with (optional)
	 * @return boolean
	 */
	public static function send($app, $subject, $subjectparams = array(), $message = '', $messageparams = array(), $file = '', $link = '', $affecteduser = '', $type = 0, $prio = Data::PRIORITY_MEDIUM) {
		$timestamp = time();
		$user = \OCP\User::getUser();
		
		if ($affecteduser === '') {
			$auser = \OCP\User::getUser();
		} else {
			$auser = $affecteduser;
		}

		// store in DB
		$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		$query->execute(array($app, $subject, serialize($subjectparams), $message, serialize($messageparams), $file, $link, $user, $auser, $timestamp, $prio, $type));

		// call the expire function only every 1000x time to preserve performance.
		if (rand(0, 1000) == 0) {
			Data::expire();
		}

		// fire a hook so that other apps like notification systems can connect
		\OCP\Util::emitHook('OC_Activity', 'post_event', array('app' => $app, 'subject' => $subject, 'user' => $user, 'affecteduser' => $affecteduser, 'message' => $message, 'file' => $file, 'link'=> $link, 'prio' => $prio, 'type' => $type));

		return true;
	}

	public static function prepare_files_params($app, $text, $params, $file_position = false)
	{
		if ($app === 'files') {
			$prepared_params = array();
			foreach ($params as $i => $param) {
				if ($file_position === $i) {
					// Remove the path from the file string
					$param = substr($param, strrpos($param, '/') + 1);
				}
				$prepared_params[] = $param;
			}
			return $prepared_params;
		}
		return $params;
	}

	/**
	 * @brief Translate an event string with the translations from the app where it was send from
	 * @param string $app The app where this event comes from
	 * @param string $text The text including placeholders
	 * @param array $params The parameter for the placeholder
	 * @return string translated
	 */
	public static function translation($app, $text, $params) {
		if (!$text) {
			return '';
		}

		if ($app === 'files') {

			$l = \OCP\Util::getL10N('activity');
			$params = self::prepare_files_params($app, $text, $params, 0);
			if ($text === 'created_self') {
				return $l->t('You created %1$s', $params);
			}
			else if ($text === 'created_by') {
				return $l->t('%2$s created %1$s', $params);
			}
			else if ($text === 'changed_self') {
				return $l->t('You changed %1$s', $params);
			}
			else if ($text === 'changed_by') {
				return $l->t('%2$s changed %1$s', $params);
			}
			else if ($text === 'deleted_self') {
				return $l->t('You deleted %1$s', $params);
			}
			else if ($text === 'deleted_by') {
				return $l->t('%2$s deleted %1$s', $params);
			}
			else if ($text === 'shared_user_self') {
				return $l->t('You shared %1$s with %2$s', $params);
			}
			else if ($text === 'shared_group_self') {
				return $l->t('You shared %1$s with group %2$s', $params);
			}
			else if ($text === 'shared_with_by') {
				return $l->t('%2$s shared %1$s with you', $params);
			}
			else if ($text === 'shared_link_self') {
				return $l->t('You shared %1$s', $params);
			}

			return $text;
		} else {
			$l = \OCP\Util::getL10N($app);
			return $l->t($text, $params);
		}
	}

	public static function getUserSetting($user, $method, $type) {
		return \OCP\Config::getUserValue(
			$user,
			'activity',
			'notify_' . $method . '_' . $type,
			\OCA\Activity\Data::getUserDefaultSetting($method, $type)
		);
	}

	/**
	 * @param string	$user	Name of the user
	 * @param string	$method	Should be one of 'stream', 'email'
	 * @return string	Part of the SQL query limiting the activities
	 */
	public static function getUserNotificationTypesQuery($user, $method) {
		$l = \OC_L10N::get('activity');
		$types = \OCA\Activity\Data::getNotificationTypes($l);

		foreach ($types as $type => $desc) {
			if (self::getUserSetting($user, $method, $type)) {
				$user_activities[] = $type;
			}
		}

		// We don't want to display any activities
		if (empty($user_activities)) {
			return '1 = 0';
		}

		return "`type` IN ('" . implode("','", $user_activities) . "')";
	}

	/**
	 * @brief Read a list of events from the activity stream
	 * @param int $start The start entry
	 * @param int $count The number of statements to read
	 * @return array
	 */
	public static function read($start, $count) {
		// get current user
		$user = \OCP\User::getUser();
		$limit_activities_type = 'AND ' . self::getUserNotificationTypesQuery($user, 'stream');

		// fetch from DB
		$query = \OCP\DB::prepare(
			'SELECT `activity_id`, `app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `timestamp`, `priority`, `type`, `user`, `affecteduser` '
			. ' FROM `*PREFIX*activity` '
			. ' WHERE `affecteduser` = ? ' . $limit_activities_type
			. ' ORDER BY `timestamp` desc',
			$count, $start);
		$result = $query->execute(array($user));

		$activity = array();
		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('OCA\Activity\Data::read', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
		} else {
			while ($row = $result->fetchRow()) {
				$row['subject'] = Data::translation($row['app'],$row['subject'],unserialize($row['subjectparams']));
				$row['message'] = Data::translation($row['app'],$row['message'],unserialize($row['messageparams']));
				$activity[] = $row;
			}
		}
		return $activity;

	}

	/**
	 * @brief Get a list of events which contain the query string
	 * @param string $txt The query string
	 * @param int $count The number of statements to read
	 * @return array
	 */
	public static function search($txt, $count) {
		// get current user
		$user = \OCP\User::getUser();
		$limit_activities_type = 'AND ' . self::getUserNotificationTypesQuery($user, 'stream');

		// search in DB
		$query = \OCP\DB::prepare(
			'SELECT `activity_id`, `app`, `subject`, `message`, `file`, `link`, `timestamp`, `priority`, `type`, `user`, `affecteduser` '
			. ' FROM `*PREFIX*activity` '
			. 'WHERE `affecteduser` = ? AND ((`subject` LIKE ?) OR (`message` LIKE ?) OR (`file` LIKE ?)) ' . $limit_activities_type
			. 'ORDER BY `timestamp` desc'
			, $count);
		$result = $query->execute(array($user, '%' . $txt . '%', '%' . $txt . '%', '%' . $txt . '%')); //$result = $query->execute(array($user,'%'.$txt.''));

		$activity = array();
		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('OCA\Activity\Data::search', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
		} else {
			while ($row = $result->fetchRow()) {
				$row['subject'] = Data::translation($row['app'],$row['subject'],unserialize($row['subjectparams']));
				$row['message'] = Data::translation($row['app'],$row['message'],unserialize($row['messageparams']));
				$activity[] = $row;
			}
		}
		return $activity;

	}

	/**
	 * @brief Show a specific event in the activities
	 * @param array $event An array with all the event data in it
	 */
	public static function show($event) {
		$l = \OC_L10N::get('lib');

		$tmpl = new \OCP\Template('activity', 'activity.box');
		$tmpl->assign('formattedDate', \OCP\Util::formatDate($event['timestamp']));
		$tmpl->assign('formattedTimestamp', \OCP\relative_modified_date($event['timestamp']));
		$tmpl->assign('user', $event['user']);
		$tmpl->assign('displayName', \OCP\User::getDisplayName($event['user']));
		$tmpl->assign('event', $event);
		$tmpl->assign('isGrouped', !empty($event['isGrouped']));

		$rootView = new \OC\Files\View('');
		if ($event['file'] !== null){
			$exist = $rootView->file_exists('/' . $event['user'] . '/files' . $event['file']);
			unset($rootView);
			// show a preview image if the file still exists
			if ($exist) {
				$tmpl->assign('previewImageLink',
					\OCP\Util::linkToRoute('core_ajax_preview', array(
						'file' => $event['file'],
						'x' => 150,
						'y' => 150,
					))
				);
			}
		}

		$tmpl->printPage();
	}


	/**
	 * @brief Expire old events
	 */
	public static function expire() {
		// keep activity feed entries for one year
		$ttl = (60 * 60 * 24 * 365);

		$timelimit = time() - $ttl;
		$query = \OCP\DB::prepare('DELETE FROM `*PREFIX*activity` where `timestamp`<?');
		$query->execute(array($timelimit));
	}


	/**
	 * @brief Generate an RSS feed
	 * @param string $link
	 * @param string $content
	 */
	public static function generaterss($link, $content) {

		$writer = xmlwriter_open_memory();
		xmlwriter_set_indent($writer, 4);
		xmlwriter_start_document($writer, '1.0', 'utf-8');

		xmlwriter_start_element($writer, 'rss');
		xmlwriter_write_attribute($writer, 'version', '2.0');
		xmlwriter_write_attribute($writer, 'xmlns:atom', 'http://www.w3.org/2005/Atom');
		xmlwriter_start_element($writer, 'channel');

		xmlwriter_write_element($writer, 'title', 'my ownCloud');
		xmlwriter_write_element($writer, 'language', 'en-us');
		xmlwriter_write_element($writer, 'link', $link);
		xmlwriter_write_element($writer, 'description', 'A personal ownCloud activities');
		xmlwriter_write_element($writer, 'pubDate', date('r'));
		xmlwriter_write_element($writer, 'lastBuildDate', date('r'));

		xmlwriter_start_element($writer, 'atom:link');
		xmlwriter_write_attribute($writer, 'href', $link);
		xmlwriter_write_attribute($writer, 'rel', 'self');
		xmlwriter_write_attribute($writer, 'type', 'application/rss+xml');
		xmlwriter_end_element($writer);

		// items
		for ($i = 0; $i < count($content); $i++) {
			xmlwriter_start_element($writer, 'item');
			if (isset($content[$i]['subject'])) {
				xmlwriter_write_element($writer, 'title', $content[$i]['subject']);
			}

			if (isset($content[$i]['link'])) xmlwriter_write_element($writer, 'link', $content[$i]['link']);
			if (isset($content[$i]['link'])) xmlwriter_write_element($writer, 'guid', $content[$i]['link']);
			if (isset($content[$i]['timestamp'])) xmlwriter_write_element($writer, 'pubDate', date('r', $content[$i]['timestamp']));

			if (isset($content[$i]['message'])) {
				xmlwriter_start_element($writer, 'description');
				xmlwriter_start_cdata($writer);
				xmlwriter_text($writer, $content[$i]['message']);
				xmlwriter_end_cdata($writer);
				xmlwriter_end_element($writer);
			}
			xmlwriter_end_element($writer);
		}

		xmlwriter_end_element($writer);
		xmlwriter_end_element($writer);

		xmlwriter_end_document($writer);
		$entry = xmlwriter_output_memory($writer);
		unset($writer);
		return ($entry);
	}
}
