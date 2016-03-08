$(document).ready(function(){



	$('#introspectionEndpoint').blur(function(event){
		event.preventDefault();
		var post = $( "#introspectionEndpoint" ).serialize();
		$.post( OC.filePath('user_oauth', 'ajax', 'seturl.php') , post, function(data){
			$('#user_oauth .msg').text('Finished saving: ' + data);
		});
	});



});
