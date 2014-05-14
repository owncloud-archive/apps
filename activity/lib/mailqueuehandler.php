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
		$query_params = array_unshift($query_params, (int) $max_time);

		$query = \OCP\DB::prepare(
			'SELECT * '
			. ' FROM `*PREFIX*activity_mq` '
			. ' WHERE `amq_timestamp` <= ? '
			. ' AND `amq_affecteduser` IN (' . $placeholders . ')');
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
	 * Delete all entries we dealed with
	 *
	 * @param array $affected_users
	 * @param int $max_time
	 */
	public function deleteSentItems($affected_users, $max_time) {
		$placeholders = implode(',', array_fill(0, sizeof($affected_users), '?'));
		$query_params = $affected_users;
		$query_params = array_unshift($query_params, (int) $max_time);

		$query = \OCP\DB::prepare(
			'DELETE FROM `*PREFIX*activity_mq` '
			. ' WHERE `amq_timestamp` <= ? '
			. ' AND `amq_affecteduser` IN (' . $placeholders . ')');
		$query->execute($query_params);
	}
}
