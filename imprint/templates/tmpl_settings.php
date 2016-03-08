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

vendor_style('imprint', 'chosen.jquery.min');
vendor_script('imprint', 'chosen.jquery.min');

?>

<?php
/**
 * @file templates/tmpl_settings.php
 * Dialog to change app settings, to be included in the clouds settings page.
 * @access public
 * @author Christian Reiner
 */
?>


<!-- settings of app 'imprint' -->
<div class="section" id="imprint">
	<img src="<?php p(\OCP\Util::imagePath("imprint","imprint-dusky.svg")); ?> ">
	<h2><?php p($l->t("Imprint"));?></h2>
	<div id="imprint-options" class="imprint-option">
		<label for="imprint-option-position-user" class="imprint-option"><?php p($l->t("Reference").": ");?></label>
		<select id="imprint-option-position-user" class="imprint-option" type="select" name="position-user">
			<option value=""></option>
			<option value="header-left"><?php p($l->t("Header left"));?></option>
			<option value="header-right"><?php p($l->t("Header right"));?></option>
		</select>
		<label for="imprint-option-position-guest" class="imprint-option followup"><?php p($l->t("As guest").": ");?></label>
		<select id="imprint-option-position-guest" class="imprint-option" type="select" name="position-guest">
			<option value=""></option>
			<option value="header-left"><?php p($l->t("Header left"));?></option>
			<option value="header-right"><?php p($l->t("Header right"));?></option>
			<option value="footer-left"><?php p($l->t("Footer left"));?></option>
			<option value="footer-right"><?php p($l->t("Footer right"));?></option>
		</select>
		<label for="imprint-option-position-login" class="imprint-option followup"><?php p($l->t("At login").": ");?></label>
		<select id="imprint-option-position-login" class="imprint-option" type="select" name="position-login">
			<option value=""></option>
			<option value="header-left"><?php p($l->t("Header left"));?></option>
			<option value="header-right"><?php p($l->t("Header right"));?></option>
			<option value="footer-left"><?php p($l->t("Footer left"));?></option>
			<option value="footer-right"><?php p($l->t("Footer right"));?></option>
		</select>
		<br>
		<label for="imprint-option-standalone" class="imprint-option"><?php p($l->t("Application").": ");?></label>
		<input id="imprint-option-standalone" type="checkbox" class="imprint-option">Offer as application inside the menu</input>
		<br>
		<label   for="imprint-content" class="imprint-option"><?php p($l->t("Content").': ');?></label>
		<textarea id="imprint-content" class="imprint-option"></textarea>
		<br>
		<label   for="imprint-usage"   class="imprint-option"></label>
		<span     id="imprint-usage"   class="imprint-option imprint-hint">
			<?php p($l->t("You can use plain text, markdown notation or html markup with inline style attributes. "));?>
		</span>
	</div>
</div>
