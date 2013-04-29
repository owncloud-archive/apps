<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

?>

<fieldset class="personalblock">
	<strong>Updater</strong>
	<br />
	<?php print_unescaped(OC_Updater::ShowUpdatingHint()) ?>
	<br />
	<?php /* I know, it's crap. But it's a fast and working crap ;) */ ?>
	<div id="upd-progress" style="display:none;height:20px;margin:5px 3px;width:200px;border:1px #ccc solid;"><div style="width:0;background-color:#5CE228;min-height:20px;"></div></div>
	<?php $data=OC_Updater::check();
		if(isset($data['version']) && strlen($data['version'])) { ?>
			<button id="updater_backup"><?php p($l->t('Update')) ?></button>
	<?php }		?>
</fieldset>
