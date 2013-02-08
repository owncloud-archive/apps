<?php

/**
* ownCloud - impress player
*
* @author Frank Karlitschek
* @copyright 2012 Frank Karlitschek ink@owncloud.org
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

require_once 'lib/impress.php';

// Check if we are a user
OCP\User::checkLoggedIn();
OCP\JSON::checkAppEnabled('impress');

$filename = OCP\Util::sanitizeHTML($_GET['file']);
$title = OCP\Util::sanitizeHTML($_GET['name']);

if(!\OC\Files\Filesystem::file_exists($filename)) {
	header("HTTP/1.0 404 Not Found");
	$tmpl = new OCP\Template( '', '404', 'guest' );
	$tmpl->assign('file', $filename);
	$tmpl->printPage();
	exit;
}

$data=\OC\Files\Filesystem::file_get_contents( $filename );


if((stripos($data,'<html')<>false) or (stripos($data,'<head')<>false) or (stripos($data,'<body')<>false)) {
	echo('<br /><center>This is not a valid impress file. Please check the documentation.</center>');
	exit;
}

if(stripos($data,'<script')<>false) {
	echo('<br /><center>Please don\'t use javascript in impress files.</center>');
	exit;
}


header('Content-Type: text/html', true);
OCP\Response::disableCaching();

@ob_end_clean();

\OCA_Impress\Storage::showHeader($title);
\OC\Files\Filesystem::readfile( $filename );
\OCA_Impress\Storage::showFooter();
