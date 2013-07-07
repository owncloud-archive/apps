<?php
/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
OCP\JSON::checkAdminUser();

if (isset($_POST['allowUsers']) && $_POST['allowUsers']) {
	OCP\Config::setAppValue('external', 'allowUsers', 'true');
} else{
	OCP\Config::setAppValue('external', 'allowUsers', 'false');
}
OCP\JSON::success();
