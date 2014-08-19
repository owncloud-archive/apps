<?php
/**
 * Copyright (c) 2014, Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

/** @var $this \OCP\Route\IRouter */
$this->create('files_svgedit_index', '/')
	->actionInclude('files_svgedit/index.php');
$this->create('files_svgedit_ajax_save', 'ajax/save.php')
	->actionInclude('files_svgedit/ajax/save.php');
