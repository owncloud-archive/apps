function getFileName(dir, filename) {
	var baseUrl = '';
	if(dir === '/'){
		baseUrl = filename;	
	}
	else{
		baseUrl = dir.replace(/^\//g,'') + '/' + filename;
	}
	return baseUrl;
}

$(document).ready(function(){
	if(!$.browser.msie){//doesn't work on IE
		
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('all','Add to crate', OC.PERMISSION_READ, '',function(filename){
					$.ajax({url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?file='+getFileName($('#dir').val(),filename)+'&action=add',
						type: 'get',
						dataType: 'text/html',
						complete: function(data){
							OC.Notification.show(data.responseText);
							setTimeout(function() {OC.Notification.hide();}, 3000);
						}
					});
				});
			}
		}
		
		
	}
});

function addToTree(file){
	//$.ajax({url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?dir='+encodeURIComponent($('#dir').val())
		//.replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'))+'&action=add'
}
