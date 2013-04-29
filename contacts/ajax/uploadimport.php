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

// File input transfers are handled here
if (!isset($_FILES['file'])) {
	bailOut($l10n->t('No file was uploaded. Unknown error'));
}

$file=$_FILES['file'];

if($file['error'] !== UPLOAD_ERR_OK) {
	$errors = array(
		UPLOAD_ERR_OK			=> $l10n->t("There is no error, the file uploaded with success"),
		UPLOAD_ERR_INI_SIZE		=> $l10n->t("The uploaded file exceeds the upload_max_filesize directive in php.ini")
			.ini_get('upload_max_filesize'),
		UPLOAD_ERR_FORM_SIZE	=> $l10n->t("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
		UPLOAD_ERR_PARTIAL		=> $l10n->t("The uploaded file was only partially uploaded"),
		UPLOAD_ERR_NO_FILE		=> $l10n->t("No file was uploaded"),
		UPLOAD_ERR_NO_TMP_DIR	=> $l10n->t('Missing a temporary folder'),
		UPLOAD_ERR_CANT_WRITE	=> $l10n->t('Failed to write to disk'),
	);
	bailOut($errors[$error]);
}

$maxUploadFilesize = OCP\Util::maxUploadFilesize('/');
$maxHumanFilesize = OCP\Util::humanFileSize($maxUploadFilesize);

$totalSize = $file['size'];
if ($maxUploadFilesize >= 0 and $totalSize > $maxUploadFilesize) {
	bailOut($l10n->t('Not enough storage available'));
}

$tmpname = $file['tmp_name'];
$filename = strtr($file['name'], array('/' => '', "\\" => ''));
if(is_uploaded_file($tmpname)) {
	if(OC\Files\Filesystem::isFileBlacklisted($filename)) {
		bailOut($l10n->t('Upload of blacklisted file:') . $filename);
	}
	if($view->file_put_contents('/imports/'.$filename, file_get_contents($tmpname))) {
		OCP\JSON::success(array('file'=>$filename));
	} else {
		bailOut($l10n->t('Error uploading contacts to storage.'));
	}
} else {
	bailOut('Temporary file: \''.$tmpname.'\' has gone AWOL?');
}

