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
?>

<?php
/**
 * @file templates/tmpl_wdg_shortlet.php
 * Widget offering the 'Shortlet' as a drag'n'drop object
 * @access public
 * @author Christian Reiner
 */
?>

	<!-- shortlet -->
	<label for="shortlet" class="shorty-aspect"><?php echo OC_Shorty_L10n::t("Shortlet").":";?></label>
	<span id="shortlet">
		<a class="shortlet"
			href="javascript:(function(){url=encodeURIComponent(location.href);window.open('<?php echo OCP\Util::linkToAbsolute('shorty', 'index.php'); ?>?url='+url, 'owncloud-shorty')%20})()">
			<?php echo OC_Shorty_L10n::t("Add page as 'Shorty' to ownCloud"); ?>
		</a>
	</span>
	<p>
		<span class="shorty-explain">
			<em>
				<?php echo OC_Shorty_L10n::t("Drag this to your browser bookmarks."); ?>
				<br>
				<?php echo OC_Shorty_L10n::t("Click it, for whatever site you want to create a Shorty."); ?>
			</em>
		</span>
	</p>
