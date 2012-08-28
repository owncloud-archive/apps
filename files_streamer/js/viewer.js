var streamerPlayer = {
	UI : {
		show : function (file){
			var container = '<div class="overlay" id="overlay" style="display:none;"></div><div id="nonebox"><div id="container"><a class="box-close" id="box-close" href="#"></a>'+file+'</div></div>';
			$('body').append(container);
			
			$('#overlay').fadeIn('fast',function(){
				$('#nonebox').fadeIn('fast');
			});
			$('#box-close').click(streamerPlayer.hidePlayer);
			var size = streamerPlayer.UI.getSize();
			$('<video width="' + size.width + '" height="' + size.height + '" id="media_element" controls="controls" preload="none"></video>').prependTo('#container');
		},
		hide : function(){
			$('#nonebox').fadeOut('fast', function(){
				$('#nonebox').remove();
				$('#overlay').fadeOut('fast', function() {
					$('#overlay').remove();
				});
			});
		},
		getSize : function (){
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
	showPlayer : function(dir, file) {
		streamerPlayer.UI.show(file);
		
		var location = streamerPlayer.getMediaUrl(dir, file);
		var mime = FileActions.getCurrentMimeType();
		streamerPlayer.addSource(mime, location);
		
		//someFallbacks
		streamerPlayer.addSource('video/x-flv', location);
		streamerPlayer.addSource('video/x-ms-asf', location);
		
		$('video').mediaelementplayer('#media_element', { 
			features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
			pluginPath : OC.filePath('files_streamer', 'js', ''),
			enablePluginDebug: false,
			plugins: ['flash','silverlight']
		});
		streamerPlayer.isVisible = true;
	},
	hidePlayer : function() {
		streamerPlayer.UI.hide();
		streamerPlayer.isVisible = false;
	},
	addSource : function(mime, location) {
		$('<source type="' + mime + '" src="' + location + '" />').appendTo('#media_element');
	},
	getMediaUrl : function(dir, file) {
		var port = window.location.port !== "" ? window.location.port : "80";
		return window.location.protocol+
			"//"+document.domain+
			":"+port+
			OC.filePath('files','ajax','download.php')+
			'?files='+file+'&dir='+dir;
	},
	onKeyDown : function(e) {
		if (e.keyCode == 27 && !$('.mejs-container-fullscreen').length && streamerPlayer && streamerPlayer.isVisible) {
			 streamerPlayer.hidePlayer();
		}
	}
};

function registerType(mime, func) {
	FileActions.register(mime, 'View', '', func);
        FileActions.setDefault(mime, 'View');
}

$(document).ready(function() {	
	if (typeof FileActions !== 'undefined') {
		var mimeTypesCommon = new Array(
			'video/mp4',
			'video/webm',
			'video/x-flv',
			'application/ogg',
			'video/quicktime',
			'video/x-msvideo',
			'video/x-ms-asf'
		);
		var func = function(filename) {
			streamerPlayer.showPlayer($('#dir').val(), filename);
                };
		for (var i = 0; i < mimeTypesCommon.length; ++i) {
			var mime = mimeTypesCommon[i];
			registerType(mime, func);
		}
		$(document).keydown(streamerPlayer.onKeyDown);
	}
});
