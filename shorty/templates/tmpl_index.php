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
 * @file templates/tmpl_index.php
 * The general html environment where specific templates are embedded into. 
 * @access public
 * @author Christian Reiner
 */
?>

<!-- central messenger area -->
<div id="shorty-messenger" class="shorty-messenger" style="z-index:9250;">
	<fieldset>
		<img id="close" title="" class="svg" src="<?php echo OCP\Util::imagePath('shorty','actions/shade.svg');  ?>">
		<img id="symbol" title="" src="">
		<span id="title"></span>
		<img id="symbol" title="" src="">
		<hr>
		<div id="message"></div>
	</fieldset>
</div>

<!-- top control bar -->
<div id="controls" class="shorty-controls shorty-panel-visible" data-referrer="<?php if (array_key_exists('shorty-referrer',$_)) echo $_['shorty-referrer']; ?>">
	<!-- controls: left area, buttons -->
	<span class="shorty-controls-left">
		<!-- button to add a new entry to list -->
		<input type="button" id="add" value="<?php echo OC_Shorty_L10n::t('New Shorty'); ?>"/>
	</span>
	<!-- controls: right area, buttons -->
	<span class="shorty-controls-right">
		<!-- the 'home' button currently links to the entry in the OC app store -->
		<a href="http://apps.owncloud.com/content/show.php/Shorty?content=150401" target="_blank">
			<button id="controls-home" class="shorty-config settings" title="<?php echo OC_Shorty_L10n::t('Home') ?>">
				<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'places/home.svg'); ?>"
					alt="<?php echo OC_Shorty_L10n::t('Home') ?>" />
			</button>
		</a>
<?php if (OC_Shorty_Tools::versionCompare('>','4.80')) { ?>
		<!-- the internal settings button -->
		<button id="controls-preferences" class="shorty-config settings" title="<?php echo OC_Shorty_L10n::t('Configuration') ?>">
			<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/settings.svg'); ?>"
				alt="<?php echo OC_Shorty_L10n::t('Configuration') ?>" />
		</button>
		<!-- a container that will hold the preferences dialog -->
		<div id="appsettings" class="popup topright hidden"></div>
<?php } ?>
		<!-- handle to hide/show the panel -->
		<span id="controls-handle" class="shorty-handle shorty-handle-top">
			<img class="shorty-icon svg" src="<?php echo OCP\Util::imagePath('shorty','actions/shade.svg'); ?>" >
		</span>
	</span>
	<!-- controls: center area, some  passive information -->
	<span class="shorty-controls-center">
		<!-- display label: number of entries in list -->
		<span class="shorty-prompt"><?php echo OC_Shorty_L10n::t('Number of entries') ?>:</span>
		<span id="sum_shortys" class="shorty-value">
			<img src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>"
				alt="<?php echo OC_Shorty_L10n::t('Loading') ?>..."/>
		</span>
		<!-- display label: total of clicks in list -->
		<span class="shorty-prompt"><?php echo OC_Shorty_L10n::t('Total of clicks') ?>:</span>
		<span id="sum_clicks" class="shorty-value">
			<img src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>"
				alt="<?php echo OC_Shorty_L10n::t('Loading') ?>..." />
		</span>
	</span>
	<!-- the dialogs, hidden by default -->
	<?php require_once('tmpl_url_add.php'); ?>
	<?php require_once('tmpl_url_edit.php'); ?>
	<?php require_once('tmpl_url_show.php'); ?>
	<?php require_once('tmpl_url_share.php'); ?>
</div>

<!-- the "desktop where the action takes place -->
<div id="desktop" class="right-content shorty-desktop">
	<?php require_once('tmpl_url_list.php'); ?>
</div>
