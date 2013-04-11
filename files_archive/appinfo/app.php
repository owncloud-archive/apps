<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OC::$CLASSPATH['OC\Files\Storage\Archive']='files_archive/lib/storage.php';

OCP\Util::connectHook('OC_Filesystem','get_mountpoint','OC\Files\Storage\Archive','autoMount');

OCP\Util::addscript( 'files_archive', 'archive' );
