$(document).ready(function(){
	$('#activity_notifications .personalblock input[type=checkbox]').change(function(){
		OC.msg.startSaving('#activity_notifications_msg');
		var post = $( '#activity_notifications' ).serialize();
		$.post( OC.filePath('activity', 'ajax', 'settings.php'), post, function(data){
			OC.msg.finishedSaving('#activity_notifications_msg', data);
		});
	});
});
