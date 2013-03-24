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
 * @file index.php
 * This is the apps central view
 * @access public
 * @author Christian Reiner
 */

// Session checks
OCP\App::checkAppEnabled ( 'imprint' );

OCP\App::setActiveNavigationEntry ( 'imprint' );
OCP\Util::addStyle  ( 'imprint','imprint' );

// prepare content
if ( FALSE === ($content=OCP\Config::getAppValue('imprint','content',FALSE)) )
{
	// fetch 'dummy' template
	$tmpl = new OCP\Template( 'imprint', 'tmpl_dummy', 'user' );
}
else
{
	// fetch 'real' template, will pull content itself
	$tmpl = new OCP\Template( 'imprint', 'tmpl_index', 'user' );
}
// render template
$tmpl->printPage ( );
?>
