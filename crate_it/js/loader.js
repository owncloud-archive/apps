$(document).ready(function(){
	if(!$.browser.msie){//doesn't work on IE
		
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('all','Add to cart', OC.PERMISSION_READ, '',function(filename){
					$.ajax(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?dir='+encodeURIComponent($('#dir').val())
						.replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'))+'&action=add');
				});
			}
		}
		
		
	}
});
