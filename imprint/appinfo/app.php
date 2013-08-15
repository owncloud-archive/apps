<?php
/**
* @package imprint an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
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

$l = new OC_L10n('imprint');

OCP\App::registerAdmin ( 'imprint', 'settings' );
OCP\Util::addStyle  ( 'imprint', 'imprint' );
// workaround for OC-4.x's chaotoc header layout
if (5>@reset(OCP\Util::getVersion()))
	OCP\Util::addStyle  ( 'imprint', 'imprint-oc4' );

// backwards compatibility for OC5's global p() functions
$ocVersion = implode('.',OCP\Util::getVersion());
if (version_compare($ocVersion,'4.93','<')) // OC-5
{
	if ( ! function_exists('p'))
	{
		function p($string) {
			print(OC_Util::sanitizeHTML($string));
		}
	}
	if ( ! function_exists('print_unescaped'))
	{
		function print_unescaped($string) {
			print($string);
		}
	}
}

// add link according to what position is selected inside the apps options
if( ! \OC_User::isLoggedIn()) {
	// user NOT logged in, anonymous access, only limited positions to place the link:
	switch ( OCP\Config::getAppValue( 'imprint', 'anonposition', '' ) )
	{
		case 'header-left':
			OCP\Util::addScript ( 'imprint', 'imprint_header_left' );
			break;
		case 'header-right':
			OCP\Util::addScript ( 'imprint', 'imprint_header_right' );
			break;
		default:
			// don't show a link!
			break;
	} // switch
} else { // if logged in
	// user logged in, we have more positions to place the link:
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
				'id'    => 'imprint',
				'order' => 99999,
				'href'  => OCP\Util::linkTo   ( 'imprint', 'index.php' ),
				'icon'  => (5<=@reset(OCP\Util::getVersion()))
									? OCP\Util::imagePath( 'imprint', 'imprint-light.svg' )
									: OCP\Util::imagePath( 'imprint', 'imprint-dusky.svg' ),
				'name'  => $l->t("Legal notice") ) );
	} // switch
} // if logged in
?>
