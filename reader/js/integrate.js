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
		// Generate thumbnails for folders.
		create_folder_thumbnails();
		
		// On close of the pdf viewer, reload the page.
		$('#close').live('click',function(event) {
			location.reload();
		});
		// On hover over pdf thumbnails, their title should show.
		$('a.name').hover(function(){
			if($(this).children().hasClass('title'))
				$(this).children().addClass('visible');
		});
		$('a.name').mouseleave(function(){
			if($(this).children().hasClass('title'))
				$(this).children().removeClass('visible');
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
			window.location=OC.linkTo('reader', 'index.php') + '&dir='+
			encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+
			encodeURIComponent(filename) + '/';
		}
	}
	return name;
}

function create_thumbnails() {
	PDFJS.disableWorker = true;
		$('td.filename a').each(function() {
			// Get url and title of each pdf file from anchor tags.
			var url = $(this).attr('href');
			var title = $(this).parent().parent().attr('data-file');
			if (url.indexOf('pdf') != -1) {
				PDFJS.getDocument(url).then(function(pdf) {
				// Using promise to fetch the page
					pdf.getPage(1).then(function(page) {
						var scale = 0.2;
						var viewport = page.getViewport(scale);
						
						var div = document.createElement('div');
						div.id = 'thumbnailContainer';
						div.className = 'thumbnail';
						var anchor = document.getElementById(url);
						// Create canvas elements for each pdf's first page.
						var canvas = document.createElement("canvas");
						canvas.id = 'thumbnail';
						
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
						
						div.style.height = canvas.height + 'px';
						div.style.width = canvas.width + 'px';
						div.appendChild(canvas);
						anchor.appendChild(div);
						
						var title_div = document.createElement('div');
						title_div.className = 'title';
						title_div.innerHTML = title;
						anchor.appendChild(title_div);
					
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
						page.render(renderContext);
					});
				});
			}
		});
}

// Function to create thumbnails for folders.
function create_folder_thumbnails() {
	$('a.dirs input').each(function() {
		// fetch margin, url and and directory name values for each pdf, stored in input tags.
			var margin = $(this).attr('name');
			var pdf_dir = $(this).attr('value');
			var url = $(this).attr('id');
			
			PDFJS.getDocument(url).then(function(pdf) {
				// Using promise to fetch the page
					pdf.getPage(1).then(function(page) {
						var scale = 0.3;
						var viewport = page.getViewport(scale);
					
						// Prepare canvas using PDF page dimensions
						var anchor = document.getElementById(pdf_dir);
			
						var canvas = document.createElement("canvas");
						canvas.id = "dirsCanvas";
						// Each thumbnail in the 3-thumbnail set should be of same dimensions.
						canvas.height = '168';
						canvas.width = '120';
						// Canvases should be on top of each other
						$(canvas).css('z-index',100 - margin);
						$(canvas).css('margin-left', margin  + 'px');
						$(canvas).css('-webkit-backface-visibility', 'visible');
						$(canvas).css('-webkit-transform-origin', '0% 51%');
						$(canvas).css('-webkit-transform',' perspective(' + margin*35 + 'px) rotateY(23deg)');
				
						anchor.appendChild(canvas);

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
						page.render(renderContext);
					});
			});
	});
}
