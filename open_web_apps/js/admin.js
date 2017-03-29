$(document).ready(function(){



	$('#storage_origin').blur(function(event){
		event.preventDefault();
		var post = $( "#storage_origin" ).serialize();
		$.post( OC.filePath('open_web_apps', 'ajax', 'setstorageorigin.php') , post, function(data){
			$('#open_web_apps .msg').text('Finished saving: ' + data);
		});
	});



});
