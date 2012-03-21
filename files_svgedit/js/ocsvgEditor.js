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
    setFileMTime: function(mtime) {
        // set last modified time of the file
        this.currentFile.mtime = mtime;
    },
    save: function() {
        var savePath = prompt(t('files_svgedit', 'Save as'), this.currentFile.path);
        if(savePath === null || savePath == '') {
            return;
        } else {
            this.currentFile.path = savePath;
            this.currentFile.filecontents = svgCanvas.getSvgString();
            $.post(
                OC.filePath('files_svgedit','ajax','save.php'),
                this.currentFile,
                function(result) {
                    if(result.status!='success'){
                        // Save failed
                        alert(t('files_svgedit', 'Could not save:') + "\n" + ocsvg.currentFile.path);
                    } else {
                        // Save OK
                        // Update mtime:
                        ocsvg.currentFile.mtime = result.data.mtime;
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
});
