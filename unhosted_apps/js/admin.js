$(document).ready(function(){



	$('#storage_origin').blur(function(event){
		event.preventDefault();
		var post = $( "#storage_origin" ).serialize();
		$.post( OC.filePath('unhosted_apps', 'ajax', 'setstorageorigin.php') , post, function(data){
			$('#unhosted_apps .msg').text('Finished saving: ' + data);
		});
	});



});
