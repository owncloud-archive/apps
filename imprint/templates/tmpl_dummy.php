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
?>

<?php
/**
 * @file templates/tmpl_dummy.php
 * Fallback imprint content guiding towards the required settings.
 * @access public
 * @author Christian Reiner
 */
?>

<div class="imprint-dummy">
	<div class="imprint-factoid">    <?php p($l->t("Nothing here yet")."!");?></div>
	<div class="imprint-suggestion"> <?php p($l->t("The content of the legal notice has to be configured first").".");?></div>
	<div class="imprint-explanation">
		<?php p($l->t("The configuration is done here").":");?>
		<a	class="imprint-reference"
			href="<?php p(OCP\Util::linkTo('','settings/admin.php'));?>">
			<?php p($l->t("Admin"));?>
		</a>
	</div>
</div>
