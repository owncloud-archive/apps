var videoViewer = {
	UI : {
		playerTemplate : '<video width="%width%" height="%height%" id="media_element" class="video-js vjs-default-skin" controls preload="none">' + 
		'<source type="%type%" src="%src%" />' + 
		'</video>',
		show : function () {
			$('<div id="videoviewer_overlay" style="display:none;"></div><div id="videoviewer_popup"><div id="videoviewer_container"><a class="box-close" id="box-close" href="#"></a><h3>'+videoViewer.file+'</h3></div></div>').appendTo('body');
			
			$('#videoviewer_overlay').fadeIn('fast',function(){
				$('#videoviewer_popup').fadeIn('fast');
			});
			$('#box-close').click(videoViewer.hidePlayer);
			var size = videoViewer.UI.getSize();
			var playerView = videoViewer.UI.playerTemplate.replace(/%width%/g, size.width)
								.replace(/%height%/g, size.height)
								.replace(/%type%/g, videoViewer.mime)
								.replace(/%src%/g, videoViewer.location)
			;
			$(playerView).prependTo('#videoviewer_container');
		},
		hide : function() {
			$('#videoviewer_popup').fadeOut('fast', function() {
				$('#videoviewer_overlay').fadeOut('fast', function() {
					$('#videoviewer_popup').remove();
					$('#videoviewer_overlay').remove();
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
		'video/ogg',
		'video/quicktime',
		'video/x-msvideo',
		'video/x-matroska',
		'video/x-ms-asf'
	],
	onView : function(file) {
		videoViewer.file = file;
		videoViewer.location = videoViewer.getMediaUrl(file);
		videoViewer.mime = FileActions.getCurrentMimeType();
		
		OC.addScript('files_videoviewer','mediaelement-and-player', function(){
			OC.addScript('files_videoviewer','mep-extra', videoViewer.showPlayer);
		});
	},
	showPlayer : function() {
		videoViewer.UI.show();
	
		videoViewer.player = new MediaElementPlayer('#media_element', {
			features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
			pluginPath : OC.filePath('files_videoviewer', 'js', ''),
			pauseOtherPlayers: false,
			enablePluginDebug: false,
			plugins: ['flash','silverlight'],
			success: function (player, node) {
				//set the size (for flash otherwise no video just sound!)
				player.setVideoSize($(node).width(), $(node).height());
				player.load();
				player.pause();
			},
			error: function (m) { 
				console.log(m);
			}
		});
	},
	hidePlayer : function() {
		videoViewer.player = false;
		delete videoViewer.player;

		videoViewer.UI.hide();
	},
	getMediaUrl : function(file) {
		var dir = $('#dir').val();
		return fileDownloadPath(dir, file);
	},
	onKeyDown : function(e) {
		if (e.keyCode == 27 && !$('.mejs-container-fullscreen').length && videoViewer.player) {
			 videoViewer.hidePlayer();
		}
	},
	log : function(message){
		console.log(message);
	}
};

$(document).ready(function() {	
	if (typeof FileActions !== 'undefined') {
		for (var i = 0; i < videoViewer.mimeTypes.length; ++i) {
			var mime = videoViewer.mimeTypes[i];
			FileActions.register(mime, 'View', OC.PERMISSION_READ, '', videoViewer.onView);
			FileActions.setDefault(mime, 'View');
		}
		$(document).keydown(videoViewer.onKeyDown);
	}
});
