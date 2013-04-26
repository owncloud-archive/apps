$(document).ready(function(){
  
  	function setAllowUsers(){
		$.post( OC.filePath('external','ajax','allowusers.php') , {allowUsers : $('#allowUsers:checked').val()}, function(data) {
		});
	}
	
	$('#allowUsers').click(function(){
		setAllowUsers();
	});
});
