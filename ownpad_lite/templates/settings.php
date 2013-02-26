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
<?php $urlKey = OCA\ownpad_lite\App::CONFIG_ETHERPAD_URL; ?>
<?php $usernameKey = OCA\ownpad_lite\App::CONFIG_USERNAME; ?>
<dl>
	<dt><?php echo $l->t('Etherpad service URL'); ?></dt>
	<dd>
		<input id="<?php echo $urlKey; ?>" value="<?php echo $_[$urlKey]; ?>" />
	</dd>
	<dt>
		<?php echo $l->t('Username'); ?>
	</dt>
	<dd>
		<input id="<?php echo $usernameKey; ?>" value="<?php echo $_[$usernameKey]; ?>" />
	</dd>
	<dt>&nbsp;</dt>
	<dd>
		<button id="ownpad_settings_apply" class="hidden"><?php echo $l->t('Save'); ?>
	</dd>
</dl>