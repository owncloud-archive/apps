var cr8it = {
	mimeTypes : [
	     'application/msword',
	     'application/pdf',
	     'text/plain',
	     'image'
	]
};

$(document).ready(function(){
	if(!$.browser.msie){//doesn't work on IE
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				for(var i = 0; i < cr8it.mimeTypes.length; i++){
					var mime = cr8it.mimeTypes[i];
					FileActions.register(mime,'Add to crate', OC.PERMISSION_READ, '',function(filename){
						$.ajax({url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?dir='+encodeURIComponent($('#dir').val())
							.replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'))+'&action=add',
							type: 'get',
							dataType: 'text/html',
							complete: function(data){
								OC.Notification.show(data.responseText);
								setTimeout(function() {OC.Notification.hide();}, 1000);
							}
						});
					});
				}
			}
		}
		
		
	}
});

function addToTree(file){
	
}
