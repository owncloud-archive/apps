function hideDOCviewer() {
	$('#content table').show();
	$("#controls").show();
	$("#editor").show();
	$('iframe').remove();
	$('a.action').remove();
}

showPreview.oldCode='';
showPreview.lastTitle='';

function showPreview(dir,filename){
	if(!showPreview.shown){
		$("#editor").hide();
		$('#content table').hide();
		$("#controls").hide();
		var oldcontent = $("#content").html();
		//var viewer = getRequestURL(dir, filename, '.html');
		var viewer = getFilePath(dir, filename);
		//var viewer = OC.linkTo('file_previewer', 'docViewer.php')+'?dir='+encodeURIComponent(dir).replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'));
		$("#content").html(oldcontent+'<iframe style="width:100%;height:100%;display:block;" src="'+viewer+'" />');
		$("#pageWidthOption").attr("selected","selected");
	}
}

function getFilePath(dir, filename) {
	dir = encodeURIComponent(dir).replace(/%2F/g, '/');
	filename = encodeURIComponent(filename.replace('&', '%26'));
	var baseUrl = '';
	if(dir === '/'){
		baseUrl = dir + filename;	
	}
	else{
		baseUrl = dir + '/' + filename;
	}
	var viewer = OC.Router.generate('previewer', { link: baseUrl});
	return viewer;
}

function getRequestURL(dir, filename, type) {
	dir = encodeURIComponent(dir).replace(/%2F/g, '/');
	filename = encodeURIComponent(filename.replace('&', '%26'));
	var baseUrl = '';
	if(dir === '/'){
		baseUrl = dir + filename + '/';	
	}
	else{
		baseUrl = dir + '/' + filename + '/';
	}
	var idx = filename.lastIndexOf(".");
	var url = baseUrl + filename.slice(0, idx) + type;
	var viewer = OC.Router.generate('previewer', { link: url});
	return viewer;
}

$(document).ready(function() {
	if(!$.browser.msie){//doesn't work on IE
		
		if(location.href.indexOf("files")!=-1) {
			/*if(typeof FileActions!=='undefined'){
				FileActions.register('application/msword','Prev', OC.PERMISSION_READ, '',function(filename) {
					showPreview($('#dir').val(),filename);
				});
				FileActions.setDefault('application/msword','Prev');
			}*/
			if(typeof FileActions!=='undefined'){

				var supportedMimes = new Array(
					'application/msword',
					'application/msexcel',
					'application/mspowerpoint',
					'application/vnd.oasis.opendocument.text', 
					'application/vnd.oasis.opendocument.spreadsheet',
					'application/vnd.oasis.opendocument.graphics',
					'application/vnd.oasis.opendocument.presentation');
				for (var i = 0; i < supportedMimes.length; ++i){
					var mime = supportedMimes[i];
					FileActions.register(mime,'Prev',OC.PERMISSION_READ,'',function(filename){
						showPreview($('#dir').val(),filename);
					});
					FileActions.setDefault(mime,'Prev');
				}
			}
		}
		
		/*if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('application/msexcel','Prev', OC.PERMISSION_READ, '',function(filename) {
					showPreview($('#dir').val(),filename);
				});
				FileActions.setDefault('application/msexcel','Prev');
			}
		}
		
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined'){
				FileActions.register('application/mspowerpoint','Prev', OC.PERMISSION_READ, '',function(filename) {
					showPreview($('#dir').val(),filename);
				});
				FileActions.setDefault('application/mspowerpoint','Prev');
			}
		}*/
		
		if(location.href.indexOf("files")!=-1) {
			if(typeof FileActions!=='undefined') {
				FileActions.register('application/msword','ePub', OC.PERMISSION_READ, '',function(filename) {
					//window.location = OC.linkTo('file_previewer', 'docViewer.php')+'?dir='+encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'&file='+encodeURIComponent(filename.replace('&', '%26'))+'&type=epub';
					window.location = getRequestURL($('#dir').val(), filename, '.epub');
				});
			}
		}
		
		
	}
});
