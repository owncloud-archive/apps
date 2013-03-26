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
 * @file templates/tmpl_dlg_verify.php
 * Dialog popup to validate a configured static backend base
 * @access public
 * @author Christian Reiner
 */
?>

<!-- a (usually hidden) dialog used for verification of the correct setup of the 'static' backend -->
<div id="dialog-verification" style="display:none;" title="<?php echo OC_Shorty_L10n::t("Static backend: base url verification"); ?>">
	<!-- verification-in-progress -->
	<div id="hourglass">
		<img src="<?php echo OCP\Util::imagePath('shorty', 'loading-disk.gif'); ?>">
	</div>
	<!-- success -->
	<div id="success" style="display:none;">
		<fieldset>
			<legend>
				<img class="shorty-status" src="<?php echo OCP\Util::imagePath('shorty','status/good.png'); ?>" alt="<?php OC_Shorty_L10n::t('Success') ?>" title="<?php OC_Shorty_L10n::t('Verification successful') ?>">
				<span id="title" class="shorty-title"><strong><?php echo OC_Shorty_L10n::t("Verification successful");?>!</strong></span>
			</legend>
			<p><?php	echo OC_Shorty_L10n::t("Great, your setup appears to be working fine!");?></p>
			<p><?php	echo OC_Shorty_L10n::t(
							"Requests to the configured base url are mapped to this ownClouds relay service.");
						echo OC_Shorty_L10n::t(
							"Usage of that static backend is fine and safe as long as this setup is not altered.");?></p>
			<p><?php	echo OC_Shorty_L10n::t(
							"This backend will now be offered as an additional backend alternative to all local users inside their personal preferences.");?></p>
		</fieldset>
	</div>
	<!-- failure -->
	<div id="failure" style="display:none;">
		<fieldset>
			<legend>
				<img class="shorty-status" src="<?php echo OCP\Util::imagePath('shorty','status/bad.png'); ?>" alt="<?php OC_Shorty_L10n::t('Success') ?>" title="<?php OC_Shorty_L10n::t('Verification successful') ?>">
				<span id="title" class="shorty-title"><strong><?php echo OC_Shorty_L10n::t("Verification failed");?>!</strong></span>
			</legend>
			<p><?php	echo OC_Shorty_L10n::t("Sorry, but your setup appears not to be working correctly yet!");?></p>
			<p><?php	echo OC_Shorty_L10n::t("Please check your setup and make sure that the configured base url is indeed correct.");
						echo OC_Shorty_L10n::t("Make sure that all requests to it are somehow mapped to Shortys relay service.");?></p>
			<p><?php	echo OC_Shorty_L10n::t("Relay service");?>:
				<br>
				<a><?php	echo OCP\Util::linkToAbsolute('','public.php?service=shorty_relay&id=')."&lt;shorty-key&gt;";?></a></p>
		</fieldset>
	</div>
</div>
<!-- end of verification dialog -->
