var Gallery = {};
Gallery.albums = {};
Gallery.currentAlbum = '';
Gallery.subAlbums = {};
// fill the albums from Gallery.images
Gallery.fillAlbums = function () {
	var albumPath, i, imagePath, parent;
	for (i = 0; i < Gallery.images.length; i++) {
		imagePath = Gallery.images[i];
		albumPath = OC.dirname(imagePath);
		if (!Gallery.albums[albumPath]) {
			Gallery.albums[albumPath] = [];
		}
		parent = OC.dirname(albumPath);
		while (parent && !Gallery.albums[parent]) {
			Gallery.albums[parent] = [];
			parent = OC.dirname(parent);
		}
		Gallery.albums[albumPath].push(imagePath);
	}

	for (albumPath in Gallery.albums) {
		if (albumPath !== '') {
			parent = OC.dirname(albumPath);
			if (!Gallery.subAlbums[parent]) {
				Gallery.subAlbums[parent] = [];
			}
			Gallery.subAlbums[parent].push(albumPath);
		}
	}
};
Gallery.getImage = function (image) {
	return OC.filePath('files', 'ajax', 'download.php') + '?dir=' + OC.dirname(image) + '&files=' + OC.basename(image);
};
Gallery.getThumbnail = function (image) {
	return OC.filePath('gallery', 'ajax', 'thumbnail.php') + '?file=' + image;
};
Gallery.getAlbumThumbnail = function (image) {
	return OC.filePath('gallery', 'ajax', 'albumthumbnail.php') + '?file=' + image;
};
Gallery.view = {};
Gallery.view.element = null;
Gallery.view.clear = function () {
	Gallery.view.element.empty();
};

Gallery.view.addImage = function (image) {
	var link = $('<a/>'), thumb = $('<img/>');
	link.addClass('image');
	link.attr('href', Gallery.getImage(image)).attr('rel', 'album').attr('alt', OC.basename(image)).attr('title', OC.basename(image));

	thumb.attr('src', Gallery.getThumbnail(image));
	link.append(thumb);

	Gallery.view.element.append(link);
};

Gallery.view.addAlbum = function (path) {
	var link = $('<a/>'), image;
	link.addClass('album');
	link.attr('href', '#' + path);
	link.click(Gallery.view.viewAlbum.bind(null, path));
	link.data('path', path);
	link.data('offset', 0);
	link.attr('style', 'background-image:url("' + Gallery.getAlbumThumbnail(path) + '")').attr('title', OC.basename(path));
	image = new Image();
	image.src = Gallery.getAlbumThumbnail(path);

	link.mousemove(function (event) {
		var mousePos = event.pageX - $(this).offset().left,
			path = $(this).data('path'),
			album = Gallery.albums[path],
			offset = Math.floor((mousePos / 200) * (image.width / 200)),
			oldOffset = $(this).data('offset');
		if (offset !== oldOffset) {
			$(this).css('background-position', offset * 200 + 'px 0px');
			$(this).data('offset', offset);
		}
	});

	Gallery.view.element.append(link);
};

Gallery.view.viewAlbum = function (albumPath) {
	Gallery.view.clear();
	Gallery.currentAlbum = albumPath;

	var i, album, subAlbums, crumbs, path;
	subAlbums = Gallery.subAlbums[albumPath];
	if (subAlbums) {
		for (i = 0; i < subAlbums.length; i++) {
			Gallery.view.addAlbum(subAlbums[i]);
		}
	}

	album = Gallery.albums[albumPath];
	for (i = 0; i < album.length; i++) {
		Gallery.view.addImage(album[i]);
	}

	OC.Breadcrumb.clear();
	OC.Breadcrumb.push('Pictures', '#').click(Gallery.view.viewAlbum.bind(null, ''));
	crumbs = albumPath.split('/');
	path = '';
	for (i = 0; i < crumbs.length; i++) {
		if (crumbs[i]) {
			path += '/' + crumbs[i];
			OC.Breadcrumb.push(crumbs[i], '#' + crumbs[i]).click(Gallery.view.viewAlbum.bind(null, path));
		}
	}

	$('#gallery').children('a.image').fancybox({
		"titlePosition": "inside"
	});
};

Gallery.slideshow = {
	supersized: {},
	init: function () {
		$('#slideshow-content').append("<div id='supersized-holder'></div>");
		$('#supersized-loader').remove();
		$('#supersized').remove();
		$('#supersized-holder').append("<div id='supersized-loader'></div><ul id='supersized'></ul>");
	},
	start: function () {
		var i,
			album = Gallery.albums[Gallery.currentAlbum],
			images = [];

		for (i = 0; i < album.length; i++) {
			images.push({image: Gallery.getImage(album[i]), title: OC.basename(album[i]), thumb: Gallery.getThumbnail(album[i]), url: 'javascript:Gallery.slideshow.end()'});
		}

		if (images.length <= 0) {
			return;
		}

		//ensure all cleanup is done
		Gallery.slideshow.end();
		if ($.supersized.vars.is_paused && Gallery.slideshow.supersized.playToggle) {
			Gallery.slideshow.supersized.playToggle();
		}

		Gallery.slideshow.init();
		$('#supersized').show();
		$('#slideshow-content').show();

		$.supersized.themeVars.image_path = OC.linkTo('gallery', 'img/supersized/');
		$.supersized.call(Gallery.slideshow.supersized, {

			// Functionality
			slide_interval: 3000, // Length between transitions
			transition: 1, // 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
			transition_speed: 700, // Speed of transition

			// Components
			slide_links: 'blank', // Individual links for each slide (Options: false, 'num', 'name', 'blank')
			slides: images, // Slideshow Images
			image_protect: false,
			fit_always: true

		});

		if (BigScreen.enabled) {
			BigScreen.request();
		}

		$('html').css('overflow', 'hidden');//hide scrollbar during the slideshow
	},
	end: function () {
		BigScreen.exit();
		if (!$.supersized.vars.is_paused && Gallery.slideshow.supersized.playToggle) {
			Gallery.slideshow.supersized.playToggle();
		}
		if ($.supersized.vars.slideshow_interval) {
			clearInterval($.supersized.vars.slideshow_interval);
		}

		$('#supersized-holder').remove();
		$('#slideshow-content').hide();
		$('#thumb-list').remove();
		Gallery.slideshow.supersized = {};
		$.supersized.vars.in_animation = false;
		$.supersized.vars.current_slide = 0;
		$('html').css('overflow', 'auto');
	}
};

$(document).ready(function () {
	Gallery.fillAlbums();
	Gallery.view.element = $('#gallery');
	OC.Breadcrumb.container = $('#breadcrumbs');
	var album = location.hash.substr(1);
	Gallery.view.viewAlbum(album);

	//close slideshow on esc and remove holder
	$(document).keyup(function (e) {
		if (e.keyCode === 27) { // esc
			Gallery.slideshow.end();
		}
	});
	$('#slideshow-start').click(function () {
		Gallery.slideshow.start();
	});
});
