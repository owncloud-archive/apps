<?php
/**
 * Copyright (c) 2013 Lukas Reschke <lukas@statuscode.ch>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$this->create('ready_js', 'js/ready.js')
	->actionInclude('gallery/js/ready.php');