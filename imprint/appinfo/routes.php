<?php
/**
 * @package imprint an ownCloud app
 * @author Christian Reiner
 * @copyright 2012-2014 Christian Reiner <foss@christian-reiner.info>
 * @license GNU Affero General Public license (AGPL)
 * @link information http://apps.owncloud.com/content/show.php?content=153220
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the license, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file appinfo/routes.php
 * @brief Basic request routing map
 * @author Christian Reiner
 */

$this->create('imprint_index', '/')
	->actionInclude('imprint/index.php');
$this->create('imprint_settings', '/settings.php')
	->actionInclude('imprint/settings.php');
$this->create('imprint_content', '/content.php')
	->actionInclude('imprint/content.php');
