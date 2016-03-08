function viewOdf(dir, file, fileList) {
    OC.addStyle('files_odfviewer', 'webodf');
    OC.addStyle('files_odfviewer', 'odfviewer');
    OC.addScript('files_odfviewer','webodf').done(function(){
        var location = fileList.getDownloadUrl(file, dir);

		// start viewer mode
		fileList.setViewerMode(true);

		// odf action toolbar
		var odfToolbarHtml =
			'<div id="odf-toolbar">' +
			'<button id="odf_close">'+t('files_odfviewer','Close')+
			'</button></div>';
		fileList.$el.find('#controls').append(odfToolbarHtml);

		var canvashtml = '<div id="odf-canvas"></div>';
		fileList.$table.after(canvashtml);
		// in case we are on the public sharing page we shall display the odf into the preview tag
		$('#preview').html(canvashtml);

		var odfelement = $('#odf-canvas');
		odfelement.data('fileList', fileList);
		var odfcanvas = new odf.OdfCanvas(odfelement.get(0));
		odfcanvas.load(location);
    });
}

function closeOdfViewer(){
	// Remove odf-toolbar
	var fileList = $('#odf-canvas').data('fileList');
	$('#odf-toolbar').remove();
	$('#odf-canvas').remove();
	fileList.setViewerMode(false);
	is_editor_shown = false;
}

$(document).ready(function() {
	if(typeof OCA !== 'undefined' && OCA.Files) {
		var fileActions = OCA.Files.fileActions;
		var supportedMimes = new Array(
			'application/vnd.oasis.opendocument.text', 
			'application/vnd.oasis.opendocument.spreadsheet',
			'application/vnd.oasis.opendocument.graphics',
			'application/vnd.oasis.opendocument.presentation');
		for (var i = 0; i < supportedMimes.length; ++i){
			var mime = supportedMimes[i];
			fileActions.register(mime, 'View', OC.PERMISSION_READ, '', function(filename, context){
				viewOdf(context.dir, filename, context.fileList);
			});
			fileActions.setDefault(mime,'View');
		}
	}
	
	$('#odf_close').live('click',function() {
		closeOdfViewer();	
	});
});
