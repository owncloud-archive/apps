$(document).ready(function() {
	
	$('.download').click('click', function(event) { 
		OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
		
	});
	
	$('.clear').click(function(event) {
		$.ajax(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=clear');
		window.location = OC.linkTo('crate_it', 'index.php');
	});
	
	/*$('.download').click('click',function(event) {
		var files=getSelectedFiles('name');
		var fileslist = JSON.stringify(files);
		var dir=$('#dir').val()||'/';
		OC.Notification.show(t('files','Your download is being prepared. This might take some time if the files are big.'));
		// use special download URL if provided, e.g. for public shared files
		if ( (downloadURL = document.getElementById("downloadURL")) ) {
			window.location=downloadURL.value+"&download&files="+files;
		} else {
			window.location=OC.filePath('files', 'ajax', 'download.php') + '?'+ $.param({ dir: dir, files: fileslist });
		}
		return false;
	});
	
	$('.delete').click(function(event) {
		var files=getSelectedFiles('name');
		event.preventDefault();
		FileList.do_delete(files);
		return false;
	});

	// drag&drop support using jquery.fileupload
	// TODO use OC.dialogs
	$(document).bind('drop dragover', function (e) {
			e.preventDefault(); // prevent browser from doing anything, if file isn't dropped in dropZone
	});*/
	
	
	
});

