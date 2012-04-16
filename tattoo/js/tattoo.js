$(document).ready(function() {
	$.ajax({
		url: OC.filePath('tattoo', 'ajax', 'bg.php'),
		success: function(response){
			$('#content').css('background-image', 'url('+OC.filePath('tattoo', 'img', response.data)+')');
		}
	});
});