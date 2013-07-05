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
 * @file templates/tmpl_settings.php
 * Dialog to change app settings, to be included in the clouds settings page.
 * @access public
 * @author Christian Reiner
 */
?>

<!-- settings of app 'imprint' -->
<form id="imprint">
	<fieldset class="personalblock">
		<legend>
			<span id="title" class="title">
				<img class="" src="<?php p(OCP\Util::imagePath("imprint","imprint-dusky.svg")); ?> ">
				<strong><?php p($l->t("Imprint"));?></strong>
			</span>
		</legend>
		<div id="imprint-options" class="imprint-option">
			<label for="imprint-option-position" class="imprint-option"><?php p($l->t("Placement").": ");?></label>
			<select id="imprint-option-position" class="imprint-option" type="select" name="position">
				<option value="standalone"><?php p($l->t("Standalone app"));?></option>
				<option value="header-left"><?php p($l->t("Header left"));?></option>
				<option value="header-right"><?php p($l->t("Header right"));?></option>
				<option value="navigation-top"><?php p($l->t("Navigation top"));?></option>
				<option value="navigation-bottom"><?php p($l->t("Navigation bottom"));?></option>
			</select>
			<label for="imprint-option-anonposition" class="imprint-option"><?php p($l->t("During login").": ");?></label>
			<select id="imprint-option-anonposition" class="imprint-option" type="select" name="anonposition">
				<option value=""></option>
				<option value="header-left"><?php p($l->t("Header left"));?></option>
				<option value="header-right"><?php p($l->t("Header right"));?></option>
			</select>
			<br>
			<label   for="imprint-content" class="imprint-option"><?php p($l->t("Content").': ');?></label>
			<textarea id="imprint-content" class="imprint-option"></textarea>
			<br>
			<label   for="imprint-usage"   class="imprint-option"></label>
			<span     id="imprint-usage"   class="imprint-option imprint-hint">
				<?php p($l->t("You can use html markup (e.g. &lt;br&gt; for a linebreak) and inline style attributes (e.g. &lt;a style=\"color:red;\"&gt;)."));?>
			</span>
		</div>
  </fieldset>
</form>
