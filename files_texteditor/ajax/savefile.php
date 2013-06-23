<?php
/**
 * ownCloud - files_texteditor
 *
 * @author Tom Needham
 * @copyright 2013 Tom Needham tom@owncloud.com
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
OCP\JSON::callCheck();

$editor = new \OCA\Files_Texteditor\App(
	\OC\Files\Filesystem::getView(),
	\OC_L10n::get('files_texteditor')
);

// Get paramteres
$contents = $_POST['filecontents'];
$path = isset($_POST['path']) ? $_POST['path'] : '';
$opened = isset($_POST['opened']) ? $_POST['opened'] : '';

// Save the file
echo json_encode($editor->saveFile($path, $contents, $opened));