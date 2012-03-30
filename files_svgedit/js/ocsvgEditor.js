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
    setEditorSize: function() {
        // Fits the size of editor area to the available space
        fillWindow($('#editorWrapper'));
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
    save: function(svgString, error) {
        if(error) {
            alert("Couldn't get SVG contents:\n\n" + error);
            return;
        }
        var savePath = prompt(t('files_svgedit', 'Save as'), ocsvg.currentFile.path);
        if(savePath === null || savePath == '') {
            return;
        } else {
            ocsvg.setFilePath(savePath);
            ocsvg.setFileContents('<?xml version="1.0" encoding="UTF-8" standalone="no"?>\n' + svgString);
            $.post(
                OC.filePath('files_svgedit','ajax','save.php'),
                ocsvg.currentFile,
                function(result) {
                    if(result.status!='success'){
                        // Save failed
                        alert(t('files_svgedit', 'Could not save:') + "\n" + ocsvg.currentFile.path + "\n" + result.data.message);
                        ocsvg.save(svgString, null);
                    } else {
                        // Save OK
                        // Update mtime:
                        ocsvg.currentFile.mtime = result.data.mtime;
                        alert(t('files_svgedit', 'Successfully saved!'));
                    }
                },
                'json'
            );
        }
    }/*,
    showPreferences: function() {
        if (ocsvg.prefsShown) return;
        ocsvg.prefsShown = true;
        
        // Update background color with current one
        var blocks = ocsvg.frameDoc.find('#bg_blocks div');
        var cur_bg = 'cur_background';
        var canvas_bg = ocsvg.frameWin.$.pref('bkgd_color');
        var url = ocsvg.frameWin.$.pref('bkgd_url');
// 		if(url) url = url[1];
        blocks.each(function() {
            var blk = $(this);
            var is_bg = blk.css('background-color') == canvas_bg;
            blk.toggleClass(cur_bg, is_bg);
            if(is_bg) $(ocsvg.frameDoc).find('#canvas_bg_url').removeClass(cur_bg);
        });
        if(!canvas_bg) blocks.eq(0).addClass(cur_bg);
        if(url) {
            ocsvg.frameDoc.find('#canvas_bg_url').val(url);
        }
        ocsvg.frameDoc.find('grid_snapping_step').attr('value', ocsvg.frameWin.svgEditor.curConfig.snappingStep);
        if (ocsvg.frameWin.svgEditor.curConfig.gridSnapping == true) {
            ocsvg.frameDoc.find('#grid_snapping_on').attr('checked', 'checked');
        } else {
            ocsvg.frameDoc.find('#grid_snapping_on').removeAttr('checked');
        }
        
        ocsvg.frameDoc.find('#svg_prefs').show();
    }*/
};

$(document).ready(function() {
    /*
    // set control buttons' onclick handlers:
    $('#ocsvgBtnSave').click(function() {
        svgCanvas.getSvgString()(ocsvg.save);
    });
    */
    // import file
    ocsvg.setFileContents(ocsvgFile.contents);
    ocsvg.setFilePath(ocsvgFile.path);
    ocsvg.setFileMTime(ocsvgFile.mtime);
    
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
        //ocsvg.frameDoc.find('#main_button').hide().next().css('left', 0).css('padding-left', 2).css('padding-top', 2);
        // fix broken color select field
        ocsvg.frameDoc.find('#fill_color,#stroke_color').find('svg').css('height', '100%');
        /*
        // set handler for show preferences button:
        //$('#ocsvgBtnPrefs').click(ocsvg.showPreferences);
        $('#ocsvgBtnPrefs').click(function() {
            ocsvg.frameDoc.find('#svg_prefs').toggle();
        });
        // set handler for preferences cancel button:
        ocsvg.frameDoc.find('#tool_prefs_cancel').click(function() {
            ocsvg.frameDoc.find('#svg_prefs').hide();
        });
        */
        svgCanvas.setSvgString(ocsvg.currentFile.filecontents)(function(data, error) {
            if(error) {
                alert("Could not load file!\n\n" + error);
            }
        });
    });
});
