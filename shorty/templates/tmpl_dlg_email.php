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
 * @file templates/tmpl_dlg_email.php
 * Dialog popup to prepare sending a url via email
 * @access public
 * @author Christian Reiner
 */
?>

<!-- begin of email dialog -->
<div id="dialog-email" style="display:none;">
	<fieldset class="">
		<legend><?php echo OC_Shorty_L10n::t("Send link by email");?>:</legend>
		<div class="usage-explanation">
			<span class="explanation">
				<?php echo OC_Shorty_L10n::t("Clicking 'Ok' below will try to launch an email composer");?>.
				<br>
				<?php echo OC_Shorty_L10n::t("Alternatively the link can be copied into a message manually");?>:
			</span>
		</div>
		<textarea class="payload" readonly></textarea>
		<div class="usage-instruction">
			<?php echo OC_Shorty_L10n::t("Copy to clipboard");?>:
			<span class="usage-token">
				<?php echo OC_Shorty_L10n::t("Ctrl-C");?>
			</span>
			<br>
			<?php echo OC_Shorty_L10n::t("then paste into message");?>:
			<span class="usage-token">
				<?php echo OC_Shorty_L10n::t("Ctrl-V");?>
			</span>
		</div>
	</fieldset>
</div>
<!-- end of email dialog -->
