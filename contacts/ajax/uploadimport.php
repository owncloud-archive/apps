<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus <thomas@tanghus.net>
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

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::callCheck();
require_once 'loghandler.php';

$l10n = OCA\Contacts\App::$l10n;

$view = OCP\Files::getStorage('contacts');
if(!$view->file_exists('imports')) {
	$view->mkdir('imports');
}
$tmpfile = md5(rand());

// If it is a Drag'n'Drop transfer it's handled here.
$fn = (isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : false);
if($fn) {
	if($view->file_put_contents('/imports/'.$fn, file_get_contents('php://input'))) {
		OCP\JSON::success(array('data' => array('file'=>$tmpfile, 'name'=>$fn)));
		exit();
	} else {
		bailOut($l10n->t('Error uploading contacts to storage.'));
	}
}

// File input transfers are handled here
if (!isset($_FILES['importfile'])) {
	OCP\Util::writeLog('contacts',
		'ajax/uploadphoto.php: No file was uploaded. Unknown error.',
		OCP\Util::DEBUG);
	OCP\JSON::error(array('
		data' => array(
			'message' => 'No file was uploaded. Unknown error' )));
	exit();
}
$error = $_FILES['importfile']['error'];
if($error !== UPLOAD_ERR_OK) {
	$errors = array(
		0=>$l10n->t("There is no error, the file uploaded with success"),
		1=>$l10n->t("The uploaded file exceeds the upload_max_filesize directive in php.ini").ini_get('upload_max_filesize'),
		2=>$l10n->t("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
		3=>$l10n->t("The uploaded file was only partially uploaded"),
		4=>$l10n->t("No file was uploaded"),
		6=>$l10n->t("Missing a temporary folder")
	);
	bailOut($errors[$error]);
}
$file=$_FILES['importfile'];

if(file_exists($file['tmp_name'])) {
	if($view->file_put_contents('/imports/'.$file['name'], file_get_contents($file['tmp_name']))) {
		OCP\JSON::success(array('data' => array('file'=>$file['name'], 'name'=>$file['name'])));
	} else {
		bailOut($l10n->t('Error uploading contacts to storage.'));
	}
} else {
	bailOut('Temporary file: \''.$file['tmp_name'].'\' has gone AWOL?');
}
