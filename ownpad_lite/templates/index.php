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
<div id="ownpad-location">
	<button id="settingsbtn" title="<?php echo $l->t('Settings'); ?>">
		<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/settings.png'); ?>" alt="<?php echo $l->t('Settings'); ?>" />
	</button>
	<label><?php echo $l->t('Pad Title') ?></label><input id="ownpad-title" value="eetest" />
	<button id="ownpad-open"><?php echo $l->t('Open') ?></button>
</div>
<div id="ownpad-content"></div>
<div id="ownpad-appsettings"><div id="appsettings" class="popup hidden topright"></div></div>

<script type="text/javascript" src="<?php echo OC_Helper::linkToRoute('ownpad_lite');?>"></script>