function showfirstrunwizard(){
	$.colorbox({
		opacity:0.4, 
		transition:"elastic", 
		speed:100, 
		width:"70%", 
		height:"70%", 
		href: OC.filePath('firstrunwizard', 'templates', 'wizard.php'), 
		onClosed : function(){

			$.ajax({
			url: OC.filePath('firstrunwizard', 'ajax', 'disable.php'),
			data: ""
			});
		}  
	});
}
