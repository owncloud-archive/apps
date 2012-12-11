jQuery(document).ready(function () {
	$.colorbox({
		opacity:0.4, 
		transition:"elastic", 
		speed:300, 
		width:"70%", 
		height:"70%", 
		href:oc_webroot+"/apps/firstrunwizard/templates/wizard.php", 
		onClosed : function(){

			$.ajax({
			url: OC.filePath('firstrunwizard', 'ajax', 'disable.php'),
			data: ""
			});
		}  
	});
});
