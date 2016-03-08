var svgCanvas = null;
var svgString = "";
var postFile = function(file) {
    if(typeof(file) == 'undefined') {
        return {filecontents: '', path: '', mtime: 0};
    } else {
        return {
            filecontents: file.contents,
            path: file.path,
            mtime: file.mtime
        };
    }
}
var ocsvg = {
    frameDoc: null,
    frameWin: null,
    prefsShown: false,
    currentFile: null,
    pngFile: null,
    pdfFile: null,
	changed: false,
	setTitle: function() {
		var filename = ocsvg.currentFile.path.replace(/^\/(.*\/)*/, '');
		document.title = filename + ' - ownCloud';
	},
    setEditorSize: function() {
        // Fits the size of editor area to the available space
        fillWindow($('#svgEditor'));
    },
    setFilePath: function(newPath) {
        // set a new path for saving the file
        this.currentFile.path = newPath;
    },
    setFileContents: function(newContents) {
        // set file contents
        this.currentFile.filecontents = newContents;
    },
    setFileMTime: function(mtime) {
        // set last modified time of the file
        this.currentFile.mtime = mtime;
    },
	setFileContentsSvg: function(svgString, e) {
        if(e) {
            console.log("getSvgString error:", e);
        }
		ocsvg.setFileContents('<?xml version="1.0" encoding="UTF-8" standalone="no"?>\n' + svgString);
	},
	init: function(file) {
		ocsvg.currentFile = new postFile(file);
        var fileRegex = /(\..{3})?$/;
        var ext = ['svg', 'png', 'pdf'];
        for(var i in ext) {
            document.getElementById(ext[i] + 'SavePath').value = file.path.replace(fileRegex, '.' + ext[i]);
        }
	},
	saveFile: function(data, callback) {
        if(!data.file.path) {
            return;
        } else {
            $.post(
                OC.filePath('files_svgedit','ajax','save.php'),
				data,
                function(result) {
					console.log("ajax result:", result);
                    if(result.status == 'error') {
                        if(typeof(OCdialogs) != 'undefined' && typeof(OCdialogs.confirm) == 'function') {
							OCdialogs.confirm(
								result.data.message + '<p>' + t('files_svgedit', 'Overwrite file?') + '</p>',
								t('files_svgedit', 'Error'),
								function(force) {
									if(force) {
										ocsvg.saveFile($.extend(data, {force: true}), callback);
									}
								}
							);
						} else {
							if(confirm(result.data.message + '\n' + t('files_svgedit', 'Overwrite file?'))) {
								ocsvg.saveFile($.extend(data, {force: true}), callback);
							}
						}
                    }
                    if(typeof(callback) == 'function') {
                        callback(result);
                    }
                },
                'json'
            );
        }
	},
	canvasRender: function(svgString, canvasSelector, callback) {
		var canvas = $(canvasSelector)[0];
		canvas.width = ocsvg.frameWin.svgCanvas.contentW;
		canvas.height = ocsvg.frameWin.svgCanvas.contentH;
		canvg(canvas, svgString, {renderCallback: function() {
            if(typeof(callback) == 'function') {
                callback();
            }
        }});
	},
	changedHandler: function() {
		if(!ocsvg.changed) {
			ocsvg.changed = true;
		}
	},
	confirmExit: function() {
		if(ocsvg.changed) {
			return t('files_svgedit', 'File has unsaved content. Really want to quit?');
		} else {
			return null;
		}
	}
};

