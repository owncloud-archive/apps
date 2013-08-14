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
 * @file content.php
 * Content of the imprint as configured
 * @access public
 * @author Christian Reiner
 */

// Session checks
// OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled ( 'imprint' );

// prepare content
if ( FALSE === ($content=OCP\Config::getAppValue('imprint','content',FALSE)) )
{
	$tmpl = new OCP\Template( 'imprint', 'tmpl_dummy' );
	OCP\Util::addStyle  ( 'imprint','imprint' );
	// workaround for OC-4.x's chaotoc header layout
	if (5>reset(OCP\Util::getVersion()))
		OCP\Util::addStyle  ( 'imprint', 'imprint-oc4' );
}
else
{
	// detect type of stored content and process accordingly
	if ( strlen($content)!=strlen(strip_tags($content)) )
		$processed_content = $content;
	else
		$processed_content = sprintf ( "<pre>\n%s\n</pre>", $content );
	// output processed content
	OCP\Util::addStyle  ( 'imprint','content' );
	$tmpl = new OCP\Template( 'imprint', 'tmpl_content' );
	$tmpl->assign ( 'processed-content', $processed_content );
}

// render template
$tmpl->printPage ( );
?>
