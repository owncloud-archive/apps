<?php
/**
 * Copyright (c) 2013 Thomas Müller
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Set the content type to Javascript
header("Content-type: text/javascript");

// Disallow caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

?>

var ownPad = {
	username : '<?php echo $_[\OCA\ownpad_lite\App::CONFIG_USERNAME] ?>',
	host :  '<?php echo $_[\OCA\ownpad_lite\App::CONFIG_ETHERPAD_URL] ?>',
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