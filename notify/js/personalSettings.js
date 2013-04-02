$(document).ready(function() {
	$('tr.notificationClass input[type="checkbox"]').change(function(e) {
		var block = $(this).is(':checked');
		var id = parseInt($(this).parentsUntil('tr').parent().toggleClass('notify-blocked', block).attr('data-notify-class-id'));
		$.post(
			OC.filePath('notify', 'ajax', 'blacklist.php'),
			{id: id, block: block ? 1 : 0},
			function(data) {
				if(data.status != "success") {
					OC.dialogs.alert(data.message, 'Error');
					$(this).attr('checked', !block).parentsUntil('tr').parent().toggleClass('notify-blocked', !block);
				}
			}
		);
	});
	$('#notify-block-all').change(function(e) {
		var blockAll = $(this).is(':checked');
		$('.notificationClass input[type="checkbox"]').each(function(i, el) {
			if(blockAll != $(el).is(':checked')) {
				$(el).attr('checked', blockAll).change();
			}
		});
	});
	$('tr.notificationClass').click(function(e) {
		var box = $(this).find('input[type="checkbox"]');
		box.attr('checked', !box.is(':checked')).change();
	});
});
