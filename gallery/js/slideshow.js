jQuery.fn.slideShow = function (container, start, options) {
	var i, images = [], settings;
	start = start || 0;
	settings = jQuery.extend({
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
	jQuery.fn.slideShow.showImage(images[start], images[start + 1]);
	jQuery.fn.slideShow.progressBar = container.find('.progress');
	jQuery(window).resize(function () {
		jQuery.fn.slideShow.loadImage(jQuery.fn.slideShow.images[jQuery.fn.slideShow.current]).then(function(image) {
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
			top = ((container.height() - (image.natHeight * jQuery.fn.slideShow.settings.maxScale)) / 2) + 'px';
			height = (image.natHeight * jQuery.fn.slideShow.settings.maxScale) + 'px';
			width = (image.natWidth * jQuery.fn.slideShow.settings.maxScale) + 'px';
		} else {
			width = container.width()+'px';
			height = (container.width()/ratio)+'px';
			top = ((container.height() - (container.width() / ratio)) / 2) + 'px';
		}
	} else {
		if (container.height() > image.natHeight * jQuery.fn.slideShow.settings.maxScale) {
			top = ((container.height() - (image.natHeight * jQuery.fn.slideShow.settings.maxScale)) / 2) + 'px';
			height = (image.natHeight * jQuery.fn.slideShow.settings.maxScale) + 'px';
			width = (image.natWidth * jQuery.fn.slideShow.settings.maxScale) + 'px';
		} else {
			top = 0;
			height = container.height()+'px';
			width = (container.height()*ratio)+"px";
		}
	}
	jQuery(image).css({
		top: top,
		width: width,
		height: height
	});
};

jQuery.fn.slideShow.showImage = function (url, preloadUrl) {
	var container = jQuery.fn.slideShow.container;
	jQuery.fn.slideShow.loadImage(url).then(function (image) {
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
