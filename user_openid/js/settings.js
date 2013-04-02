$(document).ready(function(){
	$('#openidform #identity').blur(function(event){
		event.preventDefault();
		OC.msg.startSaving('#openidform .msg');
		var post = $( "#openidform" ).serialize();
		$.post( OC.filePath('user_openid', 'ajax', 'openid.php'), post, function(data){
			OC.msg.finishedSaving('#openidform .msg', data);
		});
	});
});
