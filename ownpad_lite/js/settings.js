$(document).ready(function(){
	$('#appsettings_popup').wrap('<div id="ownpad-appsettings"></div>');

	var ownPadSettings = {
		getHost : function(){
			var cleanUrl = $('#etherpad_url').val().match(/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i);
			var url = cleanUrl && cleanUrl[0] ? cleanUrl[0] : '';
			return url;
		},
		getUsername : function(){
			return $('#etherpad_username').val().replace(/[^0-9a-zA-Z\.\-_]*/, '');
		},
		onChange : function(){
			if (ownPadSettings.getHost() && ownPadSettings.getUsername()) {
				$('#ownpad_settings_apply').show();
			} else {
				$('#ownpad_settings_apply').hide();
			}
		},
		save : function() {
			var data = {
				url : ownPadSettings.getHost(),
				username : ownPadSettings.getUsername()
			};
			$.post(OC.filePath('ownpad_lite', 'ajax', 'settings.php'), data, ownPadSettings.afterSave);
		},
		afterSave : function(){
			ownPad.setHost(ownPadSettings.getHost());
			ownPad.setUsername(ownPadSettings.getUsername());
			$('#settingsbtn img').trigger('click');
		}
	};
	$('#etherpad_url').keyup(ownPadSettings.onChange);
	$('#etherpad_username').keyup(ownPadSettings.onChange);
	$('#ownpad_settings_apply').click(ownPadSettings.save);
});