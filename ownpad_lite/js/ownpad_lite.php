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
	username : '<?php echo OCA\ownpad_lite\App::getUsername() ?>',
	host :  '<?php echo OCA\ownpad_lite\App::getServiceUrl() ?>',
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
	},
	onSearch : function(request, response){
		if (request && request.term){
			$.post(
				OC.filePath('ownpad_lite', 'ajax', 'search.php'),
				{<?php echo OCA\ownpad_lite\UrlParam::SHARE_SEARCH ?>:request.term},
				function(data){
					if (data.status == 'success' && data.data){
						response( data.data );
					}
				}
			);
		}
	},
	onShare : function(){
		var source = ownPad.getHost() + ownPad.getTitle();
		var shareWith = $('#ownpad-share').val();
		if (shareWith.length<3) {
			return;
		}
			$.post(
				OC.filePath('ownpad_lite', 'ajax', 'share.php'),
				{
					<?php echo OCA\ownpad_lite\UrlParam::SHARE_WHAT ?> : source,
					<?php echo OCA\ownpad_lite\UrlParam::SHARE_WITH ?> : shareWith
				},
				ownPad.onShareComplete
			);
	},
	onShareComplete : function(data){
		var successMessage = t('<?php echo OCA\ownpad_lite\App::APP_ID ?>', 'Shared successfully');
		var errorMessage = t('<?php echo OCA\ownpad_lite\App::APP_ID ?>', 'Failed to send notification');
		var message = data && data.status && data.status=='success' ? successMessage : errorMessage ;
		OC.Notification.show(message);
		setTimeout(OC.Notification.hide, 6000);
	}
};

$('#ownpad-open').click(ownPad.showPad);
$('#ownpad-share').autocomplete({ minLength: 3, source: ownPad.onSearch});
$('#ownpad-share-button').click(ownPad.onShare);
$('#settingsbtn').on('click keydown', function() {
	try {
		OC.appSettings({appid:'ownpad_lite', loadJS:true, cache:false});
	} catch(e) {
		console.log(e);
	}
});