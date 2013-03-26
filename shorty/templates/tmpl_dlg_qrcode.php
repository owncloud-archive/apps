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
 * @file templates/tmpl_dlg_qrcode.php
 * Dialog popup to visualize and offer an url as a QRCode (2D barcode)
 * @access public
 * @author Christian Reiner
 */
?>

<!-- begin of qrcode dialog -->
<div id="dialog-qrcode" style="display:none;">
	<fieldset class="">
		<legend><?php echo OC_Shorty_L10n::t("Shorty as QRCode");?>:</legend>
		<input id="qrcode-ref" type="hidden" value="<?php echo $_['qrcode-ref']; ?>">
		<div class='qrcode-img'>
			<div class="usage-explanation">
				<?php echo OC_Shorty_L10n::t("This 2d barcode encodes the url pointing to this Shorty");?>.
				<br>
				<?php echo OC_Shorty_L10n::t("Use it in web pages by referencing or embedding");?>,
				<?php echo OC_Shorty_L10n::t("or simpy print or download it for off-line usage");?>!
			</div>
			<div style="text-align:center;">
				<img style="width:154px;" class="usage-qrcode" alt="<?php echo OC_Shorty_L10n::t("QRCode"); ?>"
					src="<?php echo OCP\Util::imagePath('shorty','loading-disk.gif'); ?>" >
				<div class="usage-instruction">
					<?php echo OC_Shorty_L10n::t("Click for embedding details");?>…
				</div>
			</div>
		</div>
		<div class='qrcode-ref' style="display:none;">
			<div class="usage-explanation">
				<?php echo OC_Shorty_L10n::t("This is the url referencing the QRCode shown before");?>.
				<br>
				<?php echo OC_Shorty_L10n::t("Embed the QRCode as an image into some web page using this url");?>.
			</div>
			<input class="payload" readonly>
			<div class="usage-instruction">
				<?php echo OC_Shorty_L10n::t("Copy to clipboard");?>:<span class="usage-token"><?php echo OC_Shorty_L10n::t("Ctrl-C");?></span>
				<br>
				<?php echo OC_Shorty_L10n::t("Paste to embed elsewhere");?>:<span class="usage-token"><?php echo OC_Shorty_L10n::t("Ctrl-V");?></span>
			</div>
			<hr>
			<div class="usage-explanation">
				<?php echo OC_Shorty_L10n::t("Alternatively the image can be downloaded for printout or storage");?>.
				<?php echo OC_Shorty_L10n::t("That image can be used when writing documents or setting up web sites");?>:
				<br>
				<div style="text-align:center;">
				<button id="download" style="margin:1.2em;" class="shorty-button">Download QRCode</button>
				</div>
			</div>
		</div>
	</fieldset>
</div>
<!-- end of qrcode dialog -->
