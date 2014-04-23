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

	/**
	 * @brief Send an event into the activity stream
	 * @param string $app The app where this event is associated with
	 * @param string $subject A short description of the event
	 * @param string $message A longer description of the event
	 * @param string $file The file including path where this event is associated with. (optional)
	 * @param string $link A link where this event is associated with (optional)
	 * @return boolean
	 */
	public static function send($app, $subject, $subjectparams = array(), $message = '', $messageparams = array(), $file = '', $link = '', $affecteduser = '', $type = 0, $prio = Data::PRIORITY_MEDIUM)
	{

		$timestamp = time();
		$user = \OCP\User::getUser();
		
		if($affecteduser === '') {
			$auser = \OCP\User::getUser();
		} else{
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
		// todo translations
		\OCP\Util::emitHook('OC_Activity', 'post_event', array('app' => $app, 'subject' => $subject, 'user' => $user, 'affecteduser' => $affecteduser, 'message' => $message, 'file' => $file, 'link'=> $link, 'prio' => $prio, 'type' => $type));

		return true;
	}

	/**
	 * @brief Translate an event string with the translations from the app where it was send from
	 * @param string $app The app where this event comes from
	 * @param string $text The text including placeholders
	 * @param array $params The parameter for the placeholder
	 * @return string translated
	 */
	public static function translation($app, $text, $params)
	{
		$l = \OCP\Util::getL10N($app);
		$result = $l->t($text, $params);
		unset($l);
		return($result);
	}

	/**
	 * @brief Read a list of events from the activity stream
	 * @param int $start The start entry
	 * @param int $count The number of statements to read
	 * @return array
	 */
	public static function read($start, $count)
	{
		// get current user
		$user = \OCP\User::getUser();

		// fetch from DB
		$query = \OCP\DB::prepare('SELECT `activity_id`, `app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `timestamp`, `priority`, `type`, `user`, `affecteduser`  FROM `*PREFIX*activity` WHERE `affecteduser` = ? ORDER BY `timestamp` desc', $count, $start);
		$result = $query->execute(array($user));

		$activity = array();
		while ($row = $result->fetchRow()) {
			$row['subject'] = Data::translation($row['app'],$row['subject'],unserialize($row['subjectparams']));
			$row['message'] = Data::translation($row['app'],$row['message'],unserialize($row['messageparams']));
			$activity[] = $row;
		}
		return $activity;

	}

	/**
	 * @brief Get a list of events which contain the query string
	 * @param string $txt The query string
	 * @param int $count The number of statements to read
	 * @return array
	 */
	public static function search($txt, $count)
	{

		// get current user
		$user = \OCP\User::getUser();

		// search in DB
		$query = \OCP\DB::prepare('SELECT `activity_id`, `app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `timestamp`, `priority`, `type`, `user`, `affecteduser` FROM `*PREFIX*activity` WHERE `affecteduser` = ? AND ((`subject` LIKE ?) OR (`message` LIKE ?) OR (`file` LIKE ?)) ORDER BY `timestamp` desc', $count);
		$result = $query->execute(array($user, '%' . $txt . '%', '%' . $txt . '%', '%' . $txt . '%')); //$result = $query->execute(array($user,'%'.$txt.''));

		$activity = array();
		while ($row = $result->fetchRow()) {
			$row['subject'] = Data::translation($row['app'],$row['subject'],unserialize($row['subjectparams']));
			$row['message'] = Data::translation($row['app'],$row['message'],unserialize($row['messageparams']));
			$activity[] = $row;
		}
		return $activity;

	}

	/**
	 * @brief Show a specific event in the activities
	 * @param array $event An array with all the event data in it
	 */
	public static function show($event)
	{
		$l = \OC_L10N::get('lib');
		$user = $event['user'];
		if (!isset($event['isGrouped'])){
			$event['isGrouped'] = false;
		}

		$formattedDate = \OCP\Util::formatDate($event['timestamp']);
		$formattedTimestamp = \OCP\relative_modified_date($event['timestamp']);
		$displayName = \OCP\User::getDisplayName($user);

		// TODO: move into template?
		echo('<div class="box">');

		echo('<div class="header">');
		echo('<span class="avatar" data-user="' . \OC_Util::sanitizeHTML($user) . '"></span>');
		echo('<span>');
		echo('<span class="user">' . \OC_Util::sanitizeHTML($displayName) . '</span>');
		echo('<span class="activitytime tooltip" title="' . \OC_Util::sanitizeHTML($formattedDate) . '">' . \OC_Util::sanitizeHTML($formattedTimestamp) . '</span>');
		echo('<span class="appname">' . \OC_Util::sanitizeHTML($event['app']) . '</span>');
		echo('</span>');
		echo('</div>');
		echo('<div class="messagecontainer">');

		if ($event['isGrouped']){
			$count = 0;
			echo('<ul class="activitysubject grouped">');
			foreach($event['events'] as $subEvent){
				echo('<li>');
				if ($subEvent['link'] <> '') echo('<a href="' . $subEvent['link'] . '">');
				echo(\OC_Util::sanitizeHTML($subEvent['subject']));
				if ($subEvent['link'] <> '') echo('</a>');
				echo('</li>');
				$count++;
				if ($count > 5){
					echo('<li class="more">' . $l->n('%n more...', '%n more...', count($event['events']) - $count) . '</li>');
					break;
				}
			}
			echo('</ul>');
		}
		else{
			if ($event['link'] <> '') echo('<a href="' . $event['link'] . '">');
			echo('<div class="activitysubject">' . \OC_Util::sanitizeHTML($event['subject']) . '</div>');
			echo('<div class="activitymessage">' . \OC_Util::sanitizeHTML($event['message']) . '</div>');
		}

		$rootView = new \OC\Files\View('');
		if ($event['file'] !== null){
			$exist = $rootView->file_exists('/' . $user . '/files' . $event['file']);
			unset($rootView);
			// show a preview image if the file still exists
			if ($exist) {
				echo('<img class="preview" src="' . \OCP\Util::linkToRoute('core_ajax_preview', array('file' => $event['file'], 'x' => 150, 'y' => 150)) . '" />');
			}
		}

		if (!$event['isGrouped'] && $event['link'] <> '') echo('</a>');
		echo('</div>'); // end messagecontainer
		echo('</div>'); // end box

	}


	/**
	 * @brief Expire old events
	 */
	public static function expire()
	{
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
	public static function generaterss($link, $content)
	{

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
