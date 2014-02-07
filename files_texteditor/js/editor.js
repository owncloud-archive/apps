function getFileExtension(file) {
	var parts = file.split('.');
	return parts[parts.length - 1];
}

function setSyntaxMode(ext) {
	// Loads the syntax mode files and tells the editor
	var filetype = new Array();
	// add file extensions like this: filetype["extension"] = "filetype":
	filetype["h"] = "c_cpp";
	filetype["c"] = "c_cpp";
	filetype["clj"] = "clojure";
	filetype["coffee"] = "coffee"; // coffescript can be compiled to javascript
	filetype["coldfusion"] = "cfc";
	filetype["cpp"] = "c_cpp";
	filetype["cs"] = "csharp";
	filetype["css"] = "css";
	filetype["groovy"] = "groovy";
	filetype["haxe"] = "hx";
	filetype["htm"] = "html";
	filetype["html"] = "html";
	filetype["java"] = "java";
	filetype["js"] = "javascript";
	filetype["jsm"] = "javascript";
	filetype["json"] = "json";
	filetype["latex"] = "latex";
	filetype["less"] = "less";
	filetype["ly"] = "latex";
	filetype["ily"] = "latex";
	filetype["lua"] = "lua";
	filetype["markdown"] = "markdown";
	filetype["md"] = "markdown";
	filetype["mdown"] = "markdown";
	filetype["mdwn"] = "markdown";
	filetype["mkd"] = "markdown";
	filetype["ml"] = "ocaml";
	filetype["mli"] = "ocaml";
	filetype["pl"] = "perl";
	filetype["php"] = "php";
	filetype["powershell"] = "ps1";
	filetype["py"] = "python";
	filetype["rb"] = "ruby";
	filetype["scad"] = "scad"; // seems to be something like 3d model files printed with e.g. reprap
	filetype["scala"] = "scala";
	filetype["scss"] = "scss"; // "sassy css"
	filetype["sh"] = "sh";
	filetype["sql"] = "sql";
	filetype["svg"] = "svg";
	filetype["textile"] = "textile"; // related to markdown
	filetype["xml"] = "xml";

	if (filetype[ext] != null) {
		// Then it must be in the array, so load the custom syntax mode
		// Set the syntax mode
		OC.addScript('files_texteditor', 'vendor/ace/src-noconflict/mode-' + filetype[ext], function () {
			var SyntaxMode = ace.require("ace/mode/" + filetype[ext]).Mode;
			window.aceEditor.getSession().setMode(new SyntaxMode());
		});
	}
}

function showControls(dir, filename, writeable) {
	// Loads the control bar at the top.
	OC.Breadcrumb.show(dir, filename, '#');
	// Load the new toolbar.
	var editorbarhtml = '<div id="editorcontrols" style="display: none;">';
	if (writeable) {
		editorbarhtml += '<button id="editor_save">' + t('files_texteditor', 'Save') + '</button>';
	}
	editorbarhtml += '<label for="editorseachval">' + t('files_texteditor', 'Search');
	editorbarhtml += '</label><input type="text" name="editorsearchval" id="editorsearchval">';
	editorbarhtml += '<button id="editor_close">';
	editorbarhtml += t('files_texteditor', 'Close') + '</button></div>';

	$('#controls').append(editorbarhtml);
	$('#editorcontrols').show();
}

function bindControlEvents() {
	$('#content').on('click', '#editor_save', doFileSave);
	$('#content').on('click', '#editor_close', hideFileEditor);
	$('#content').on('keyup', '#editorsearchval', doSearch);
	$('#content').on('click', '#clearsearchbtn', resetSearch);
	$('#content').on('click', '#nextsearchbtn', nextSearchResult);
}

// returns true or false if the editor is in view or not
function editorIsShown() {
	return is_editor_shown;
}

//resets the search
function resetSearch() {
	$('#editorsearchval').val('');
	$('#nextsearchbtn').remove();
	$('#clearsearchbtn').remove();
	window.aceEditor.gotoLine(0);
}

// moves the cursor to the next search resukt
function nextSearchResult() {
	window.aceEditor.findNext();
}
// Performs the initial search
function doSearch() {
	// check if search box empty?
	if ($('#editorsearchval').val() == '') {
		// Hide clear button
		window.aceEditor.gotoLine(0);
		$('#nextsearchbtn').remove();
		$('#clearsearchbtn').remove();
	} else {
		// New search
		// Reset cursor
		window.aceEditor.gotoLine(0);
		// Do search
		window.aceEditor.find($('#editorsearchval').val(), {
			backwards: false,
			wrap: false,
			caseSensitive: false,
			wholeWord: false,
			regExp: false
		});
		// Show next and clear buttons
		// check if already there
		if ($('#nextsearchbtn').length == 0) {
			var nextbtnhtml = '<button id="nextsearchbtn">' + t('files_texteditor', 'Next') + '</button>';
			var clearbtnhtml = '<button id="clearsearchbtn">' + t('files_texteditor', 'Clear') + '</button>';
			$('#editorsearchval').after(nextbtnhtml).after(clearbtnhtml);
		}
	}
}

