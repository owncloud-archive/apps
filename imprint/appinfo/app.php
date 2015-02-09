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
 * @file appinfo/app.php
 * @brief Basic registration of app inside ownCloud
 * @author Christian Reiner
 */

OC::$CLASSPATH['Slimdown'] = 'imprint/vendor/slimdown.php';

\OCP\App::registerAdmin('imprint', 'settings' );
\OCP\Util::addStyle    ('imprint', 'reference');
\OCP\Util::addScript   ('imprint', 'reference');

// add imprint positioning options as meta tags to the html head to avoid additional ajax requests
\OCP\Util::addHeader('meta', array('data-imprint-position-user' =>\OCP\Config::getAppValue('imprint', 'position-user',  '')));
\OCP\Util::addHeader('meta', array('data-imprint-position-guest'=>\OCP\Config::getAppValue('imprint', 'position-guest', '')));
\OCP\Util::addHeader('meta', array('data-imprint-position-login'=>\OCP\Config::getAppValue('imprint', 'position-login', '')));

// offer application as standalone entry in the menu?
if ('true' === \OCP\Config::getAppValue('imprint', 'standalone', 'false')) {
		// no js required, we add the imprint as a normal app to the navigation
		\OCP\App::addNavigationEntry(array(
			'id'    => 'imprint',
			'order' => 99999,
			'href'  => \OCP\Util::linkTo   ('imprint', 'index.php'),
			'icon'  => \OCP\Util::imagePath('imprint', 'imprint-light.svg'),
			'name'  => \OCP\Util::getL10N  ('imprint')->t("Legal notice")
		));
} // if
