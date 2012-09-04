$(document).ready(function(){
	$('#appsettings_popup').wrap('<div id="ownpad-appsettings"></div>');
	$('#ownpad_settings_apply').click(function(){
		var data = {
			etherpad_url : $('#etherpad_url').val(),
			etherpad_username : $('#etherpad_username').val()
		};
		//TODO: Validation!!
		$.post(OC.filePath('ownpad_lite', 'ajax', 'settings.php'), data, function(){
			ownPad.setHost(data.etherpad_url);
			ownPad.setUsername(data.etherpad_username);
		});
	});
});