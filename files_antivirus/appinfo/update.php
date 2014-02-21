<?php
/**
 * Copyright (c) 2014 Victor Dubiniuk <victor.dubiniuk@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


$installedVersion = \OCP\Config::getAppValue('files_antivirus', 'installed_version');

if (version_compare($installedVersion, '0.5', '<')) {
	\OCA\Files_Antivirus\Status::init();
}