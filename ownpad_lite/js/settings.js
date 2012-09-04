$('#appsettings_popup a.close').click(function(){
	var data = {
		etherpad_url : $('#etherpad_url').val(),
		etherpad_username : $('#etherpad_username').val()
	};
	//TODO: Validation!!
	$.post(OC.filePath('ownpad_lite', 'ajax', 'settings.php'), data, function(){});
});