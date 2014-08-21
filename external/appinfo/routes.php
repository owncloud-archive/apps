<?php
/**
 * Copyright (c) 2014, Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

/** @var $this \OCP\Route\IRouter */
$this->create('external_index', '/{id}')
	->actionInclude('external/index.php');
$this->create('external_ajax_setsites', 'ajax/setsites.php')
	->actionInclude('external/ajax/setsites.php');
