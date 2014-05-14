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

namespace OCA\Activity;

/**
 * Class MailQueueHandler
 * Gets the users from the database and
 *
 * @package OCA\Activity
 */
class MailQueueHandler {
	/** @var array */
	protected $languages;

	/** @var string */
	protected $sender_address;

	/** @var string */
	protected $sender_name;

	/**
	 * Get the users we want to send an email to
	 *
	 * @param int|null $limit
	 * @return array
	 */
	public function getAffectedUsers($limit) {
		$limit = (!$limit) ? null : (int) $limit;

		$query = \OCP\DB::prepare(
			'SELECT `amq_affecteduser` '
			. ' FROM `*PREFIX*activity_mq` '
			. ' WHERE `amq_latest_send` < ? '
			. ' GROUP BY `amq_affecteduser` '
			. ' ORDER BY `amq_latest_send` ASC',
			$limit);
		$result = $query->execute(array(time()));

		$affected_users = array();
		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('OCA\Activity\BackgroundJob\EmailNotification::getAffectedUsers', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
		} else {
			while ($row = $result->fetchRow()) {
				$affected_users[] = $row['amq_affecteduser'];
			}
		}

		return $affected_users;
	}

	/**
	 * Get all items for the users we want to send an email to
	 *
	 * @param array $affected_users
	 * @param int $max_time
	 * @return array Notification data (user => array of rows from the table)
	 */
	public function getItemsForUsers($affected_users, $max_time) {
		$placeholders = implode(',', array_fill(0, sizeof($affected_users), '?'));
		$query_params = $affected_users;
		array_unshift($query_params, (int) $max_time);

		$query = \OCP\DB::prepare(
			'SELECT * '
			. ' FROM `*PREFIX*activity_mq` '
			. ' WHERE `amq_timestamp` <= ? '
			. ' AND `amq_affecteduser` IN (' . $placeholders . ')'
			. ' ORDER BY `amq_timestamp`'
		);
		$result = $query->execute($query_params);

		$user_activity_map = array();
		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('OCA\Activity\BackgroundJob\EmailNotification::getItemsForUsers', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
		} else {
			while ($row = $result->fetchRow()) {
				$user_activity_map[$row['amq_affecteduser']][] = $row;
			}
		}

		return $user_activity_map;
	}

	/**
	 * Get a language object for a specific language
	 *
	 * @param string $lang Language identifier
	 * @return \OC_L10N Language object of $lang
	 */
	protected function getLanguage($lang) {
		if (!isset($this->languages[$lang])) {
			$this->languages[$lang] = \OC_L10N::get('activity', $lang);
		}

		return $this->languages[$lang];
	}

	/**
	 * Get the sender data
	 * @param string $setting Either `email` or `name`
	 * @return string
	 */
	protected function getSenderData($setting) {
		if (empty($this->sender_address)) {
			$this->sender_address = \OCP\Util::getDefaultEmailAddress('no-reply');
		}
		if (empty($this->sender_name)) {
			$defaults = new \OC_Defaults();
			$this->sender_name = $defaults->getName();
		}

		if ($setting === 'email') {
			return $this->sender_address;
		}
		return $this->sender_name;
	}

	/**
	 * Get the language string for a timeframe
	 * However we are not very accurate here, so we match the setting of the user
	 * a bit better
	 *
	 * @param \OC_L10N $l
	 * @param int      $timestamp
	 * @return \OC_L10N_String
	 */
	protected function getLangForApproximatedTimeframe(\OC_L10N $l, $timestamp) {
		if (time() - $timestamp < 4000) {
			return $l->t('in the last hour');
		} else if (time() - $timestamp < 90000) {
			return $l->t('in the last day');
		} else {
			return $l->t('in the last week');
		}
	}

	/**
	 * Send a notification to one user
	 *
	 * @param string $user Username of the recipient
	 * @param string $email Email address of the recipient
	 * @param string $lang Selected language of the recipient
	 * @param array $mail_data Notification data we send to the user
	 */
	public function sendEmailToUser($user, $email, $lang, $mail_data) {
		$l = $this->getLanguage($lang);

		$activity_list = array();
		foreach ($mail_data as $activity) {
			$activity_list[] = \OCA\Activity\Data::translation(
				$activity['amq_appid'], $activity['amq_subject'], unserialize($activity['amq_subjectparams']),
				false, false, $l
			);
		}
		$activity_list = implode("\n", $activity_list);

		$email_text = $l->t(
			'Hello %1$s,' . "\n"
			. "\n"
			. 'You receive this email because %2$s the following things happened at %3$s' . "\n"
			. "\n"
			. '%4$s' . "\n",
			array(
				$user,
				$this->getLangForApproximatedTimeframe($l, $mail_data[0]['amq_timestamp']),
				'owncloud', // @todo: Replace with oC URL
				$activity_list,
			)
		);

		// @todo Remove log after testing
		\OCP\Util::writeLog('activity', 'Send email to user ' . $user . ' with text: ' . $email_text, \OCP\Util::FATAL);

		try {
			\OC_Mail::send(
				$email, $user,
				$l->t('Activity notification'), $email_text,
				$this->getSenderData('email'), $this->getSenderData('name')
			);
		} catch (\Exception $e) {
			$message = $l->t('A problem occurred while sending the e-mail. Please revisit your settings.');
			\OC_JSON::error( array( "data" => array( "message" => $message)) );
			exit;
		}
	}

	/**
	 * Delete all entries we dealed with
	 *
	 * @param array $affected_users
	 * @param int $max_time
	 */
	public function deleteSentItems($affected_users, $max_time) {
		$placeholders = implode(',', array_fill(0, sizeof($affected_users), '?'));
		$query_params = $affected_users;
		array_unshift($query_params, (int) $max_time);

		$query = \OCP\DB::prepare(
			'DELETE FROM `*PREFIX*activity_mq` '
			. ' WHERE `amq_timestamp` <= ? '
			. ' AND `amq_affecteduser` IN (' . $placeholders . ')');
		$result = $query->execute($query_params);

		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('OCA\Activity\BackgroundJob\EmailNotification::deleteSentItems', \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
		}
	}
}
