var streamerPlayer = {
	UI : {
		show : function (file) {
			$('<div class="overlay" id="overlay" style="display:none;"></div><div id="nonebox"><div id="container"><a class="box-close" id="box-close" href="#"></a><h3>'+file+'</h3></div></div>').appendTo('body');
			
			$('#overlay').fadeIn('fast',function(){
				$('#nonebox').fadeIn('fast');
			});
			$('#box-close').click(streamerPlayer.hidePlayer);
			var size = streamerPlayer.UI.getSize();
			$('<video width="' + size.width + '" height="' + size.height + '" id="media_element" controls="controls" ></video>').prependTo('#container');
		},
		hide : function() {
			$('#nonebox').fadeOut('fast', function() {
				$('#nonebox').remove();
				$('#overlay').fadeOut('fast', function() {
					$('#overlay').remove();
				});
			});
		},
		getSize : function () {
			var size;
			if ($(document).width()>'680' && $(document).height()>'520' ){
				size = {width: 640, height: 480};
			} else {
				size = {width: 320, height: 240};
			}
			return size;
		},
	},
	isVisible : false,
	mimeTypes : [
		'video/mp4',
		'video/webm',
		'video/x-flv',
		'application/ogg',
		'video/quicktime',
		'video/x-msvideo',
		'video/x-ms-asf'
	],
	onView : function(file) {
		streamerPlayer.UI.show(file);
		var location = streamerPlayer.getMediaUrl(file);
		var mime = FileActions.getCurrentMimeType();
		streamerPlayer.addSource(mime, location);
		
		//some Fallbacks
		streamerPlayer.addSource('video/x-flv', location);
		streamerPlayer.addSource('video/x-ms-asf', location);
		
		streamerPlayer.player = $('video#media_element').mediaelementplayer('#media_element', {
			features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
			pluginPath : OC.filePath('files_videoviewer', 'js', ''),
			enablePluginDebug: false,
			plugins: ['flash','silverlight']
		});

		streamerPlayer.isVisible = true;
	},
	hidePlayer : function() {
		streamerPlayer.isVisible = false;
		streamerPlayer.player=null;
		streamerPlayer.UI.hide();
	},
	addSource : function(mime, location) {
		$('<source type="' + mime + '" src="' + location + '" />').appendTo('#media_element');
	},
	getMediaUrl : function(file) {
		var dir = $('#dir').val();
		var port = window.location.port !== "" ? window.location.port : "80";
		return window.location.protocol+
			"//"+document.domain+
			":"+port+
			OC.filePath('files','ajax','download.php')+
			'?files='+file+'&dir='+dir;
	},
	onKeyDown : function(e) {
		if (e.keyCode == 27 && !$('.mejs-container-fullscreen').length && streamerPlayer.isVisible) {
			 streamerPlayer.hidePlayer();
		}
	}
};

$(document).ready(function() {	
	if (typeof FileActions !== 'undefined') {
		for (var i = 0; i < streamerPlayer.mimeTypes.length; ++i) {
			var mime = streamerPlayer.mimeTypes[i];
			FileActions.register(mime, 'View', FileActions.PERMISSION_READ, '', streamerPlayer.onView);
			FileActions.setDefault(mime, 'View');
		}
		$(document).keydown(streamerPlayer.onKeyDown);
	}
});
