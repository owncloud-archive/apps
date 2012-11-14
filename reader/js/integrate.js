$(document).ready(function() {
	$('#fileList tr').each(function(){
		// data-file attribute to contain unescaped filenames.
		$(this).attr('data-file',decodeURIComponent($(this).attr('data-file')));
	});
	
	$('#file_action_panel').attr('activeAction', false);

	$('td.filename.svg a.dirs').hover(function(){
		$(this).children().children('img#img2').animate({
			left: '-=60'}, 50, function(){
			});
		$(this).children().children('img#img3').animate({
			left: '+=60'}, 50, function(){
			});
	});
	$('td.filename.svg a.dirs').mouseleave(function(){
		$(this).children().children('img#img2').animate({
			left: '+=120'}, 50, function(){
			});
		$(this).children().children('img#img3').animate({
			left: '-=120'}, 50, function(){
			});
	});
});	

$(function() {
	// See if url conatins the index 'reader'
	if(location.href.indexOf("reader")!=-1) {
		'use strict';
		// create thumbnails for pdfs inside current directory.
		create_thumbnails();

		// Render pdf view on every click of a thumbnail, now and in future.
		$('td.filename a').live('click',function(event) {
			event.preventDefault();
			var filename=$(this).parent().parent().attr('data-file');
			var tr=$('tr').filterAttr('data-file',filename);
			var mime=$(this).parent().parent().data('mime');
			var type=$(this).parent().parent().data('type');
			// Check if clicked link is a pdf file or a directory, perform suitable function.
			var action=getAction(mime,type);
			if(action){
				action(filename);
			}
		});
	}
});

/* Function that returns suitable function definition to be executed on 
 * click of the file whose mime and type are passed. */
function getAction(mime,type) {
	var name;
	if(mime == 'application/pdf') {
		name = function (filename){
			showPDFviewer($('#dir').val(),filename);
		}
	}
	else {
		name = function (filename){
			window.location=OC.linkTo('reader', 'index.php') + '?dir='+
			encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+
			encodeURIComponent(filename) + '/';
		}
	}
	return name;
}

function create_thumbnails() {
	PDFJS.disableWorker = true;
		$('td.filename a.name').each(function() {
			// Get url and title of each pdf file from anchor tags.
			var url = $(this).attr('href');
			var title = $(this).parent().parent().attr('data-file');
			var location = $(this).attr('dir');
			var thumbnail_exists = $(this).attr('value');
			if (thumbnail_exists == "false") {
			if (url.indexOf('pdf') != -1) {
				PDFJS.getDocument(url).then(function(pdf) {
				// Using promise to fetch the page
					pdf.getPage(1).then(function(page) {
						var scale = 0.2;
						var viewport = page.getViewport(scale);
						
						// Create canvas elements for each pdf's first page.
						var canvas = document.createElement("canvas");
						
						// Canvas elements should be of proper size, not too big, not too small. 
						if (viewport.height > 170 || viewport.width > 130) {
							scale = 0.1;
						}
						else if (viewport.height < 129 || viewport.width < 86) {
							scale = 0.3;
						}
						
						viewport = page.getViewport(scale);
						canvas.height = viewport.height;
						canvas.width = viewport.width;
						
						var ctx = canvas.getContext('2d');
						ctx.save();
						ctx.fillStyle = 'rgb(255, 255, 255)';
						ctx.fillRect(0, 0, canvas.width, canvas.height);
						ctx.restore(); 
						
						var view = page.view;
						var scaleX = (canvas.width / page.width);
						var scaleY = (canvas.height / page.height);
						ctx.translate(-view.x * scaleX, -view.y * scaleY);
					
						// Render PDF page into canvas context
						var renderContext = {
							canvasContext: ctx,
							viewport: viewport
						};
					
						pageRendering = page.render(renderContext);
						pageRendering.onData(function(){
							canvasSaver(canvas,title,location);
						});
					});
				});
			}
		}
	});
}

function canvasSaver(canvas,title,location) {
	var canvas_data = canvas.toDataURL('image/png');
	$.post("apps/reader/ajax/canvas_saver.php", {canv_data:canvas_data,title:title,location:location});
}


