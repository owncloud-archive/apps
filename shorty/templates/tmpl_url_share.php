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
 * @file templates/tmpl_url_share.php
 * A dialog offering control over an entries state and offers the source url
 * @access public
 * @author Christian Reiner
 */
?>

<!-- (hidden) dialog to share a shorty from the list -->
<form id="dialog-share" class="shorty-dialog shorty-embedded">
	<fieldset>
		<legend class="">
			<a id="close" class="shorty-close-button"
				title="<?php echo OC_Shorty_L10n::t("Close"); ?>">
				<img alt="<?php echo OC_Shorty_L10n::t("Close"); ?>" class="svg"
					src="<?php echo OCP\Util::imagePath('shorty','actions/shade.svg');  ?>">
			</a>
			<span class="heading"><?php echo OC_Shorty_L10n::t("Share and use").':'; ?></span>
		</legend>
		<input id="id" name="id" type="hidden" data="" class="" readonly disabled />
		<label for="title"><?php echo OC_Shorty_L10n::t("Title").':'; ?></label>
		<span id="title" class="ellipsis"></span>
		<br />
		<label for="status"><?php echo OC_Shorty_L10n::t("Status").':'; ?></label>
		<select id="status" name="status" data="" class="" value="">
			<?php
				foreach ( OC_Shorty_Type::$STATUS as $status )
					if ( 'deleted'!=$status )
						echo sprintf ( "<option value=\"%s\">%s</option>\n", $status, OC_Shorty_L10n::t($status) );
			?>
		</select>
		<span id="blocked" class="status-hint" style="display:none;"><?php echo OC_Shorty_L10n::t("for any access")."."; ?></span>
		<span id="private" class="status-hint" style="display:none;"><?php echo OC_Shorty_L10n::t("for own usage")."."; ?></span>
		<span id="shared"  class="status-hint" style="display:none;"><?php echo OC_Shorty_L10n::t("with ownCloud users")."."; ?></span>
		<span id="public"  class="status-hint" style="display:none;"><?php echo OC_Shorty_L10n::t("available for everyone")."."; ?></span>
		<div class="shorty-usages">
			<fieldset class="shorty-collapsible collapsed">
				<label for="source-text"><?php echo OC_Shorty_L10n::t("Source url").':'; ?></label>
				<span id="source-text"><?php echo OC_Shorty_L10n::t("This is the shortened url registered at the backend").'.'; ?></span>
				<div class="shorty-collapsible-tail" style="display:none;">
					<a id="source" class="shorty-clickable" target="_blank"
						title="<?php echo OC_Shorty_L10n::t("Open source url"); ?>"
						href=""></a>
				</div>
			</fieldset>
			<fieldset class="shorty-collapsible collapsed">
				<label for="relay-text"><?php echo OC_Shorty_L10n::t("Relay url").':'; ?></label>
				<span id="relay-text"><?php echo OC_Shorty_L10n::t("This is a internal url that the shortened url relays to").'.'; ?></span>
				<div class="shorty-collapsible-tail" style="display:none;">
					<a id="relay" class="shorty-clickable" target="_blank"
						title="<?php echo OC_Shorty_L10n::t("Open relay url"); ?>"
						href=""></a>
				</div>
			</fieldset>
			<fieldset class="shorty-collapsible collapsed">
				<label for="target-text"><?php echo OC_Shorty_L10n::t("Target url").':'; ?></label>
				<span id="target-text"><?php echo OC_Shorty_L10n::t("This is the target url specified when generating this Shorty").'.'; ?></span>
				<div class="shorty-collapsible-tail" style="display:none;">
					<a id="target" class="shorty-clickable" target="_blank"
						title="<?php echo OC_Shorty_L10n::t("Open target url"); ?>"
						href=""></a>
				</div>
			</fieldset>
		</div>
		<table class="shorty-grid">
			<tr>
				<td>
					<img id="usage-qrcode" name="usage-qrcode" class="shorty-usage svg" alt="qrcode"
						src="<?php echo OCP\Util::imagePath('shorty','usage/qrcode.svg'); ?>"
						title="<?php echo OC_Shorty_L10n::t("Show as QRCode"); ?>" />
				</td>
				<td>
					<img id="usage-email" name="usage-email" class="shorty-usage svg" alt="email"
						src="<?php echo OCP\Util::imagePath('shorty','usage/email.svg'); ?>"
						title="<?php echo OC_Shorty_L10n::t("Send by email"); ?>" />
				</td>
<?php if ('disabled'!=$_['sms-control']) { ?>
				<td>
					<img id="usage-sms" name="usage-sms" class="shorty-usage svg" alt="sms"
						src="<?php echo OCP\Util::imagePath('shorty','usage/sms.svg'); ?>"
						title="<?php echo OC_Shorty_L10n::t("Send by SMS"); ?>" />
				</td>
<?php } ?>
				<td>
					<img id="usage-clipboard" name="usage-clipboard" class="shorty-usage svg" alt="clipboard"
						src="<?php echo OCP\Util::imagePath('shorty','usage/clipboard.svg'); ?>"
						title="<?php echo OC_Shorty_L10n::t("Copy to clipboard"); ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
</form>

<!-- additional (hidden) popup dialogs for specific usage actions -->
<?php require_once('tmpl_dlg_email.php'); ?>
<?php require_once('tmpl_dlg_sms.php'); ?>
<?php require_once('tmpl_dlg_clipboard.php'); ?>
<?php require_once('tmpl_dlg_qrcode.php'); ?>
