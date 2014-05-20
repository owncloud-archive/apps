<?php

/**
 * ownCloud - Activity App
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
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

namespace OCA\Activity\BackgroundJob;

/**
 * Class EmailNotification
 *
 * @package OCA\Activity\BackgroundJob
 */
class EmailNotification extends \OC\BackgroundJob\TimedJob {
	const CLI_EMAIL_BATCH_SIZE = 500;
	const WEB_EMAIL_BATCH_SIZE = 25;

	/** @var \OCA\Activity\MailQueueHandler */
	protected $mq_handler;

	public function __construct() {
		// Run all 15 Minutes
		$this->setInterval(15 * 60);
		$this->mq_handler = new \OCA\Activity\MailQueueHandler();
	}

	protected function run($argument) {
		if (\OC::$CLI) {
			do {
				// If we are in CLI mode, we keep sending emails
				// until we are done.
				$emails_sent = $this->runStep(self::CLI_EMAIL_BATCH_SIZE);
			} while ($emails_sent === self::CLI_EMAIL_BATCH_SIZE);
		} else {
			// Only send 25 Emails in one go for web cron
			$this->runStep(self::WEB_EMAIL_BATCH_SIZE);
		}
	}

	/**
	 * Send an email to {$limit} users
	 *
	 * @param int $limit Number of users we want to send an email to
	 * @return int Number of users we sent an email to
	 */
	protected function runStep($limit) {
		// Get all users which should receive an email
		$affected_users = $this->mq_handler->getAffectedUsers($limit);
		if (empty($affected_users)) {
			// No users found to notify, mission abort
			return 0;
		}

		$user_languages = $this->getPreferencesForUsers($affected_users, 'core', 'lang');
		$user_emails = $this->getPreferencesForUsers($affected_users, 'settings', 'email');

		// Get all items for these users
		// We do use don't use time() but "time() - 1" here, so we don't run into
		// runtime issues and delete emails later, which were created in the
		// same second, but where not collected for the emails.
		$send_time = time() - 1;
		$mail_data = $this->mq_handler->getItemsForUsers($affected_users, $send_time);

		// Send Email
		$default_lang = \OC_Config::getValue('default_language', 'en');
		foreach ($mail_data as $user => $user_data) {
			if (!isset($user_emails[$user])) {
				// The user did not setup an email address
				// So we will not send an email :(
				continue;
			}

			$language = (isset($user_languages[$user])) ? $user_languages[$user] : $default_lang;
			$this->mq_handler->sendEmailToUser($user, $user_emails[$user], $language, $user_data);
		}

		// Delete all entries we dealed with
		$this->mq_handler->deleteSentItems($affected_users, $send_time);

		return sizeof($affected_users);
	}

	protected function getPreferencesForUsers($users, $appid, $configkey) {
		$placeholders = implode(',', array_fill(0, sizeof($users), '?'));

		$query_params = $users;
		array_unshift($query_params, $configkey);
		array_unshift($query_params, $appid);

		$query = \OCP\DB::prepare(
			'SELECT `userid`, `configvalue` '
			. ' FROM `*PREFIX*preferences` '
			. ' WHERE `appid` = ? AND `configkey` = ?'
			. ' AND `userid` IN (' . $placeholders . ')'
		);
		$result = $query->execute($query_params);

		$user_preferences = array();
		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('OCA\Activity\BackgroundJob\EmailNotification::getPreferencesForUsers', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
		} else {
			while ($row = $result->fetchRow()) {
				$user_preferences[$row['userid']] = $row['configvalue'];
			}
		}

		return $user_preferences;
	}
}
