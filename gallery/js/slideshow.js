jQuery.fn.slideShow = function (container, start, options) {
	var i, images = [], settings;
	start = start || 0;
	settings = jQuery.extend({
		'interval': 5000,
		'play'    : false,
		'maxScale': 2
	}, options);
	if (settings.play){
		$('#slideshow').children('.play').hide();
		$('#slideshow').children('.pause').show();
	}
	else{
		$('#slideshow').children('.play').show();
		$('#slideshow').children('.pause').hide();
	}
	jQuery.fn.slideShow.container = container;
	jQuery.fn.slideShow.settings = settings;
	jQuery.fn.slideShow.current = start;
	for (i = 0; i < this.length; i++) {
		var imageLink = this[i];
		images.push(imageLink.imageUrl || imageLink.href);
	}
	container.children('img').remove();
	container.show();
	jQuery.fn.slideShow.images = images;
	jQuery.fn.slideShow.cache = [];
	jQuery.fn.slideShow.showImage(images[start], images[start + 1]);
	jQuery.fn.slideShow.progressBar = container.find('.progress');

	// hide arrows and play/pause when only one pic
	$('#slideshow').find('.next, .previous').toggle(images.length > 1);
	if (images.length === 1) {
		// note: only handling hide case here as we don't want to
		// re-show the buttons that might have been hidden by
		// the settings.play condition above
		$('#slideshow').find('.play, .pause').hide();
	}

	jQuery(window).resize(function () {
		jQuery.fn.slideShow.loadImage(jQuery.fn.slideShow.images[jQuery.fn.slideShow.current]).then(function (image) {
			jQuery.fn.slideShow.fitImage(container, image);
		});
	});
	return jQuery.fn.slideShow;
};

jQuery.fn.slideShow.progressBar = null;

jQuery.fn.slideShow.loadImage = function (url) {
	if (!jQuery.fn.slideShow.cache[url]) {
		jQuery.fn.slideShow.cache[url] = new jQuery.Deferred();
		var image = new Image();
		jQuery.fn.slideShow.cache[url].fail(function (u) {
			image = false;
			jQuery.fn.slideShow.cache[url] = false;
		});
		image.onload = function () {
			if (image) {
				image.natWidth = image.width;
				image.natHeight = image.height;
			}
			if (jQuery.fn.slideShow.cache[url]) {
				jQuery.fn.slideShow.cache[url].resolve(image);
			}
		};
		image.onerror = function () {
			if (jQuery.fn.slideShow.cache[url]) {
				jQuery.fn.slideShow.cache[url].reject(url);
			}
		};
		image.src = url;
	}
	return jQuery.fn.slideShow.cache[url];
};

jQuery.fn.slideShow.fitImage = function (container, image) {
	var ratio = image.natWidth / image.natHeight,
		screenRatio = container.width() / container.height(),
		width = null, height = null, top = null;
	if (ratio > screenRatio) {
		if (container.width() > image.natWidth * jQuery.fn.slideShow.settings.maxScale) {
			top = ((container.height() - image.natHeight) / 2) + 'px';
			height = image.natHeight + 'px';
			width = image.natWidth + 'px';
		} else {
			width = container.width() + 'px';
			height = (container.width() / ratio) + 'px';
			top = ((container.height() - (container.width() / ratio)) / 2) + 'px';
		}
	} else {
		if (container.height() > image.natHeight * jQuery.fn.slideShow.settings.maxScale) {
			top = ((container.height() - image.natHeight) / 2) + 'px';
			height = image.natHeight + 'px';
			width = image.natWidth + 'px';
		} else {
			top = 0;
			height = container.height() + 'px';
			width = (container.height() * ratio) + "px";
		}
	}
	jQuery(image).css({
		top   : top,
		width : width,
		height: height
	});
};

jQuery.fn.slideShow.showImage = function (url, preloadUrl) {
	var container = jQuery.fn.slideShow.container;

	container.css('background-position', 'center');
	jQuery.fn.slideShow.loadImage(url).then(function (image) {
		container.css('background-position', '-10000px 0');
		if (url === jQuery.fn.slideShow.images[jQuery.fn.slideShow.current]) {
			container.children('img').remove();
			container.append(image);
			jQuery.fn.slideShow.fitImage(container, image);
			if (jQuery.fn.slideShow.settings.play) {
				jQuery.fn.slideShow.setTimeout();
			}
			if (preloadUrl) {
				jQuery.fn.slideShow.loadImage(preloadUrl);
			}
		}
	});
};

