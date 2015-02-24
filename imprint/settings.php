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
 * @file settings.php
 * This apps system settings dialog
 * The dialog will be included in the general framework of the system settings page
 * @access public
 * @author Christian Reiner
 */

// Session checks
\OCP\User::checkLoggedIn();
\OCP\User::checkAdminUser();
\OCP\App::checkAppEnabled('imprint');

\OCP\Util::addStyle('imprint', 'settings');
\OCP\Util::addStyle('imprint', 'reference');

\OCP\Util::addScript('imprint', 'settings');

// fetch template
$tmpl = new \OCP\Template('imprint', 'tmpl_settings');
// render template
return $tmpl->fetchPage();
?>
