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
	if(OC_Filesystem::is_writable($path))
	{
		$mtime = OC_Filesystem::filemtime($path);
		$filecontents = OC_Filesystem::file_get_contents($path);
		$encoding = detectUTF8($filecontents) ? "UTF-8" : "ISO-8859-1"; // use this check, because mb_detect_encoding has a bug
                $filecontents = iconv($encoding, "UTF-8//IGNORE", $filecontents); // use //IGNORE otherwise file is truncated
		OCP\JSON::success(array('data' => array('filecontents' => $filecontents, 'write' => 'true', 'mtime' => $mtime)));
	}
	else
	{
		$mtime = OC_Filesystem::filemtime($path);
		$filecontents = OC_Filesystem::file_get_contents($path);
		$encoding = detectUTF8($filecontents) ? "UTF-8" : "ISO-8859-1"; // use this check, because mb_detect_encoding has a bug
                $filecontents = iconv($encoding, "UTF-8//IGNORE", $filecontents); // use //IGNORE otherwise file is truncated
		OCP\JSON::success(array('data' => array('filecontents' => $filecontents, 'write' => 'false', 'mtime' => $mtime)));
	}
} else {
	OCP\JSON::error(array('data' => array( 'message' => 'Invalid file path supplied.')));
}

// Problem with the editor was, that it did not correctly display non-UTF-files
// see: https://github.com/owncloud/core/issues/666
// Stefan Hammes (stefan@hammes.de) @ 18-Jan-2013
//
// Reason:
// The PHP function mb_detect_encoding has a bug with UTF-8 without BOM
//
// Fix:
// Use the function detectUTF8 below from chris AT w3style.co DOT uk:
// http://de2.php.net/manual/de/function.mb-detect-encoding.php#68607
//
// I checked many detection functions, but only this one works

function detectUTF8($string)
{
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
}
