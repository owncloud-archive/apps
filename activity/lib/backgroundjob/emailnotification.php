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
	/** @var \OCA\Activity\MailQueueHandler */
	protected $mq_handler;

	public function __construct() {
		// Run all 15 Minutes
		// @todo $this->setInterval(15 * 60);
		$this->setInterval(10);
		$this->mq_handler = new \OCA\Activity\MailQueueHandler();
	}

	protected function run($argument) {
		// If we are in CLI mode, we just send all emails
		$limit = (\OC::$CLI) ? null : 25;

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
	}
}
