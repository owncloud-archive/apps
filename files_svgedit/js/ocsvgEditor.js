var ocsvg = {
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
    save: function(win, svgString) {
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
                        ocsvg.save(win, svgString);
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
    });*/
    svgEditor.addExtension("OCSVG Handlers", function() {
        svgCanvas.bind('saved', ocsvg.save);
        return {};
    });
});
