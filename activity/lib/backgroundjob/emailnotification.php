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
		// @todo $this->setInterval(15 * 60);
		$this->setInterval(10);
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

		// Get all items for these users
		// We do use don't use time() here, so we don't delete items
		// later which were created in the same second, but where not
		// collected for the emails
		$send_time = time() - 5;
		$mail_data = $this->mq_handler->getItemsForUsers($affected_users, $send_time);

		// Send Email
		foreach ($mail_data as $affected_user => $user_mail_data) {
			$this->mq_handler->sendEmailToUser($affected_user, $user_mail_data);
		}

		// Delete all entries we dealed with
		$this->mq_handler->deleteSentItems($affected_users, $send_time);

		return sizeof($affected_users);
	}
}
