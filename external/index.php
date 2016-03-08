<?php

/**
 * ownCloud - External plugin
 *
 * @author Frank Karlitschek
 * @copyright 2012 Frank Karlitschek frank@owncloud.org
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
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


use OCA\External\External;

OCP\JSON::checkAppEnabled('external');
OCP\User::checkLoggedIn();
OCP\Util::addStyle( 'external', 'style');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sites = External::getSites();
if (sizeof($sites) >= $id) {
	$url = $sites[$id - 1][1];
	OCP\App::setActiveNavigationEntry('external_index' . $id);

	$tmpl = new OCP\Template('external', 'frame', 'user');
	//overwrite x-frame-options
	header('X-Frame-Options: ALLOW-FROM *');

	$tmpl->assign('url', $url);
	$tmpl->printPage();
} else {
	\OC_Util::redirectToDefaultPage();
}

