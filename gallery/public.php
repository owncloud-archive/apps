<?php
 /**
 * ownCloud
 *
 * @author Thomas Müller
 * @copyright 2013 Thomas Müller deepdiver@owncloud.com
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

\OCP\JSON::checkAppEnabled('gallery');

OCP\Util::addStyle('gallery', 'styles');

if (isset($_GET['t'])) {
	$token = $_GET['t'];
	$linkItem = \OCP\Share::getShareByToken($token);
	if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
		// seems to be a valid share
		$type = $linkItem['item_type'];
		$fileSource = $linkItem['file_source'];
		$shareOwner = $linkItem['uid_owner'];
		$path = null;
		$rootLinkItem = \OCP\Share::resolveReShare($linkItem);
		$fileOwner = $rootLinkItem['uid_owner'];
		$albumName = $linkItem['file_target'];

		// render template
		$tmpl = new \OCP\Template('gallery', 'public', 'base');
		OCP\Util::addScript('gallery', 'gallery');
		OCP\Util::addScript('gallery', 'thumbnail');
		OCP\Util::addStyle('gallery', 'public');
		$tmpl->assign('token', $token);
		$tmpl->assign('displayName', $fileOwner);
		$tmpl->assign('albumName', $albumName);

		$tmpl->printPage();
		exit;
	}
}

$tmpl = new OCP\Template('', '404', 'guest');
$tmpl->printPage();
