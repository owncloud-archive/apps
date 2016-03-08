$(document).ready(function() {
	$('#fileList tr').each(function(){
		// data-file attribute to contain unescaped filenames.
		$(this).attr('data-file',decodeURIComponent($(this).attr('data-file')));
	});
	
	$('#file_action_panel').attr('activeAction', false);
	
});	


$(function() {
	// See if url conatins the index 'reader'
	if(location.href.indexOf("reader")!=-1) {
		'use strict';
		// create thumbnails for pdfs inside current directory.
		create_thumbnails();
		create_thumbnails_for_directories();
		// Render pdf view on every click of a thumbnail, now and in future.
		$('td.filename a.name').live('click',function(event) {
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
	
	$('form#TagForm').submit(function(event) {
		event.preventDefault(); 
		var path = $(this).parent().children('a.name').attr('dir');
		var result = $(this).parent().children('div#result');
		var $form = $(this),
			tag = $form.find( 'input[name="tag"]' ).val(),
			url = $form.attr('action');
		$.post( url, {tag:tag,path:path},
			function(data) {
				result.append('<div class = "each_result"><a id = "each_tag" "href = "apps/reader/fetch_tags.php?tag='+data+'">'+data+'</a><a id = "close" value = "'+data+'">x</a></div></div>');
			}
		);
	});
	
	$('a#close').click(function(){
		event.preventDefault(); 
		var elem = $(this).parent();
		var filepath = $(this).parent().parent().parent().children('a.name').attr('dir');
		var url = 'apps/reader/ajax/remove_tags.php';
		var tag = $(this).attr('value');
		elem.hide();
		$.post(url, {tag:tag, filepath:filepath});
	});
	
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
			window.location=OC.linkTo('reader', 'index.php') + '&dir='+
			encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+
			encodeURIComponent(filename) + '/';
		}
	}
	return name;
}

function create_thumbnails() {
	PDFJS.disableWorker = true;
		$('td#thumbnail_container > img').each(function() {
			// Get url and title of each pdf file from image tags.
			var title = $(this).parent().parent().attr('data-file');
			var location = $(this).attr('id');
			var url = OC.linkTo('files', 'download.php')+'?file=' + location;
			var thumbnail_exists = $(this).attr('value');
			if (thumbnail_exists == "false") {
			if (url.indexOf('pdf') != -1) {
				render_thumbnail(url,location,title);
			}
		}
	});
}

function create_thumbnails_for_directories() {
	$('div#thumbs img').each(function(){
		var thumb_exists = $(this).attr('value');
		if (thumb_exists == "false") {
			var location = $(this).attr('id');
			var url = OC.linkTo('files', 'download.php')+'?file=' + location;
			var title = location.replace(/\\/g,'/').replace( /.*\//, '' );
			if (url.indexOf('pdf') != -1) {
				render_thumbnail(url,location,title);
			}
		}
	});
}

function canvasSaver(canvas,title,location) {
	var canvas_data = canvas.toDataURL('image/png');
	$.post("apps/reader/ajax/canvas_saver.php", {canv_data:canvas_data,title:title,location:location});
}


function render_thumbnail(url,location,title) {
	
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
							var imageElement = document.getElementById(location);
							imageElement.src = canvas.toDataURL();
							imageElement.style.height = '100px';
							imageElement.style.width = '100px';
							
						});
					});
				});
}
