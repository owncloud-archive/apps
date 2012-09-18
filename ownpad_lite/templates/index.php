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
 <script type="text/javascript">
 var ownPad = {
	username : '<?php echo $_[OCA\ownpad_lite\App::CONFIG_USERNAME] ?>',
	host :  '<?php echo $_[OCA\ownpad_lite\App::CONFIG_ETHERPAD_URL] ?>',
	showPad : function() {
		$('#ownpad-content').pad({
			'showControls'     : true,
			'showChat'         : true,
			'showLineNumbers'  : true,
			'border'           : '1px',
			'padId'            : ownPad.getTitle(),
			'userName'         : ownPad.getUsername(),
			'host'             : ownPad.getHost(),
			'baseUrl'          : ''
		});
	},
	getTitle : function() {
		return $('#ownpad-title').val();
	},
	getUsername : function() {
		return ownPad.username;
	},
	setUsername : function(username) {
		ownPad.username = username;
	},
	getHost : function() {
		return ownPad.host;
	},
	setHost : function(host) {
		ownPad.host = host;
	}
};

$('#ownpad-open').click(ownPad.showPad);
$('#settingsbtn').on('click keydown', function() {
        try {
            OC.appSettings({appid:'ownpad_lite', loadJS:true, cache:false});
        } catch(e) {
            console.log(e);
        }
    });
</script>