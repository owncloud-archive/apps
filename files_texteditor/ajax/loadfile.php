<?php
/**
 * ownCloud - files_texteditor
 *
 * @author Tom Needham
 * @copyright 2011 Tom Needham contact@tomneedham.com
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

// Init owncloud



// Check if we are a user
OCP\JSON::checkLoggedIn();

// Set the session key for the file we are about to edit.
$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$filename = isset($_GET['file']) ? $_GET['file'] : '';
if(!empty($filename))
{
	$path = $dir.'/'.$filename;
	$writeable = \OC\Files\Filesystem::isUpdatable($path);
	$mime = \OC\Files\Filesystem::getMimeType($path);
	$mtime = \OC\Files\Filesystem::filemtime($path);
	$filecontents = \OC\Files\Filesystem::file_get_contents($path);
	$encoding = mb_detect_encoding($filecontents."a", "UTF-8, WINDOWS-1252, ISO-8859-15, ISO-8859-1, ASCII", true);
	if ($encoding == "") {
		// set default encoding if it couldn't be detected
		$encoding = 'ISO-8859-15';
	}
	$filecontents = iconv($encoding, "UTF-8", $filecontents);
	OCP\JSON::success(array('data' => array(
		'filecontents' => $filecontents,
		'writeable' => $writeable,
		'mime' => $mime,
		'mtime' => $mtime))
	);
} else {
	OCP\JSON::error(array('data' => array( 'message' => 'Invalid file path supplied.')));
}
