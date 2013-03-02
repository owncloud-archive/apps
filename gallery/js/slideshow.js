jQuery.fn.slideShow = function (container, start, options) {
	var i, images = [], settings;
	start = start || 0;
	settings = $.extend({
		'interval': 5000,
		'play': true,
		'maxScale': 2
	}, options);
	jQuery.fn.slideShow.container = container;
	jQuery.fn.slideShow.settings = settings;
	jQuery.fn.slideShow.current = start;
	for (i = 0; i < this.length; i++) {
		images.push(this[i].href);
	}
	container.children('img').remove();
	container.show();
	jQuery.fn.slideShow.images = images;
	jQuery.fn.slideShow.cache = [];
	jQuery.fn.slideShow.showImage(images[start]);
	jQuery.fn.slideShow.progressBar = container.find('.progress');
	return jQuery.fn.slideShow;
};

jQuery.fn.slideShow.progressBar = null;

jQuery.fn.slideShow.loadImage = function (url) {
	if (!jQuery.fn.slideShow.cache[url]) {
		jQuery.fn.slideShow.cache[url] = new jQuery.Deferred();
		image = new Image();
		image.onload = function () {
			jQuery.fn.slideShow.cache[url].resolve(image);
		};
		image.src = url;
	}
	return jQuery.fn.slideShow.cache[url];
};

jQuery.fn.slideShow.showImage = function (url) {
	var container = jQuery.fn.slideShow.container;
	jQuery.fn.slideShow.loadImage(url).then(function (image) {
		var ratio = image.width / image.height,
			screenRatio = container.width() / container.height(),
			width = null, height = null, top = null;
		container.children('img').remove();
		container.append(image);
		if (ratio > screenRatio) {
			if (container.width() > image.width * jQuery.fn.slideShow.settings.maxScale) {
				width = image.width + 'px';
				top = ((container.height() - image.height) / 2) + 'px';
			} else {
				width = '100%';
				top = ((container.height() - (container.width() / ratio)) / 2) + 'px';
			}
		} else {
			if (container.height() > image.height * jQuery.fn.slideShow.settings.maxScale) {
				top = ((container.height() - image.height) / 2) + 'px';
				height = image.height + 'px';
			} else {
				height = '100%';
			}
		}
		$(image).css({
			top: top,
			width: width,
			height: height
		});
		if (jQuery.fn.slideShow.settings.play) {
			jQuery.fn.slideShow.setTimeout();
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
		jQuery.fn.slideShow.showImage(jQuery.fn.slideShow.images[jQuery.fn.slideShow.current]);
	}
};

jQuery.fn.slideShow.previous = function () {
	if (jQuery.fn.slideShow.container) {
		jQuery.fn.slideShow.current--;
		if (jQuery.fn.slideShow.current < 0) {
			jQuery.fn.slideShow.current = jQuery.fn.slideShow.images.length - 1;
		}
		jQuery.fn.slideShow.showImage(jQuery.fn.slideShow.images[jQuery.fn.slideShow.current]);
	}
};

jQuery.fn.slideShow.stop = function () {
	if (jQuery.fn.slideShow.container) {
		jQuery.fn.slideShow.container.hide();
		jQuery.fn.slideShow.container = null;
	}
};

jQuery.fn.slideShow.hideImage = function () {
	var container = jQuery.fn.slideShow.container;
	if (container) {
		container.children('img').remove();
	}
};
