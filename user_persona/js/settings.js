$(document).ready(function(){
	$('#mozilla-persona-policy').change(function(){
		$.post(OC.filePath('user_persona', 'ajax', 'save.php'),
		{ policy : $(this).val()},
		function(){}
	)
	});
});    