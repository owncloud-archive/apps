<?php

/**
 * ownCloud - Activity Application
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

/** @var $l OC_L10N */
/** @var $theme OC_Defaults */

/**
 * @brief Strips the timestamp of its time value
 * @param int $timestamp UNIX timestamp to strip
 * @return $timestamp without time value
 */
function strip_time($timestamp){
	$date = new \DateTime("@{$timestamp}");
	$date->setTime(0, 0, 0);
	return intval($date->format('U'));
}

$lastDate = null;
foreach ($_['activity'] as $event) {
	$currentDate = strip_time($event['timestamp']);
	if ($currentDate !== $lastDate){
		// not first group ?
		if ($lastDate !== null){
			// close previous group
			echo('</div>'); // boxcontainer
			echo('</div>'); // group
		}
		$lastDate = $currentDate;
		echo('<div class="group" data-date="' . $currentDate . '">');
		// TODO: use relative date like "Today", "Yesterday" for the displayed text
		echo('<div class="groupheader"><span class="tooltip" title="' . \OCP\Util::formatDate($currentDate, true) .'">' . \OCP\Util::formatDate($currentDate, true) . '</span></div>');
		echo('<div class="boxcontainer">');
	}
	\OCA\Activity\Data::show($event);
}
echo('</div>'); // boxcontainer
echo('</div>'); // group

?>
