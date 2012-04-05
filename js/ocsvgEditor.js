var svgCanvas = null;
var ocsvg = {
    frameDoc: null,
    frameWin: null,
    prefsShown: false,
    currentFile: {
        filecontents: '',
        path: '',
        mtime: 0
    },
	exportFile: {
		filecontents: '',
		path: '',
		mtime: 0
	},
	changed: false,
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
		ocsvg.setFileContents('<?xml version="1.0" encoding="UTF-8" standalone="no"?>\n' + svgString);
	},
	init: function(file) {
		ocsvg.setFileContents(file.contents);
		ocsvg.setFilePath(file.path);
		ocsvg.setFileMTime(file.mtime);
	},
	saveFile: function(data, callback) {
        var savePath;
		if(data.force) {
	   		savePath = data.file.path;
		} else {
			savePath = prompt(t('files_svgedit', 'Save as'), data.file.path);
		}
        if(savePath === null || savePath == '') {
            return;
        } else {
            data.file.path = savePath;
            $.post(
                OC.filePath('files_svgedit','ajax','save.php'),
				data,
                function(result) {
                    if(result.status!='success'){
                        // Save failed
                        data.force = confirm(
							t('files_svgedit', 'Could not save:') + "\n"
							+ savePath + "\n"
							+ result.data.message + "\n"
							+ t('files_svgedit', 'Save anyway?')
						);
						if(callback && callback.error) {
							callback.error(result);
						}
                        ocsvg.saveFile(data);
                    } else {
                        // Save OK
                        // Update mtime:
                        data.file.mtime = result.data.mtime;
						if(callback && callback.success) {
							callback.success(result);
						}
                        alert(t('files_svgedit', 'Successfully saved!'));
                    }
                },
                'json'
            );
        }
	},
    save: function(svgString, error) {
        if(error) {
            alert("Couldn't get SVG contents:\n\n" + error);
            return;
        }
        ocsvg.setFileContentsSvg(svgString);
		//saveFile
		ocsvg.saveFile(
			{
				file: ocsvg.currentFile,
			},
			{success: function(result) {ocsvg.changed = false;}}
		);
    },
	pngExport: function(svgString, error) {
        if(error) {
            alert("Couldn't get SVG contents:\n\n" + error);
            return;
        }
		// reimplementing png export is easier than trying to use svg-edit handlers...
		if(!$('#exportCanvas').length) {
			$('<canvas>', {id: 'exportCanvas'}).hide().appendTo('body');
		}
		var canvas = $('#exportCanvas')[0];

		canvas.width = ocsvg.frameWin.svgCanvas.contentW;
		canvas.height = ocsvg.frameWin.svgCanvas.contentH;
		$('#ocsvgBtnExport').val(t('files_svgedit', 'Rendering...'));
		canvg(canvas, svgString, {renderCallback: function() {
			var datauri = canvas.toDataURL('image/png');
			$('#ocsvgBtnExport').val(t('files_svgedit', 'Export PNG'));
			if(!ocsvg.exportFile.path.length) {
				var savePath = ocsvg.currentFile.path;
				if(savePath.substr(-4) == '.svg') {
					savePath = savePath.substr(0, savePath.length - 4);
				}
				ocsvg.exportFile.path = savePath + '.png';
			}
			ocsvg.exportFile.filecontents = datauri;
			ocsvg.saveFile({
				file: ocsvg.exportFile,
				base64encoded: true,
				base64type: 'image/png'
			});
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
    // set control buttons' onclick handlers:
    $('#ocsvgBtnSave').click(function() {
        svgCanvas.getSvgString()(ocsvg.save);
    });
	$('#ocsvgBtnExport').click(function() {
		svgCanvas.getSvgString()(ocsvg.pngExport);
	});
    $('#ocsvgBtnClose').click(function() {
		var dir = ocsvg.currentFile.path.replace(/\/[^\/]*$/, '');
		window.location = OC.linkTo('files', 'index.php') + '?dir=' + dir;
	});

    // import file
	ocsvg.init(ocsvgFile);
    
    // set editor's size fit into the window when resizing it:
    $(window).resize(function() {
        ocsvg.setEditorSize();
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
        //$('#ocsvgBtnPrefs').click(ocsvg.showPreferences);
        $('#ocsvgBtnPrefs').click(function() {
            ocsvg.frameDoc.find('#svg_prefs').toggle();
        });
        // set handler for preferences cancel button:
        ocsvg.frameDoc.find('#tool_prefs_cancel').click(function() {
            ocsvg.frameDoc.find('#svg_prefs').hide();
        });
        
        svgCanvas.setSvgString(ocsvg.currentFile.filecontents)(function(data, error) {
            if(error) {
                alert("Could not load file!\n\n" + error);
            }
			//svgCanvas.bind('changed', ocsvg.changedHandler)();
			//TODO: svgCanvas.bind doesn't work here as I expect...
			ocsvg.frameWin.svgCanvas.bind('changed', ocsvg.changedHandler);
        });

		// set confirmation on exit (only if content has changed);
		ocsvg.frameWin.onbeforeunload = ocsvg.confirmExit;
    });
});
