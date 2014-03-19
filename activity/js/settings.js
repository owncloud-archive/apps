$(document).ready(function(){
	$('#activity_notifications .personalblock input[type=checkbox]').change(function(){
		var post = $( '#activity_notifications' ).serialize();
		$.post( OC.filePath('activity', 'ajax', 'settings.php'), post, function(data){return;});
		return false;
	});
});
