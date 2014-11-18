<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$manager = new \OCA\Files_Archive\Manager(
	new \OC\Files\View(''),
	\OC\Files\Filesystem::getMountManager(),
	\OC\Files\Filesystem::getLoader()
);

OCP\Util::connectHook('OC_Filesystem', 'get_mountpoint', $manager, 'autoMount');

OCP\Util::addscript('files_archive', 'archive');
