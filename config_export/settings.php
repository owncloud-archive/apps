<?php

/**
 * ownCloud - config_export
 *
 * @author Tom Needham
 * @copyright 2014 Tom Needham tom@owncloud.com
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

OCP\User::checkAdminUser();
OCP\App::checkAppEnabled('config_export');

if (isset($_POST['export'])) {

	OCP\JSON::callCheck();
	$path = \OC::$SERVERROOT . '/config/config.php';
	header("Content-Type: text/x-php");
	header("Content-Disposition: attachment; filename=" . basename($path));
	header("Content-Length: " . filesize($path));
	readfile($path);
	die();

} else {

    $tmpl = new OCP\Template('config_export', 'settings');
    return $tmpl->fetchPage();

}