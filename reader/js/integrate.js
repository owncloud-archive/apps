$(document).ready(function() {
	$('#fileList tr').each(function(){
		// data-file attribute to contain unescaped filenames.
		$(this).attr('data-file',decodeURIComponent($(this).attr('data-file')));
	});
	
	$('#file_action_panel').attr('activeAction', false);

	// See if url conatins the index 'reader'
	if(location.href.indexOf("reader")!=-1) {
		// Perform function on every click of a link, now and in future.
		$('td.filename a').live('click',function(event) {
			event.preventDefault();
			var filename=$(this).parent().parent().attr('data-file');
			var tr=$('tr').filterAttr('data-file',filename);
			var mime=$(this).parent().parent().data('mime');
			var type=$(this).parent().parent().data('type');
			// Check if clicked link is a pdf file or a directory, perform suitable function.
			var action=getAction(mime,type);
			if(action){
				action(filename);
			}
		});		
	}
});

/* Function that returns suitable function definition to be executed on 
 * click of the file whose mime and type are passed. */
function getAction(mime,type) {
	var name;
	if(mime == 'application/pdf') {
		name = function (filename){
			showPDFviewer($('#dir').val(),filename);
		}
	}
	else {
		name = function (filename){
			window.location=OC.linkTo('reader', 'index.php') + '&dir='+
			encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+
			encodeURIComponent(filename) + '/';
		}
	}
	return name;
}

