var svgCanvas = null;
var ocsvg = {
    frameDoc: null,
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
    }
};

$(document).ready(function() {
    // set control buttons' onclick handlers:
    $('#ocsvgBtnSave').click(function() {
        svgCanvas.getSvgString()(ocsvg.save);
    });
    
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
        svgCanvas = new embedded_svg_edit(frame);
        // hide main menu button, then shift the tool bar to the left border:
        ocsvg.frameDoc.find('#main_button').hide().next().css('left', 0).css('padding-left', 2).css('padding-top', 2);
        // fix broken color select field
        ocsvg.frameDoc.find('#fill_color,#stroke_color').find('svg').css('height', '100%');
        svgCanvas.setSvgString(ocsvg.currentFile.filecontents);
    });
});
/*
$(document).ready(function() {
    // Load specified file's contents into editor and set attributes:
    ocsvg.fileContents = ocsvgFile.contents;
    svgEditor.loadFromString(ocsvg.fileContents);
    ocsvg.setFilePath(ocsvgFile.path);
    ocsvg.setFileMTime(ocsvgFile.mtime);
    ocsvg.setEditorSize();
    $(window).resize(function() {
        ocsvg.setEditorSize();
    });
    // overwrite saveHandler:
    /*svgEditor.setConfig({
        saveHandler: ocsvg.save
    });*//*
    svgEditor.addExtension("OCSVG Handlers", function() {
        svgCanvas.bind('saved', ocsvg.save);
        return {};
    });
});
*/
