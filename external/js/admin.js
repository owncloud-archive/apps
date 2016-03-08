$(document).ready(function(){

	// Handler functions
	function addSiteEventHandler(event) {
		event.preventDefault();

		saveSites();
	}

	function deleteButtonEventHandler(event) {
		event.preventDefault();

		if($(this).parent().is(':only-child')) {
			$(this).parent().children('input').val('');
			$(this).parent().children('select').val('');
		} else {
			$(this).tipsy('hide');
			$(this).parent().remove();
		}

		saveSites();
	}

	function saveSites() {
		var post = $('#external').serialize();
		OC.msg.startSaving('#external .msg');
		$.post( OC.filePath('external','ajax','setsites.php') , post, function(data) {
			OC.msg.finishedSaving('#external .msg', data);
		});
	}

	function showDeleteButton() {
		$(this).find('img.delete_button').fadeIn(100);
	}

	function hideDeleteButton() {
		$(this).find('img.delete_button').fadeOut(100);
	}

	// Initialize events
	$('input[name^=site_]').change(addSiteEventHandler);
	$('select[name^=site_]').change(addSiteEventHandler);
	$('img.delete_button').click(deleteButtonEventHandler);
	$('img.delete_button').tipsy();

	$('#external li').hover(showDeleteButton, hideDeleteButton);

	$('#add_external_site').click(function(event) {
		event.preventDefault();

		$('#external ul li:last').clone().appendTo('#external ul');
		$('#external ul li:last input').val('');
		$('#external ul li:last select').val('');

		$('input.site_url:last').prev('input.site_name').andSelf().change(addSiteEventHandler);
		$('img.delete_button').click(deleteButtonEventHandler);
		$('img.delete_button:last').tipsy();
		$('#external li:last').hover(showDeleteButton, hideDeleteButton);

	});

});
