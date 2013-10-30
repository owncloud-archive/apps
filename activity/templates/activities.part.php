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
 * @brief Makes a single event that aggregates the info
 * from the given events into a group
 * @param array $events array of events to aggregate
 * @return array single event containing the aggregated info from
 * the given events
 */
function makeEventGroup($events){
	if (count($events) === 1){
		return $events[0];
	}
	$event = array_shift($events);
	// populate with first event
	$groupedEvent = array(
		'isGrouped' => true,
		'user' => $event['user'],
		'affecteduser' => $event['affecteduser'],
		'app' => $event['app'],
		'type' => $event['type'],
		'timestamp' => $event['timestamp'],
		'file' => $event['file'],
		'link' => $event['link'],
		'events' => $events
	);
	return $groupedEvent;
}

function makeGroupKey($event){
	return $event['user'] . '|' .
		$event['affecteduser'] . '|' .
		$event['app'] . '|' .
		$event['type'];
}

$lastDate = null;
$eventsInGroup = array();
$lastGroup = null;
foreach ($_['activity'] as $event) {
	// group by date
	// TODO: use more efficient way to group by date (don't group by localized string...)
	$currentDate = (string)(\OCP\relative_modified_date($event['timestamp'], true));
	// new date group
	if ($currentDate !== $lastDate){
		// not first date group ?
		if ($lastDate !== null){
			// output box group
			if (count($eventsInGroup) > 0){
				\OCA\Activity\Data::show(makeEventGroup($eventsInGroup));
			}
			$eventsInGroup = array();
			$lastGroup = null;
			// close previous date group
			echo('</div>'); // boxcontainer
			echo('</div>'); // date group
		}
		$lastDate = $currentDate;
		echo('<div class="group" data-date="' . $currentDate . '">');
		echo('<div class="groupheader"><span class="tooltip" title="' . \OCP\Util::formatDate(strip_time($event['timestamp']), true) .'">' . ucfirst($currentDate) . '</span></div>');
		echo('<div class="boxcontainer">');
	}
	$currentGroup = makeGroupKey($event);
	// new box group
	if ($lastGroup !== $currentGroup){
		if ($lastGroup !== null){
			// create meta event and add it to the list
			\OCA\Activity\Data::show(makeEventGroup($eventsInGroup));
			$eventsInGroup = array();
		}
		$lastGroup = $currentGroup;
	}
	$eventsInGroup[] = $event;
}
// show last group
if (count($eventsInGroup) > 0){
	\OCA\Activity\Data::show(makeEventGroup($eventsInGroup));
}
echo('</div>'); // boxcontainer
echo('</div>'); // group
