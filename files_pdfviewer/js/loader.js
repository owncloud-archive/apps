function hidePDFviewer() {
	$('#content table').show();
	$("#controls").show();
	$("#editor").show();
	$('iframe').remove();
	$('a.action').remove();
}

function showPDFviewer(dir,filename){
	if(!showPDFviewer.shown){
		$("#editor").hide();
		$('#content table').hide();
		$("#controls").hide();
		var oldcontent = $("#content").html();
		var viewer = OC.linkTo('files_pdfviewer', 'viewer.php')+'?dir='+encodeURIComponent(dir).replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'));
		$("#content").html(oldcontent+'<iframe style="width:100%;height:100%;display:block;" src="'+viewer+'" />');
		$("#pageWidthOption").attr("selected","selected");
	}
}
showPDFviewer.oldCode='';
showPDFviewer.lastTitle='';

$(document).ready(function(){
	if(!$.browser.msie){//doesn't work on IE
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('application/pdf','Edit', OC.PERMISSION_READ, '',function(filename){
					showPDFviewer($('#dir').val(),filename);
				});
				FileActions.setDefault('application/pdf','Edit');
			}
		}
	}
});
