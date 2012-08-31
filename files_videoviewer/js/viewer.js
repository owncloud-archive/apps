var streamerPlayer = {
	UI : {
		playerTemplate : '<video width="%width%" height="%height%" id="media_element" class="video-js vjs-default-skin" controls preload="none">' + 
		'<source type="%type%" src="%src%" />' + 
		'</video>',
		init : function(){
			OC.addScript('files_videoviewer','mediaelement-and-player', streamerPlayer.showPlayer);
		},
		show : function () {
			$('<div id="videoviewer_overlay" style="display:none;"></div><div id="videoviewer_popup"><div id="videoviewer_container"><a class="box-close" id="box-close" href="#"></a><h3>'+streamerPlayer.file+'</h3></div></div>').appendTo('body');
			
			$('#videoviewer_overlay').fadeIn('fast',function(){
				$('#videoviewer_popup').fadeIn('fast');
			});
			$('#box-close').click(streamerPlayer.hidePlayer);
			var size = streamerPlayer.UI.getSize();
			var playerView = streamerPlayer.UI.playerTemplate.replace(/%width%/g, size.width)
								.replace(/%height%/g, size.height)
								.replace(/%type%/g, streamerPlayer.mime)
								.replace(/%src%/g, streamerPlayer.location)
			;
			$(playerView).prependTo('#videoviewer_container');
		},
		hide : function() {
			$('#videoviewer_popup').fadeOut('fast', function() {
				$('#videoviewer_overlay').fadeOut('fast', function() {
					$('#videoviewer_overlay').remove();
					$('#videoviewer_popup').remove();
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
	mime : null,
	file : null,
	location : null,
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
	onView : function(file) {
		streamerPlayer.file = file;
		streamerPlayer.location = streamerPlayer.getMediaUrl(file);
		streamerPlayer.mime = FileActions.getCurrentMimeType();
		
		OC.addScript('files_videoviewer','mediaelement-and-player', streamerPlayer.showPlayer);
	},
	showPlayer : function() {
		streamerPlayer.UI.show();
	
		streamerPlayer.player = new MediaElementPlayer('#media_element', {
			features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
			pluginPath : OC.filePath('files_videoviewer', 'js', ''),
			pauseOtherPlayers: false,
			enablePluginDebug: true,
			plugins: ['flash','silverlight'],
			success: function (player, node) {
				//set the size (for flash otherwise no video just sound!)
				player.setVideoSize($(node).width(), $(node).height());
				streamerPlayer.log(streamerPlayer.location);
				player.load();
				player.pause();
				streamerPlayer.log('ready');
			},
			error: function (m) { 
				console.log(m);
			}
		});
	},
	hidePlayer : function() {
		streamerPlayer.player && streamerPlayer.pause();
		streamerPlayer.player = false;
		delete streamerPlayer.player;

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
