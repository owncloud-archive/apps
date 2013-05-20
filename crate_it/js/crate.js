$(document).ready(function() {
	
	$('#download').click('click', function(event) { 
		if($('#crateList li').length == 0){
			OC.Notification.show('No items in the crate to package');
			setTimeout(function() {OC.Notification.hide();}, 1000);
			return;
		}
		OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
		setTimeout(function() {OC.Notification.hide();}, 2000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
		
	});
	
	$('#clear').click(function(event) {
		$.ajax(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=clear');
		$('#crateList').empty();
	});
	
});

