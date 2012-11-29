$(document).ready(function(){

	OC.Router.registerLoadedCallback(function(){
		$('#somesetting').blur(function(event){
			
			event.preventDefault();
			var post = $( "#somesetting" ).serialize();
			var url = OC.Router.generate('apptemplate_advanced_ajax_setsystemvalue');

			$.post(url , post, function(data){
				$('#apptemplate .msg').text('Finished saving: ' + data);
			});

		});
	});

});
