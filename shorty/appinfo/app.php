<?php
/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401 
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
 * @brief Basic registration of plugin at ownCloud
 * @author Christian Reiner
 */

OC::$CLASSPATH['OC_Shorty_Backend']       = 'shorty/lib/backend.php';
OC::$CLASSPATH['OC_Shorty_Exception']     = 'shorty/lib/exception.php';
OC::$CLASSPATH['OC_Shorty_Hooks']         = 'shorty/lib/hooks.php';
OC::$CLASSPATH['OC_Shorty_HttpException'] = 'shorty/lib/exception.php';
OC::$CLASSPATH['OC_Shorty_L10n']          = 'shorty/lib/l10n.php';
OC::$CLASSPATH['OC_Shorty_Meta']          = 'shorty/lib/meta.php';
OC::$CLASSPATH['OC_Shorty_Query']         = 'shorty/lib/query.php';
OC::$CLASSPATH['OC_Shorty_Tools']         = 'shorty/lib/tools.php';
OC::$CLASSPATH['OC_Shorty_Type']          = 'shorty/lib/type.php';

OCP\App::registerAdmin      ( 'shorty', 'settings' );
// TODO: remove OC-4.0-compatibility:
if (OC_Shorty_Tools::versionCompare('<','4.80')) // OC-4.0
	OCP\App::registerPersonal   ( 'shorty', 'preferences' );
OCP\App::addNavigationEntry ( array (	'id' => 'shorty_index',
										'order' => 71,
										'href' => OCP\Util::linkTo   ( 'shorty', 'index.php' ),
										'icon' => (OC_Shorty_Tools::versionCompare('>=','4.91')) // OC-5pre
													? OCP\Util::imagePath( 'shorty', 'shorty-light.svg' )
													: OCP\Util::imagePath( 'shorty', 'shorty-dusky.svg' ),
										'name' => 'Shorty' ) );

OCP\Util::connectHook ( 'OC_User',   'post_deleteUser', 'OC_Shorty_Hooks', 'deleteUser');
OCP\Util::connectHook ( 'OC_Shorty', 'registerQueries', 'OC_Shorty_Hooks', 'registerQueries');

?>