jQuery.fn.slideShow.play = function () {
	if (jQuery.fn.slideShow.settings) {
		jQuery.fn.slideShow.settings.play = true;
		jQuery.fn.slideShow.setTimeout();
	}
};

jQuery.fn.slideShow.pause = function () {
	if (jQuery.fn.slideShow.settings) {
		jQuery.fn.slideShow.settings.play = false;
		jQuery.fn.slideShow.clearTimeout();
	}
};

jQuery.fn.slideShow.setTimeout = function () {
	jQuery.fn.slideShow.clearTimeout();
	jQuery.fn.slideShow.timeout = setTimeout(jQuery.fn.slideShow.next, jQuery.fn.slideShow.settings.interval);
	jQuery.fn.slideShow.progressBar.stop();
	jQuery.fn.slideShow.progressBar.css('height', '6px');
	jQuery.fn.slideShow.progressBar.animate({'height': '26px'}, jQuery.fn.slideShow.settings.interval, 'linear');
};

jQuery.fn.slideShow.clearTimeout = function () {
	if (jQuery.fn.slideShow.timeout) {
		clearTimeout(jQuery.fn.slideShow.timeout);
	}
	jQuery.fn.slideShow.progressBar.stop();
	jQuery.fn.slideShow.progressBar.css('height', '6px');
	jQuery.fn.slideShow.timeout = 0;
};

jQuery.fn.slideShow.next = function () {
	if (jQuery.fn.slideShow.container) {
		jQuery.fn.slideShow.current++;
		if (jQuery.fn.slideShow.current >= jQuery.fn.slideShow.images.length) {
			jQuery.fn.slideShow.current = 0;
		}
		var image = jQuery.fn.slideShow.images[jQuery.fn.slideShow.current],
			nextImage = jQuery.fn.slideShow.images[(jQuery.fn.slideShow.current + 1) % jQuery.fn.slideShow.images.length];
		jQuery.fn.slideShow.showImage(image, nextImage);
	}
};

jQuery.fn.slideShow.previous = function () {
	if (jQuery.fn.slideShow.container) {
		jQuery.fn.slideShow.current--;
		if (jQuery.fn.slideShow.current < 0) {
			jQuery.fn.slideShow.current = jQuery.fn.slideShow.images.length - 1;
		}
		var image = jQuery.fn.slideShow.images[jQuery.fn.slideShow.current],
			previousImage = jQuery.fn.slideShow.images[(jQuery.fn.slideShow.current - 1 + jQuery.fn.slideShow.images.length) % jQuery.fn.slideShow.images.length];
		jQuery.fn.slideShow.showImage(image, previousImage);
	}
};

jQuery.fn.slideShow.stop = function () {
	if (jQuery.fn.slideShow.container) {
		jQuery.fn.slideShow.clearTimeout();
		jQuery.fn.slideShow.container.hide();
		jQuery.fn.slideShow.container = null;
		if (jQuery.fn.slideShow.onstop) {
			jQuery.fn.slideShow.onstop();
		}
	}
};

jQuery.fn.slideShow.hideImage = function () {
	var container = jQuery.fn.slideShow.container;
	if (container) {
		container.children('img').remove();
	}
};

jQuery.fn.slideShow.onstop = null;


Slideshow = {};
Slideshow.start = function (images, start, options) {

	var content = $('#content');
	start = start || 0;
	Thumbnail.concurrent = 1; //make sure we can load the image and doesn't get blocked by loading thumbnail
	if (content.is(":visible") && typeof Gallery !== 'undefined') {
		Gallery.scrollLocation = $(window).scrollTop();
	}
	images.slideShow($('#slideshow'), start, options);
	content.hide();
};

Slideshow.end = function () {
	jQuery.fn.slideShow.stop();
};

