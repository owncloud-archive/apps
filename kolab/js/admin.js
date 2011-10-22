$(document).ready(function(){



	$('#kolaburl').blur(function(event){
		event.preventDefault();
		var post = $( "#kolaburl" ).serialize();
		$.post( OC.filePath('kolab','ajax','seturl.php') , post, function(data){ OC.msg.finishedSaving('#kolaburl .msg', data);   });
	});



});


