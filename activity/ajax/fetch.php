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

// some housekeeping
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('activity');

// read the next 30 items for the endless scrolling
// get the page that is requested. Needed for endless scrolling
$count = 30;
if (isset($_GET['page'])) {
	$page = intval($_GET['page']) - 1;
} else {
	$page = 0;
}

$activity=OCA\Activity\Data::read($page * $count, $count);
$nextpage = \OCP\Util::linkToAbsolute('activity', 'index.php', array('page' => $page + 2));

// show the next 30 entries
$tmpl = new \OCP\Template('activity', 'activities.part', '');
$tmpl->assign('activity', $activity);
if ($page == 0) $tmpl->assign('nextpage', $nextpage);
$tmpl->printPage();

