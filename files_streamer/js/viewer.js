function viewCommonVideo(dir, file) {
	bindView(file);
	playFlash(getUrl(dir, file));	
}

function viewWMV(dir, file) {
	bindView(file);
	playSilverLight(getUrl(dir, file));
}

var player;

function playSilverLight(url) {
	 player = new jeroenwijering.Player(
                        document.getElementById('container'),
                        'apps/files_streamer/player/wmvplayer.xaml',
                        {
                                file: url,
                                provider:'http',
                                'http.startparam':'starttime',
                                height: $('#nonebox').height(),
                                width: $('#nonebox').width()
                        }

                );

}

function playFlash(url) {
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

function getUrl(dir, file) {
	var port = window.location.port !== "" ? window.location.port : "80";
	return window.location.protocol+
                        "//"+document.domain+
                        ":"+port+
                        OC.filePath('files','ajax','download.php')+
                        '?files='+file+'&dir='+dir;
}

function bindView(file) {
	var container = '<div class="overlay" id="overlay" style="display:none;"></div><div class="nonebox" id="nonebox"><a class="box-close" id="box-close" href="#"></a><div id="container"></div>'+file+'</div>';
        $('#body-user').append(container);

        var shift = ($('#body-user').height()*0.2);

        $('#overlay').fadeIn('fast',function(){ // после клика запускаем наш ов$
            $('#nonebox').fadeIn('fast');
        });

        $(function() {
                $('#box-close').click(function(){ // кликаем по элементу которы$
                        closeVideo();
                });
        });
}

function closeVideo() {
	if (player) {
		player.sendEvent('STOP');
		player = null;
	}
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

function registerType(mime, func) {
	FileActions.register(
		mime,
                'View',
                '',
                func
        );
        FileActions.setDefault(mime, 'View');
}

$(document).ready(function() {	
	if (typeof FileActions !== 'undefined') {
		var mimeTypesCommon = new Array(
			'video/mp4',
			'video/x-flv'
		);
		var func = function(filename) {
                        viewCommonVideo($('#dir').val(),filename);
                };
		for (var i = 0; i < mimeTypesCommon.length; ++i) {
			var mime = mimeTypesCommon[i];
			registerType(mime, func);
		}
		var mimeTypesMS = new Array(
			'video/x-ms-asf'
		);
		var func = function(filename) {
			viewWMV($('#dir').val(),filename);
		}
		for (var i = 0; i < mimeTypesMS.length; ++i) {
			var mime = mimeTypesMS[i];
			registerType(mime, func);
		}
	}

});