Slideshow.next = function (event) {
	if (event) {
		event.stopPropagation();
	}
	jQuery.fn.slideShow.hideImage();
	jQuery.fn.slideShow.next();
};

Slideshow.previous = function (event) {
	if (event) {
		event.stopPropagation();
	}
	jQuery.fn.slideShow.hideImage();
	jQuery.fn.slideShow.previous();
};

Slideshow.pause = function (event) {
	if (event) {
		event.stopPropagation();
	}
	$('#slideshow').children('.play').show();
	$('#slideshow').children('.pause').hide();
	Slideshow.playPause.playing = false;
	jQuery.fn.slideShow.pause();
};

Slideshow.play = function (event) {
	if (event) {
		event.stopPropagation();
	}
	$('#slideshow').children('.play').hide();
	$('#slideshow').children('.pause').show();
	Slideshow.playPause.playing = true;
	jQuery.fn.slideShow.play();
};
Slideshow.playPause = function () {
	if (Slideshow.playPause.playing) {
		Slideshow.pause();
	} else {
		Slideshow.play();
	}
};
Slideshow.playPause.playing = false;
Slideshow._getSlideshowTemplate = function () {
	var defer = $.Deferred();
	if (!this.$slideshowTemplate) {
		var self = this;
		$.get(OC.filePath('gallery', 'templates', 'slideshow.html'), function (tmpl) {
			self.$slideshowTemplate = $(tmpl);
			defer.resolve(self.$slideshowTemplate);
		})
			.fail(function () {
				defer.reject();
			});
	} else {
		defer.resolve(this.$slideshowTemplate);
	}
	return defer.promise();
};

$(document).ready(function () {
	if ($('#body-login').length > 0) {
		return true; //deactivate slideshow on login page
	}

	//close slideshow on esc
	$(document).keyup(function (e) {
		if (e.keyCode === 27) { // esc
			Slideshow.end();
		} else if (e.keyCode === 37) { // left
			Slideshow.previous();
		} else if (e.keyCode === 39) { // right
			Slideshow.next();
		} else if (e.keyCode === 32) { // space
			Slideshow.playPause();
		}
	});

	$.when(Slideshow._getSlideshowTemplate()).then(function ($tmpl) {
		$('body').append($tmpl); //move the slideshow outside the content so we can hide the content

		if (!SVGSupport()) { //replace all svg images with png images for browser that dont support svg
			replaceSVG();
		}

		var slideshow = $('#slideshow');
		slideshow.children('.next').click(Slideshow.next);
		slideshow.children('.previous').click(Slideshow.previous);
		slideshow.children('.exit').click(jQuery.fn.slideShow.stop);
		slideshow.children('.pause').click(Slideshow.pause);
		slideshow.children('.play').click(Slideshow.play);
		slideshow.click(Slideshow.next);

		if ($.fn.mousewheel) {
			slideshow.bind('mousewheel.fb', function (e, delta) {
				e.preventDefault();
				if ($(e.target).get(0).clientHeight === 0 || $(e.target).get(0).scrollHeight === $(e.target).get(0).clientHeight) {
					if (delta > 0) {
						Slideshow.previous();
					} else {
						Slideshow.next();
					}
				}
			});
		}
	})
		.fail(function () {
			alert(t('core', 'Error loading slideshow template'));
		});


	if (typeof FileActions !== 'undefined' && typeof Slideshow !== 'undefined' && $('#filesApp').val()) {
		FileActions.register('image', 'View', OC.PERMISSION_READ, '', function (filename) {
			var images = $('#fileList tr[data-mime^="image"] a.name');
			var dir = FileList.getCurrentDirectory() + '/';
			var user = OC.currentUser;
			if (!user) {
				user = $('#sharingToken').val();
			}
			var start = 0;
			$.each(images, function (i, e) {
				var tr = $(e).closest('tr');
				var imageFile = tr.data('file');
				if (imageFile === filename) {
					start = i;
				}
				// use gallery URL instead of download URL
				e.imageUrl = OC.linkTo('gallery', 'ajax/image.php') +
					'?file=' + encodeURIComponent(user + dir + imageFile);
			});
			images.slideShow($('#slideshow'), start);
		});
		FileActions.setDefault('image', 'View');
	}
});
