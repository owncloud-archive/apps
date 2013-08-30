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
class Data {

	/**
	 * @brief Send an event into the activity stream
	 * @param $app The app where this event is associated with
	 * @param $subject A short description of the event
	 * @param $message A longer description of the event
	 * @param $file The file including path where this event is associated with. (optional)
	 * @param $link A link where this event is associated with (optional)
	 * @return true
	 */
	public static function send($app, $subject, $message='', $file='', $link='') {

		$timestamp=time();
		$user=\OCP\User::getUser();

		// store in DB
		$query = \OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `message`, `file`, `link`, `user`, `timestamp`)' . ' VALUES(?, ?, ?, ?, ?, ?, ? )');
		$query->execute(array($app, $subject, $message, $file, $link, $user, $timestamp));

		// call the expire function only every 1000x time to preserve performance.
		if(rand(0,1000)==0) \OCA\Activity\Data::expire();

		return(true);
	}


	/**
	* @brief Read a list of events from the activity stream
	* @param $start The start entry
	* @param $count The number of statements to read
	* @param $message A longer description of the event
	* @return true
	*/
	public static function read($start,$count) {

		$user=\OCP\User::getUser();
		
		$query = \OC_DB::prepare('SELECT `app`, `subject`, `message`, `file`, `link`, `timestamp` FROM `*PREFIX*activity` WHERE `user` = ? order by timestamp desc',$count,$start);
		$result = $query->execute(array($user));
		
		$activity=array();
		while ($row = $result->fetchRow()) {
				$activity[] = $row;
		}
		return($activity);
				
	}


	/**
	* @brief Show a specific event in the activities
	* @param $event An array with all the event data in it
	*/
	public static function show($event) {

		echo('<div class="box">');

		if($event['link']<>'') echo('<a href="'.$event['link'].'">');
		echo('<span class="activitysubject">'.$event['subject'].'</span><br />');
		echo('<span class="activitymessage">'.$event['message'].'</span><br />');
		echo('<br />');
		if($event['link']<>'') echo('</a>');
		echo('<span class="activitytime">'.\OCP\relative_modified_date($event['timestamp']).'</span><br />');
		
		echo('</div>');
		
	}


	/**
	* @brief Expire old events
	*/
	public static function expire() {
		// keep activity feed entries for one year
		$ttl=(60*60*24*365);

		$timelimit=time()-$ttl;
		$query = \OC_DB::prepare('DELETE FROM `*PREFIX*activity` where timestamp<?');
		$result = $query->execute(array($timelimit));
	}





	/**
	* @brief Generate an RSS feed
	* @param string $link
	* @param string $content
	*/
	public static function generaterss($link,$content) {
	 
		$writer = xmlwriter_open_memory();
		xmlwriter_set_indent( $writer, 4 );
		xmlwriter_start_document( $writer , '1.0', 'utf-8');
	 
		xmlwriter_start_element( $writer, 'rss' );
		xmlwriter_write_attribute( $writer,'version','2.0');
		xmlwriter_write_attribute( $writer,'xmlns:atom','http://www.w3.org/2005/Atom');
		xmlwriter_start_element( $writer, 'channel');
	 
		xmlwriter_write_element($writer,'title','my ownCloud');
		xmlwriter_write_element($writer,'language','en-us');
		xmlwriter_write_element($writer,'link',$link);
		xmlwriter_write_element($writer,'description','A personal ownCloud activities');
		xmlwriter_write_element($writer,'pubDate',date('r'));
		xmlwriter_write_element($writer,'lastBuildDate',date('r'));
	 
		xmlwriter_start_element( $writer, 'atom:link' );
		xmlwriter_write_attribute( $writer,'href',$link);
		xmlwriter_write_attribute( $writer,'rel','self');
		xmlwriter_write_attribute( $writer,'type','application/rss+xml');
		xmlwriter_end_element( $writer );
	 
		// items
		for($i=0;$i<count($content);$i++) {
			xmlwriter_start_element( $writer, 'item');
			if (isset($content[$i]['subject'])){
				xmlwriter_write_element($writer,'title',$content[$i]['subject']);
			}
	 
			if (isset($content[$i]['link']))     	xmlwriter_write_element($writer,'link',$content[$i]['link']);
			if (isset($content[$i]['link']))     	xmlwriter_write_element($writer,'guid',$content[$i]['link']);
			if (isset($content[$i]['timestamp']))  xmlwriter_write_element($writer,'pubDate',date('r',$content[$i]['timestamp']));
	 	 
			if (isset($content[$i]['message'])) {
				xmlwriter_start_element($writer,'description');
				xmlwriter_start_cdata($writer);
				xmlwriter_text($writer,$content[$i]['message']);
				xmlwriter_end_cdata($writer);
				xmlwriter_end_element($writer);
			}
			xmlwriter_end_element( $writer );
		}
	 
		xmlwriter_end_element( $writer );
		xmlwriter_end_element( $writer );
	 
		xmlwriter_end_document( $writer );
		$entry=xmlwriter_output_memory( $writer );
		unset($writer);
		return($entry);
	}



}
