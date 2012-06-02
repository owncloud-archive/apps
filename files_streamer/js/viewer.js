function viewVideo(dir, file) {
	var port = window.location.port !== "" ? window.location.port : "80";
	var url = window.location.protocol+
			"//"+document.domain+
			":"+port+ 
			OC.filePath('files','ajax','download.php')+
			'?files='+file+'&dir='+dir;
	
	var container = '<div class="overlay" id="overlay" style="display:none;"></div><div class="nonebox" id="nonebox"><a class="box-close" id="box-close" href="#"></a><div id="container"></div>'+file+'</div>';
	$('#body-user').append(container);
	
	var shift = ($('#body-user').height()*0.2);

	$('#overlay').fadeIn('fast',function(){ // после клика запускаем наш ов$
            $('#nonebox').fadeIn('fast');
        });

	$(function() {
		$('#box-close').click(function(){ // кликаем по элементу который всё это бу$
        		closeVideo();
    		});
	});

	jwplayer('container').setup({
		flashplayer:'apps/files_streamer/player/player.swf',
		file: url,
		height: $('#nonebox').height(),
		width: $('#nonebox').width(),
		provider:'http',
		'http.startparam':'starttime',		
		skin:'apps/files_streamer/player/skin.zip'
	});
}

function closeVideo() {
	$('#nonebox').fadeOut('fast', function(){ // убираем на$
        	$('#nonebox').remove();
                $('#overlay').fadeOut('fast', function() {
                	$('#overlay').remove();
                }); // и теперь убираем оверлэй
        });
}

$(document).keyup(function(e) {
	if (e.keyCode == 27) {
		// closeVideo();
	}
});

$(document).ready(function() {	
	if (typeof FileActions !== 'undefined') {
		var mimeTypes = new Array(
			'video/mp4',
			'video/x-flv'
		);
		for (var i = 0; i < mimeTypes.length; ++i) {
			var mime = mimeTypes[i];
			FileActions.register(
				mime, 
				'View', 
				'',
				function(filename) {
					viewVideo($('#dir').val(),filename);
				}
			);
			FileActions.setDefault(mime, 'View');
		}
	}

});

