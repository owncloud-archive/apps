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


// register the path of the libraries
OC::$CLASSPATH['OCA\Activity\Data'] = 'activity/lib/lib_activity.php';
OC::$CLASSPATH['OCA\Activity\Hook'] = 'activity/lib/lib_hooks.php';
OC::$CLASSPATH['OCA\Activity\OCS'] = 'activity/lib/lib_ocs.php';
OC::$CLASSPATH['OCA\Activity\Search'] = 'activity/lib/lib_search.php';


// add an navigation entry
$l=OC_L10N::get('activity');
OCP\App::addNavigationEntry(array(
	'id' => 'activity',
	'order' => 1,
	'href' => OCP\Util::linkTo('activity', 'index.php'),
	'icon' => OCP\Util::imagePath('activity', 'activity.png'),
	'name' => $l->t('Activity')));

// register the hooks for filesystem operations. All other events from other apps has to be send via the public api
OCA\Activity\Hook::register();

OC_Search::registerProvider('\OCA\Activity\Search');


