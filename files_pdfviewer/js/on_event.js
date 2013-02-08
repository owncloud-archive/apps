$(document).ready(function(){    
	$('#viewThumbnail').on('click', function(){
		PDFView.switchSidebarView('thumbs');
	});
	$('#viewOutline').on('click', function(){
		PDFView.switchSidebarView('outline');
	});
	$('#viewSearch').on('click', function(){
		PDFView.switchSidebarView('search');
	});
	$('#searchButton').on('click', function(){
		PDFView.search();
	});
	$('#pageUp').on('click', function(){
		PDFView.page--;
	});
	$('#pageDown').on('click', function(){
		PDFView.page++;
	});
	$('#pageNumber').on('change', function(){
		PDFView.page = this.value;
	});
	$('#searchTermsInput').on('keydown', function(){
		if (event.keyCode == 13) PDFView.search();
	});
	$('#fileInput').on('contextmenu', function(){
		return false;
	});
	$('#fullscreen').on('click', function(){
		PDFView.fullscreen();
	});
	$('#print').on('click', function(){
		window.print();
	});
	$('#download').on('click', function(){
		PDFView.download();
	});
	$('#close').on('click', function(){
		window.parent.hidePDFviewer();
	});
	$('#zoomOut').on('click', function(){
		PDFView.zoomOut();
	});
	$('#zoomIn').on('click', function(){
		PDFView.zoomIn();
	});
	$('#scaleSelect').on('change', function(){
		PDFView.parseScale(this.value);
	});
	$('#scaleSelect').on('contextmenu', function(){
		return false;
	});
	$('#errorShowMore').on('contextmenu', function(){
		return false;
	});
	$('#errorShowLess').on('contextmenu', function(){
		return false;
	});
	$('#errorClose').on('contextmenu', function(){
		return false;
	});
});