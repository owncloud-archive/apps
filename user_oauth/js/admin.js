$(document).ready(function(){



	$('#tokenInfoEndpoint').blur(function(event){
		event.preventDefault();
		var post = $( "#tokenInfoEndpoint" ).serialize();
		$.post( OC.filePath('user_oauth', 'ajax', 'seturl.php') , post, function(data){
			$('#user_oauth .msg').text('Finished saving: ' + data);
		});
	});



});