// Tries to save the file.
function doFileSave() {
	if (editorIsShown()) {
		// Changed contents?
		if ($('#editor').attr('data-edited') == 'true') {
			$('#editor').attr('data-edited', 'false');
			$('#editor').attr('data-saving', 'true');
			// Get file path
			var path = $('#editor').attr('data-dir') + '/' + $('#editor').attr('data-filename');
			// Get original mtime
			var mtime = $('#editor').attr('data-mtime');
			// Show saving spinner
			$("#editor_save").die('click', doFileSave);
			$('#save_result').remove();
			$('#editor_save').text(t('files_texteditor', 'Saving...'));
			// Get the data
			var filecontents = window.aceEditor.getSession().getValue();
			// Send the data
			$.post(OC.filePath('files_texteditor', 'ajax', 'savefile.php'), { filecontents: filecontents, path: path, mtime: mtime }, function (jsondata) {
				if (jsondata.status != 'success') {
					// Save failed
					$('#editor_save').text(t('files_texteditor', 'Save'));
					$('#notification').html(t('files_texteditor', 'Failed to save file'));
					$('#notification').fadeIn();
					$('#editor').attr('data-edited', 'true');
					$('#editor').attr('data-saving', 'false');
				} else {
					// Save OK
					// Update mtime
					$('#editor').attr('data-mtime', jsondata.data.mtime);
					$('#editor_save').text(t('files_texteditor', 'Save'));
					// Update titles
					if($('#editor').attr('data-edited') != 'true') {
						$('.crumb.last a').text($('#editor').attr('data-filename'));
						document.title = $('#editor').attr('data-filename') + ' - ownCloud';
					}
					$('#editor').attr('data-saving', 'false');
				}
				$('#editor_save').live('click', doFileSave);
			}, 'json');
		}
	}
	giveEditorFocus();
};

// Gives the editor focus
function giveEditorFocus() {
	window.aceEditor.focus();
};

// Loads the file editor. Accepts two parameters, dir and filename.
function showFileEditor(dir, filename) {
	// Check if unsupported file format
	if(FileActions.getCurrentMimeType() === 'text/rtf') {
		// Download the file instead.
		window.location = OC.filePath('files', 'ajax', 'download.php') + '?files=' + encodeURIComponent(filename) + '&dir=' + encodeURIComponent($('#dir').val());
	} else {
		if (!editorIsShown()) {
			is_editor_shown = true;
			// Delete any old editors
			if ($('#notification').data('reopeneditor')) {
				OC.Notification.hide();
			}
			$('#editor').remove();
			// Loads the file editor and display it.
			$('#content').append('<div id="editor_container"><div id="editor"></div></div>');

			// bigger text for better readability
			document.getElementById('editor').style.fontSize = '16px';

			var data = $.getJSON(
				OC.filePath('files_texteditor', 'ajax', 'loadfile.php'),
				{file: filename, dir: dir},
				function (result) {
					if (result.status === 'success') {
						// Save mtime
						$('#editor').attr('data-mtime', result.data.mtime);
						// Initialise the editor
						if (window.FileList){
							FileList.setViewerMode(true);
							enableEditorUnsavedWarning(true);
							$('#fileList').on('changeDirectory.texteditor', textEditorOnChangeDirectory);
						}
						// Show the control bar
						showControls(dir, filename, result.data.writeable);
						// Update document title
						$('body').attr('old_title', document.title);
						document.title = filename + ' - ownCloud';
						$('#editor').text(result.data.filecontents);
						$('#editor').attr('data-dir', dir);
						$('#editor').attr('data-filename', filename);
						$('#editor').attr('data-edited', 'false');
						window.aceEditor = ace.edit("editor");
						aceEditor.setShowPrintMargin(false);
						aceEditor.getSession().setUseWrapMode(true);
						if ( ! result.data.writeable ) {
							aceEditor.setReadOnly(true);
						}
						if (result.data.mime && result.data.mime === 'text/html') {
							setSyntaxMode('html');
						} else {
							setSyntaxMode(getFileExtension(filename));
						}
						OC.addScript('files_texteditor', 'vendor/ace/src-noconflict/theme-clouds', function () {
							window.aceEditor.setTheme("ace/theme/clouds");
						});
						window.aceEditor.getSession().on('change', function () {
							if ($('#editor').attr('data-edited') != 'true') {
								$('#editor').attr('data-edited', 'true');
								if($('#editor').attr('data-saving') != 'true') {
									$('.crumb.last a').text($('.crumb.last a').text() + ' *');
									document.title = $('#editor').attr('data-filename') + ' * - ownCloud';
								}
							}
						});
						// Add the ctrl+s event
						window.aceEditor.commands.addCommand({
							name: "save",
							bindKey: {
								win: "Ctrl-S",
								mac: "Command-S",
								sender: "editor"
							},
							exec: function () {
								doFileSave();
							}
						});
						giveEditorFocus();
					} else {
						// Failed to get the file.
						OC.dialogs.alert(result.data.message, t('files_texteditor', 'An error occurred!'));
					}
					// End success
				}
				// End ajax
			);
			return data;
		}
	}
}

