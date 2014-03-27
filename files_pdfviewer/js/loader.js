function hidePDFviewer() {
	$('#content table').show();
	$("#controls").show();
	$("#editor").show();
	$('#pdframe, #pdfbar').remove();
	if ($('#isPublic').val()){
		$('#preview').css({height: null});
	}
}

function showPDFviewer(dir,filename) {
	if(!showPDFviewer.shown) {
		var $iframe;
		$("#editor").hide();
		$('#content table').hide();
		$("#controls").hide();
		var viewer = OC.linkTo('files_pdfviewer', 'viewer.php')+'?dir='+encodeURIComponent(dir).replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename);
		$iframe = $('<iframe id="pdframe" style="width:100%;height:100%;display:block;" src="'+viewer+'" /><div id="pdfbar"><a id="close" title="Close">X</a></div>');
		if ($('#isPublic').val()) {
			// force the preview to adjust its height
			$('#preview').append($iframe).css({height: '100%'});
		} else {
			$('#content').append($iframe);
		}
		$("#pageWidthOption").attr("selected","selected");
		$('#pdfbar').css({position:'absolute',top:'6px',right:'5px'});
		// if a filelist is present, the PDF viewer can be closed to go back there
		if ($('#fileList').length) {
				$('#close').css({display:'block',padding:'0 5px',color:'#BBBBBB','font-weight':'900','font-size':'16px',height:'18px',background:'transparent'}).click(function(){
				hidePDFviewer();
			});
		} else {
			$('#close').css({display:'none'});
		}
	}

}
showPDFviewer.oldCode='';
showPDFviewer.lastTitle='';

$(document).ready(function(){
	// doesn't work in IE or public link mode
	if(!$.browser.msie && !$('#isPublic').val()){
		if ($('#filesApp').val() && typeof FileActions!=='undefined'){
			FileActions.register('application/pdf','Edit', OC.PERMISSION_READ, '',function(filename){
				showPDFviewer($('#dir').val(),filename);
			});
			FileActions.setDefault('application/pdf','Edit');
		}
	}
});
