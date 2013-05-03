<?php 

/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
 
if(OCP\Config::getAppValue('external', 'allowUsers') == 'true') $checked = 'checked';
else $checked ='';
?>
<fieldset class="personalblock">
	<div id="allowUsersForm">
		<legend><strong><?php p($l->t('External Sites'));?></strong></legend>
		<input type="checkbox" name"allowUsers" id="allowUsers" <?php echo $checked; ?> value="true" ><label for="allowUsers"><?php p($l->t('Allow users to add personal links')); ?></label>
		<span class="msg"></span>
	</div>

	<br>
	<form id="external">
		<legend><strong><?php p($l->t('External Global Sites'));?></strong></legend>
		<ul class="external_sites">

		<?php
		$sites = OC_External::getGlobalSites();
		for($i = 0; $i < sizeof($sites); $i++) {
			print_unescaped('<li><input type="text" name="site_name[]" class="site_name" value="'.OC_Util::sanitizeHTML($sites[$i][0]).'" placeholder="'.$l->t('Name').'" />
			<input type="text" class="site_url" name="site_url[]"  value="'.OC_Util::sanitizeHTML($sites[$i][1]).'"  placeholder="'.$l->t('URL').'" />
			<img class="svg action delete_button" src="'.OCP\image_path("", "actions/delete.svg") .'" title="'.$l->t("Remove site").'" />
			</li>');
		}
		?>

		</ul>
		<input type="hidden" value="true" name="global" id="global" />
        <input type="button" id="add_external_site" value="<?php p($l->t("Add extra field")); ?>" />
		<span class="msg"></span>
	</form>
</fieldset>
