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
?>

<?php
/**
 * @file templates/tmpl_content.php
 * Visualizes the configured imprint content.
 * @access public
 * @author Christian Reiner
 */
?>

<html>
	<head>
		<link rel="stylesheet" href="<?php p(\OCP\Util::linkTo('imprint','css/content.css'));?>" type="text/css" media="screen" />
	</head>
	<body id="imprint-body">
		<div id="imprint-content">
			<?php	print_unescaped($_['content']); ?>
		</div>
	</body>
</html>
