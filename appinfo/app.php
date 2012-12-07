<?php
/**
* @package imprint an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php?content=153220
* @link repository https://svn.christian-reiner.info/svn/app/oc/imprint
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

$l = new OC_L10n('imprint');

// there are three configuration options
OCP\App::registerAdmin ( 'imprint', 'settings' );
OCP\Util::addStyle  ( 'imprint', 'imprint' );
// add navigation entry in case it is enabled in the apps options
switch ( OCP\Config::getAppValue( 'imprint', 'position', 'standalone' ) )
{
	case 'header-left':
		OCP\Util::addScript ( 'imprint', 'imprint_header_left' );
		break;
	case 'header-right':
		OCP\Util::addScript ( 'imprint', 'imprint_header_right' );
		break;
	case 'navigation-top':
		OCP\Util::addScript ( 'imprint', 'imprint_navigation_top' );
		break;
	case 'navigation-bottom':
		OCP\Util::addScript ( 'imprint', 'imprint_navigation_bottom' );
		break;
	default:
	case 'standalone':
		// no js required, we add the imprint as a normal app to the navigation
		OCP\App::addNavigationEntry ( array (
			'id' => 'imprint',
			'order' => 99999,
			'href' => OCP\Util::linkTo   ( 'imprint', 'index.php' ),
			'icon' => OCP\Util::imagePath( 'imprint', 'imprint.png' ),
			'name' => $l->t("Legal notice") ) );
	} // switch
?>