$(document).ready(function() {
	// initialize UI elements:
	$('div#svgEditorSave').tabs().dialog({
		autoOpen: false,
		modal: true,
        minWidth: 400
	});
    // make preview divs vertically resizable:
    //$('div#canvasPreview, div#svgtopdfPreview').resizable({handles: 's'});

    // set button onclick handlers:
    $('#ocsvgBtnSave').click(function() {
		$('#svgEditorSave').dialog('open');
    });
	$('#ocsvgBtnPrint').click(function() {
		svgCanvas.getSvgString()(function(svg, error) {
			var printWin = window.open();
			printWin.document.write('<?xml version="1.0" encoding="UTF-8" standalone="no"?>\n' + svg);
			printWin.print();
			printWin.close();
		});
	});
	$('#svgSaveBtn').click(function() {
		svgCanvas.getSvgString()(function(svg, error) {
			var btn = $('#svgSaveBtn');
			btn.val(t('files_svgedit', 'Saving...'));
			ocsvg.setFileContentsSvg(svg);
			ocsvg.setFilePath($('#svgSavePath').val());
			ocsvg.saveFile({file: ocsvg.currentFile}, function(result) {
				ocsvg.setTitle();
				if(result.status == 'error') {
					OCdialog.alert(result.data.message, t('files_svgedit', 'Could not save'));
				} else {
					if(result.status == 'success') {
						ocsvg.changed = false;
						ocsvg.currentFile.mtime = result.data.mtime;
					}
				}
				btn.val(t('files_svgedit', 'Save'));
			});
		});
	});
    $('#svgDownloadBtn').click(function() {
        if(ocsvg.currentFile.filecontents.length > 0) {
            window.open('data:image/svg+xml;base64,' + Base64.encode(ocsvg.currentFile.filecontents), "svgDownloadWin");
        }
    });
	$('#canvasRenderBtn').click(function() {
		$('#canvasRenderBtn').val(t('files_svgedit', 'Rendering...'));
		svgCanvas.getSvgString()(function(svg, error) {
            ocsvg.canvasRender(svg, '#exportCanvas', function() {
               	if(ocsvg.pngFile == null) {
					ocsvg.pngFile = new postFile();
				}
                ocsvg.pngFile.filecontents = $('#exportCanvas').get(0).toDataURL('image/png');
                $('#canvasRenderBtn').val(t('files_svgedit', 'Render'));
            });
        });
	});
    $('#pngSaveBtn').click(function() {
        if(ocsvg.pngFile != null) {
            $(this).val(t('files_svgedit', 'Saving...'));
            ocsvg.pngFile.path = $('#pngSavePath').val();
            ocsvg.saveFile({
                file: ocsvg.pngFile,
                base64encoded: true,
                base64type: 'image/png'
            }, function(result) {
                $('#pngSaveBtn').val(t('files_svgedit', 'Save'));
				if(result.status == 'success') {
					ocsvg.pngFile.mtime = result.data.mtime;
				}
            });
        }
    });
    $('#pngDownloadBtn').click(function() {
        if(ocsvg.pngFile.filecontents.length > 0) {
            window.open(ocsvg.pngFile.filecontents, "exportedPngWin");
        }
    });
    $('#pdfRenderBtn').click(function() {
        var w = ocsvg.frameWin.svgCanvas.contentW * 72 / 96;
		var h = ocsvg.frameWin.svgCanvas.contentH * 72 / 96;
        svgCanvas.getSvgString()(function(svg, err) {
            $('#svgtopdfPreview').empty();
            svgString = svg;
            var svgElement = $(svg).appendTo('#svgtopdfPreview');
            if(ocsvg.pdfFile == null) {
				ocsvg.pdfFile = new postFile();
			}
            var pdfDoc = new jsPDF('p', 'pt', [w, h]);
            svgElementToPdf(svgElement, pdfDoc, {
                preview: true,
                scale: 72/96
            });
            ocsvg.pdfFile.filecontents = 'data:application/pdf;base64,' + Base64.encode(pdfDoc.output());
        });
    });
    $('#pdfSaveBtn').click(function() {
        if(ocsvg.pdfFile != null) {
            $(this).val(t('files_svgedit', 'Saving...'));
            ocsvg.pdfFile.path = $('#pdfSavePath').val();
            ocsvg.saveFile({
                file: ocsvg.pdfFile,
                base64encoded: true,
                base64type: 'application/pdf'
            }, function(result) {
                $('#pdfSaveBtn').val(t('files_svgedit', 'Save'));
				if(result.status == 'success') {
					ocsvg.pdfFile.mtime = result.data.mtime;
				}
            });
        }
    });
    $('#pdfDownloadBtn').click(function() {
        if(ocsvg.pdfFile.filecontents.length > 0) {
            window.open(ocsvg.pdfFile.filecontents, "exportedPdfWin");
        }
    });
        
    $('#ocsvgBtnClose').click(function() {
		var dir = ocsvg.currentFile.path.replace(/\/[^\/]*$/, '');
		window.location = OC.linkTo('files', 'index.php') + (dir == '' ? '' : '?dir=') + dir;
	});

    // import file
	ocsvg.init(ocsvgFile);
    
    // set editor's size fit into the window when resizing it:
    $(window).resize(function() {
        ocsvg.setEditorSize();
        //$('div#svgEditorSave').dialog('option', 'maxHeight', window.innerHeight);
    }).resize();
    
    // initialize editor frame:
    var frame = document.getElementById('svgedit');
    $(frame).load(function() {
        ocsvg.frameDoc = $(frame).contents();
        ocsvg.frameWin = frame.contentWindow;
        svgCanvas = new embedded_svg_edit(frame);
        
        // hide main menu button, then shift the tool bar to the left border:
        ocsvg.frameDoc.find('#main_button').hide().next().css('left', 0).css('padding-left', 2).css('padding-top', 2);
        // fix broken color select field
        ocsvg.frameDoc.find('#fill_color,#stroke_color').find('svg').css('height', '100%');
        // set handler for show preferences button:
        $('#ocsvgBtnPrefs').click(function() {
            ocsvg.frameDoc.find('#svg_prefs').toggle();
        });
        // set handler for preferences cancel button:
        ocsvg.frameDoc.find('#tool_prefs_cancel').click(function() {
            ocsvg.frameDoc.find('#svg_prefs').hide();
        });
        // set handler for show properties button:
        $('#ocsvgBtnProps').click(function() {
            ocsvg.frameDoc.find('#svg_docprops').toggle();
        });
        // set handler for properties cancel button:
        ocsvg.frameDoc.find('#tool_docprops_cancel').click(function() {
            ocsvg.frameDoc.find('#svg_docprops').hide();
        });
        
        svgCanvas.setSvgString(ocsvg.currentFile.filecontents)(function(data, error) {
            if(error) {
                alert("Could not load file!\n\n" + error);
            }
			//TODO: svgCanvas.bind doesn't work here as I expect...
			ocsvg.frameWin.svgCanvas.bind('changed', ocsvg.changedHandler);
        });
		ocsvg.setTitle();
		// set confirmation on exit (only if content has changed);
		ocsvg.frameWin.onbeforeunload = ocsvg.confirmExit;
    });
});
