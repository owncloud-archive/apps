$(document).ready(function(){
	$('#activity_notifications input[type=checkbox]').change(function(){
		OC.msg.startSaving('#activity_notifications_msg');
		var post = $( '#activity_notifications' ).serialize();
		$.post(OC.filePath('activity', 'ajax', 'settings.php'), post, function(data){
			OC.msg.finishedSaving('#activity_notifications_msg', data);
		});
	});

	$('#activity_notifications select').change(function(){
		OC.msg.startSaving('#activity_notifications_msg');
		var post = $( '#activity_notifications' ).serialize();
		$.post(OC.filePath('activity', 'ajax', 'settings.php'), post, function(data){
			OC.msg.finishedSaving('#activity_notifications_msg', data);
		});
	});

	$('#activity_notifications .activity_select_group').click(function(){
		var selectGroup = '#activity_notifications .' + $(this).attr('data-select-group');
		var checkedBoxes = $(selectGroup + ':checked').length;
		$(selectGroup).attr('checked', true);
		if (checkedBoxes === $(selectGroup + ':checked').length) {
			// All values were already selected, so invert it
			$(selectGroup).attr('checked', false);
		}

		OC.msg.startSaving('#activity_notifications_msg');
		var post = $( '#activity_notifications' ).serialize();
		$.post(OC.filePath('activity', 'ajax', 'settings.php'), post, function(data){
			OC.msg.finishedSaving('#activity_notifications_msg', data);
		});
	});
});
