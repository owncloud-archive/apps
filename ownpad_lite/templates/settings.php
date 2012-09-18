<?php

/**
 * ownCloud - ownpad_lite plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */
 ?>
 
<dl>
	<dt><?php echo $l->t('Etherpad service URL'); ?></dt>
	<dd>
		<input id="<?php echo OCA\ownpad_lite\App::CONFIG_ETHERPAD_URL ?>" value="<?php echo $_[OCA\ownpad_lite\App::CONFIG_ETHERPAD_URL] ?>" />
	</dd>
	<dt>
		<?php echo $l->t('Username'); ?>
	</dt>
	<dd>
		<input id="<?php echo OCA\ownpad_lite\App::CONFIG_USERNAME ?>" value="<?php echo $_[OCA\ownpad_lite\App::CONFIG_USERNAME] ?>" />
	</dd>
	<dt>&nbsp;</dt>
	<dd>
		<button id="ownpad_settings_apply" class="hidden"><?php echo $l->t('Save'); ?>
	</dd>
</dl>