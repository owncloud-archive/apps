<?php
/**
 * Copyright (c) 2014, Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

/** @var $this \OCP\Route\IRouter */
$this->create('impress_index', '/')
	->actionInclude('impress/index.php');
$this->create('impress_documentation', 'documentation.php')
	->actionInclude('impress/documentation.php');
$this->create('impress_player', 'player.php')
	->actionInclude('impress/player.php');
