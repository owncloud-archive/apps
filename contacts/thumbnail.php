<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
 * @copyright 2011-2012 Thomas Tanghus <thomas@tanghus.net>
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

OCP\JSON::checkLoggedIn();
OCP\App::checkAppEnabled('contacts');
session_write_close();

//OCP\Util::writeLog('contacts', OCP\Util::getRequestUri(), OCP\Util::DEBUG);

function getStandardImage() {
	OCP\Response::enableCaching();
	OCP\Response::redirect(OCP\Util::imagePath('contacts', 'person.png'));
	exit();
}

if(!extension_loaded('gd') || !function_exists('gd_info')) {
	OCP\Util::writeLog('contacts',
		'thumbnail.php. GD module not installed', OCP\Util::DEBUG);
	getStandardImage();
}

$id = $_GET['id'];
$caching = null;

$image = OCA\Contacts\App::cacheThumbnail($id);
if($image !== false) {
	try {
	$modified = OCA\Contacts\App::lastModified($id);
	} catch(Exception $e) {
		getStandardImage();
	}
	// Force refresh if modified within the last minute.
	if(!is_null($modified)) {
		$caching = (time() - $modified->format('U') > 60) ? null : 0;
		OCP\Response::setLastModifiedHeader($modified);
	}
	OCP\Response::enableCaching($caching);
	header('Content-Type: image/png');
	echo $image;
} else {
	getStandardImage();
}
