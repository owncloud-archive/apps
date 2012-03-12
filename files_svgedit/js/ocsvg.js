$(document).ready(function() {
	if(typeof FileActions!=='undefined'){
		FileActions.register('image/svg+xml','Edit','',function(filename){
            window.location = OC.filePath('files_svgedit', '', 'index.php')
                            + "?file=" + $('#dir').val() + "/" + filename;
		});
		FileActions.setDefault('image/svg+xml','Edit');
	}
});
