<?php

/**
 * ownCloud - Activity Appn
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


// check if the user has the right permissions to access the activities
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('activity');

// activate the right navigation entry
OCP\App::setActiveNavigationEntry('activity');


// load the needed js scripts and css
OCP\Util::addScript('activity', 'jquery.masonry.min');
OCP\Util::addScript('activity', 'jquery.infinitescroll.min');
OCP\Util::addScript('activity', 'script');
OCP\Util::addStyle('activity', 'style');

// get the page that is requested. Needed for endless scrolling
if (isset($_GET['page'])) {
	$page = intval($_GET['page']) - 1;
} else {
	$page = 0;
}

// get rss url
$rsslink = \OCP\Util::linkToAbsolute('activity', 'rss.php');
$nextpage = \OCP\Util::linkToAbsolute('activity', 'index.php', array('page' => $page + 2));

// read activities data
$count = 30;
$activity = OCA\Activity\Data::read(($page) * $count, 30);


// show activity template
$tmpl = new \OCP\Template('activity', 'list', 'user');
$tmpl->assign('rsslink', $rsslink);
$tmpl->assign('activity', $activity);
if ($page == 0) $tmpl->assign('nextpage', $nextpage);
$tmpl->printPage();

