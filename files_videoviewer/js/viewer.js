var streamerPlayer = {
	UI : {
		playerTemplate : '<video width="%width%" height="%height%" id="media_element" class="video-js vjs-default-skin" poster="" controls preload="auto">' + 
		'<source type="video/mp4" src="%src%" />' + 
		'<source type="video/webm" src="%src%"  />' +
		'<source type="video/flv" src="%src%"  />' +
		'<object width="%width%" height="%height%" type="application/x-shockwave-flash" data="%flash%">' +
		'<param name="movie" value="%flash%" />' +
		'<param name="flashvars" value="controls=true&amp;file=%src%" />' +
		'</object>' +
		'</video>',
		show : function (file, location, flashUri) {
			$('<div class="overlay" id="overlay" style="display:none;"></div><div id="nonebox"><div id="container"><a class="box-close" id="box-close" href="#"></a><h3>'+file+'</h3></div></div>').appendTo('body');
			
			$('#overlay').fadeIn('fast',function(){
				$('#nonebox').fadeIn('fast');
			});
			$('#box-close').click(streamerPlayer.hidePlayer);
			var size = streamerPlayer.UI.getSize();
			var playerView = streamerPlayer.UI.playerTemplate.replace(/%width%/g, size.width)
								.replace(/%height%/g, size.height)
								.replace(/%flash%/g, flashUri)
								.replace(/%src%/g, location)
			;
			$(playerView).prependTo('#container');
		},
		hide : function() {
			$(".mejs-container").remove();
			$('#nonebox').fadeOut('fast', function() {
				$('#overlay').fadeOut('fast', function() {
					$('#overlay').remove();
				});
				$('#nonebox').remove();
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
	file : null,
	player : null,
	mimeTypes : [
		'video/mp4',
		'video/webm',
		'video/x-flv',
		'application/ogg',
		'application/octet-stream',
		'video/quicktime',
		'video/x-msvideo',
		'video/x-matroska',
		'video/x-ms-asf'
	],
	showPlayer : function(){
		var location = streamerPlayer.getMediaUrl(streamerPlayer.file);
		var mime = FileActions.getCurrentMimeType();
		
		//Previous instance should NOT exist
		streamerPlayer.player = false;
		delete streamerPlayer.player;
		
		streamerPlayer.UI.show(streamerPlayer.file, location,  OC.filePath('files_videoviewer', 'js', 'flashmediaelement.swf'));
	
		streamerPlayer.player = new MediaElementPlayer('#media_element', {
			features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
			pluginPath : OC.filePath('files_videoviewer', 'js', ''),
			pauseOtherPlayers: false,
			enablePluginDebug: true,
			plugins: ['flash','silverlight'],
			success: function (player, node) {
				//set the size (for flash otherwise no video just sound!)
				player.setVideoSize($(node).width(), $(node).height());
				streamerPlayer.log(location);
				player.load();
				player.pause();
				streamerPlayer.log('ready');
			},
			error: function (m) { 
				console.log(m);
			}
		});
	},
	onView : function(file) {
		streamerPlayer.file = file;
		OC.addScript('files_videoviewer','mediaelement-and-player').done(streamerPlayer.showPlayer);
	},
	hidePlayer : function() {
		streamerPlayer.UI.hide();
	},
	getMediaUrl : function(file) {
		var dir = $('#dir').val();
		return 	OC.filePath('files','ajax','download.php')+
			encodeURIComponent('?dir='+ encodeURIComponent(dir) + '&files='+encodeURIComponent(file));
	},
	onKeyDown : function(e) {
		if (e.keyCode == 27 && !$('.mejs-container-fullscreen').length && streamerPlayer.player) {
			 streamerPlayer.hidePlayer();
		}
	},
	log : function(message){
		console.log(message);
	}
};

$(document).ready(function() {	
	if (typeof FileActions !== 'undefined') {
		for (var i = 0; i < streamerPlayer.mimeTypes.length; ++i) {
			var mime = streamerPlayer.mimeTypes[i];
			FileActions.register(mime, 'View', OC.PERMISSION_READ, '', streamerPlayer.onView);
			FileActions.setDefault(mime, 'View');
		}
		$(document).keydown(streamerPlayer.onKeyDown);
	}
});
