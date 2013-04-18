function hideDOCviewer() {
	$('#content table').show();
	$("#controls").show();
	$("#editor").show();
	$('iframe').remove();
	$('a.action').remove();
}

showDOCviewer.oldCode='';
showDOCviewer.lastTitle='';

function showDOCviewer(dir,filename){
	if(!showDOCviewer.shown){
		$("#editor").hide();
		$('#content table').hide();
		$("#controls").hide();
		var oldcontent = $("#content").html();
		var viewer = OC.linkTo('file_previewer', 'docViewer.php')+'?dir='+encodeURIComponent(dir).replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'));
		$("#content").html(oldcontent+'<iframe style="width:100%;height:100%;display:block;" src="'+viewer+'" />');
		$("#pageWidthOption").attr("selected","selected");
	}
}

$(document).ready(function(){
	if(!$.browser.msie){//doesn't work on IE
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('application/msword','Edit', OC.PERMISSION_READ, '',function(filename){
					showDOCviewer($('#dir').val(),filename);
				});
				FileActions.setDefault('application/msword','Edit');
			}
		}
	}
});