function enableEditorUnsavedWarning(enable) {
	$(window).unbind('beforeunload.texteditor');
	if (enable) {
		$(window).bind('beforeunload.texteditor', function () {
			if ($('#editor').attr('data-edited') == 'true') {
				return t('files_texteditor', 'There are unsaved changes in the text editor');
			}
		});
	}
}

function textEditorOnChangeDirectory(ev){
	// if the directory is changed, it is usually due to browser back
	// navigation. In this case, simply close the editor
	hideFileEditor();
}

// Fades out the editor.
function hideFileEditor() {
	$('#fileList').off('changeDirectory.texteditor');
	enableEditorUnsavedWarning(false);
	if (window.FileList){
		// reload the directory content with the updated file size + thumbnail
		// and also the breadcrumb
		window.FileList.reload();
	}
	if ($('#editor').attr('data-edited') == 'true') {
		// Hide, not remove
		$('#editorcontrols,#editor_container').hide();
		// Fade out editor
		// Reset document title
		document.title = $('body').attr('old_title');
		FileList.setViewerMode(false);
		$('#content table').show();
		OC.Notification.show(t('files_texteditor', 'There were unsaved changes, click here to go back'));
		$('#notification').data('reopeneditor', true);
		is_editor_shown = false;
	} else {
		// Fade out editor
		$('#editor_container, #editorcontrols').remove();
		// Reset document title
		document.title = $('body').attr('old_title');
		FileList.setViewerMode(false);
		$('#content table').show();
		is_editor_shown = false;
	}
}

// Reopens the last document
function reopenEditor() {
	FileList.setViewerMode(true);
	enableEditorUnsavedWarning(true);
	$('#fileList').on('changeDirectory.texteditor', textEditorOnChangeDirectory);
	$('#controls .last').not('#breadcrumb_file').removeClass('last');
	$('#editor_container').show();
	$('#editorcontrols').show();
	OC.Breadcrumb.show($('#editor').attr('data-dir'), $('#editor').attr('data-filename') + ' *', '#');
	document.title = $('#editor').attr('data-filename') + ' * - ownCloud';
	is_editor_shown = true;
	giveEditorFocus();
}

var is_editor_shown = false;
$(document).ready(function () {
	if ($('#isPublic').val()){
		// disable editor in public mode (not supported yet)
		return;
	}
	if (typeof FileActions !== 'undefined') {
		FileActions.register('text', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('text', 'Edit');
		FileActions.register('application/xml', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('application/xml', 'Edit');
		FileActions.register('application/x-empty', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('application/x-empty', 'Edit');
		FileActions.register('inode/x-empty', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('inode/x-empty', 'Edit');
		FileActions.register('application/x-php', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('application/x-php', 'Edit');
		FileActions.register('application/javascript', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('application/javascript', 'Edit');
		FileActions.register('application/x-pearl', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename);
		});
		FileActions.setDefault('application/x-pearl', 'Edit');

	}
	
	//legacy search result customization
	OC.search.customResults.Text = function (row, item) {
		var text = item.link.substr(item.link.indexOf('download') + 8);
		var a = row.find('td.result a');
		a.data('file', text);
		a.attr('href', '#');
		a.click(function () {
			text = decodeURIComponent(text);
			var pos = text.lastIndexOf('/');
			var file = text.substr(pos + 1);
			var dir = text.substr(0, pos);
			showFileEditor(dir, file);
		});
	};
	// customize file results when we can edit them
	OC.search.customResults.file = function (row, item) {
		var validFile = /(text\/*|application\/xml)/;
		if (validFile.test(item.mime_type)) {
			var a = row.find('td.result a');
			a.data('file', item.name);
			a.attr('href', '#');
			a.click(function () {
				showFileEditor(OC.dirname(item.path), item.name);
			});
		}
	};
	// Binds the file save and close editor events, and gotoline button
	bindControlEvents();
	$('#editor_container').remove();
	$('#notification').click(function () {
		if ($('#notification').data('reopeneditor')) {
			reopenEditor();
			OC.Notification.hide();
		}
	});
});
