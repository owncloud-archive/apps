function viewOdf(dir, file) {
    OC.addStyle('files_odfviewer', 'webodf');
    OC.addStyle('files_odfviewer', 'odfviewer');
    OC.addScript('files_odfviewer','webodf').done(function(){
        var location = fileDownloadPath(dir, file);

		// start viewer mode
		FileList.setViewerMode(true);

		// odf action toolbar
		var odfToolbarHtml =
			'<div id="odf-toolbar">' +
			'<button id="odf_close">'+t('files_odfviewer','Close')+
			'</button></div>';
		$('#controls').append(odfToolbarHtml);

		var canvashtml = '<div id="odf-canvas"></div>';
		$('table').after(canvashtml);
		// in case we are on the public sharing page we shall display the odf into the preview tag
		$('#preview').html(canvashtml);

		var odfelement = document.getElementById("odf-canvas");
		var odfcanvas = new odf.OdfCanvas(odfelement);
		odfcanvas.load(location);
    });
}

function closeOdfViewer(){
	// Remove odf-toolbar
	$('#odf-toolbar').remove();
	$('#odf-canvas').remove();
	FileList.setViewerMode(false);
	is_editor_shown = false;
}

$(document).ready(function() {
	if(typeof FileActions!=='undefined'){

		var supportedMimes = new Array(
			'application/vnd.oasis.opendocument.text', 
			'application/vnd.oasis.opendocument.spreadsheet',
			'application/vnd.oasis.opendocument.graphics',
			'application/vnd.oasis.opendocument.presentation');
		for (var i = 0; i < supportedMimes.length; ++i){
			var mime = supportedMimes[i];
			FileActions.register(mime,'View',OC.PERMISSION_READ,'',function(filename){
				viewOdf($('#dir').val(),filename);
			});
			FileActions.setDefault(mime,'View');
		}
	}
	
	$('#odf_close').live('click',function() {
		closeOdfViewer();	
	});
});
