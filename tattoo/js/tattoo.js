$(document).ready(function() {
	$.ajax({
		url: OC.filePath('tattoo', 'ajax', 'bg.php'),
		success: function(response){
			$('#content-wrapper').prepend('<img alt="background" src="'+OC.filePath('tattoo', 'img', response.data)+'" id="tattoobg"/>');
		}
	});
});
