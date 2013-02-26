var Gallery = {};
Gallery.albums = {};
Gallery.images = [];
Gallery.currentAlbum = '';
Gallery.subAlbums = {};
Gallery.users = [];
Gallery.displayNames = [];

Gallery.sortFunction = function (a, b) {
	return a.toLowerCase().localeCompare(b.toLowerCase());
};

// fill the albums from Gallery.images
Gallery.fillAlbums = function () {
	var def = new $.Deferred();
	$.getJSON(OC.filePath('gallery', 'ajax', 'getimages.php')).then(function (data) {
		var albumPath, i, imagePath, parent, path;
		Gallery.users = data.users;
		Gallery.displayNames = data.displayNames;
		for (i = 0; i < data.images.length; i++) {
			Gallery.images.push(data.images[i].path);
		}
		Gallery.fillAlbums.fill(Gallery.albums, Gallery.images);
		Gallery.fillAlbums.fillSubAlbums(Gallery.subAlbums, Gallery.albums);

		Gallery.fillAlbums.sortAlbums(Gallery.subAlbums);
		def.resolve();
	});
	return def;
};
Gallery.fillAlbums.fill = function (albums, images) {
	var imagePath, albumPath, parent;
	for (i = 0; i < images.length; i++) {
		imagePath = images[i];
		albumPath = OC.dirname(imagePath);
		if (!albums[albumPath]) {
			albums[albumPath] = [];
		}
		parent = OC.dirname(albumPath);
		while (parent && !albums[parent] && parent !== albumPath) {
			albums[parent] = [];
			parent = OC.dirname(parent);
		}
		albums[albumPath].push(imagePath);
	}
};
Gallery.fillAlbums.fillSubAlbums = function (subAlbums, albums) {
	var albumPath, parent;
	for (albumPath in albums) {
		if (albums.hasOwnProperty(albumPath)) {
			if (albumPath !== '') {
				parent = OC.dirname(albumPath);
				if (albumPath !== parent) {
					if (!subAlbums[parent]) {
						subAlbums[parent] = [];
					}
					subAlbums[parent].push(albumPath);
				}
			}
		}
	}
};
Gallery.fillAlbums.sortAlbums = function (albums) {
	var path;
	for (path in albums) {
		if (albums.hasOwnProperty(path)) {
			albums[path].sort(Gallery.sortFunction);
		}
	}
};

Gallery.getAlbumInfo = function (album) {
	if (!Gallery.getAlbumInfo.cache[album]) {
		var def = new $.Deferred();
		Gallery.getAlbumInfo.cache[album] = def;
		$.getJSON(OC.filePath('gallery', 'ajax', 'gallery.php'), {gallery: album}, function (data) {
			def.resolve(data);
		});
	}
	return Gallery.getAlbumInfo.cache[album];
};
Gallery.getAlbumInfo.cache = {};
Gallery.getImage = function (image) {
	return OC.filePath('gallery', 'ajax', 'image.php') + '?file=' + encodeURIComponent(image);
};
Gallery.getThumbnail = function (image) {
	return OC.filePath('gallery', 'ajax', 'thumbnail.php') + '?file=' + encodeURIComponent(image);
};
Gallery.getAlbumThumbnail = function (image) {
	return OC.filePath('gallery', 'ajax', 'albumthumbnail.php') + '?file=' + encodeURIComponent(image);
};
Gallery.share = function (event) {
	if (!OC.Share.droppedDown) {
		event.preventDefault();
		event.stopPropagation();
		Gallery.getAlbumInfo(Gallery.currentAlbum).then(function (info) {
			$('a.share').data('item', info.fileid)
				.data('possible-permissions', info.permissions).
				click();
		});
	}
};
Gallery.view = {};
Gallery.view.element = null;
Gallery.view.clear = function () {
	Gallery.view.element.empty();
};
Gallery.view.cache = {};

Gallery.view.addImage = function (image) {
	var link , thumb;
	if (Gallery.view.cache[image]) {
		Gallery.view.element.append(Gallery.view.cache[image]);
	} else {
		link = $('<a/>');
		thumb = $('<img/>');
		link.addClass('image');
		link.attr('href', Gallery.getImage(image)).attr('rel', 'album').attr('alt', OC.basename(image)).attr('title', OC.basename(image));

		thumb.attr('src', Gallery.getThumbnail(image));
		link.append(thumb);

		Gallery.view.element.append(link);
		Gallery.view.cache[image] = link;
	}
};

Gallery.view.addAlbum = function (path, name) {
	var link, image, label;
	name = name || OC.basename(path);
	if (Gallery.view.cache[path]) {
		Gallery.view.element.append(Gallery.view.cache[path]);
		//event handlers are removed when using clear()
		Gallery.view.cache[path].click(function () {
			Gallery.view.viewAlbum(path);
		});
		Gallery.view.cache[path].mousemove(function (event) {
			Gallery.view.addAlbum.mouseEvent.call(Gallery.view.cache[path], Gallery.view.addAlbum.thumbs[path], event);
		});
	} else {
		link = $('<a/>');
		label = $('<label/>');
		link.attr('href', '#' + path);
		link.addClass('album');
		link.click(function () {
			Gallery.view.viewAlbum(path);
		});
		link.data('path', path);
		link.data('offset', 0);
		link.attr('style', 'background-image:url("' + Gallery.getAlbumThumbnail(path) + '")').attr('title', OC.basename(path));
		label.text(name);
		link.append(label);
		image = new Image();
		image.src = Gallery.getAlbumThumbnail(path);
		Gallery.view.addAlbum.thumbs[path] = image;

		link.mousemove(function (event) {
			Gallery.view.addAlbum.mouseEvent.call(link, image, event);
		});

		Gallery.view.element.append(link);
		Gallery.view.cache[path] = link;
	}
};
Gallery.view.addAlbum.mouseEvent = function (image, event) {
	var mousePos = event.pageX - $(this).offset().left,
		path = $(this).data('path'),
		album = Gallery.albums[path],
		offset = Math.floor((mousePos / 200) * (image.width / 200)),
		oldOffset = $(this).data('offset');
	if (offset !== oldOffset) {
		$(this).css('background-position', offset * 200 + 'px 0px');
		$(this).data('offset', offset);
	}
};
Gallery.view.addAlbum.thumbs = {};

Gallery.view.viewAlbum = function (albumPath) {
	Gallery.view.clear();
	Gallery.currentAlbum = albumPath;

	var i, album, subAlbums, crumbs, path;
	subAlbums = Gallery.subAlbums[albumPath];
	if (subAlbums) {
		for (i = 0; i < subAlbums.length; i++) {
			Gallery.view.addAlbum(subAlbums[i]);
			Gallery.view.element.append(' '); //add a space for justify
		}
	}

	album = Gallery.albums[albumPath];
	if (album) {
		for (i = 0; i < album.length; i++) {
			Gallery.view.addImage(album[i]);
			Gallery.view.element.append(' '); //add a space for justify
		}
	}

	if (albumPath === OC.currentUser) {
		$('button.share').hide();
	} else {
		$('button.share').show();
	}

	OC.Breadcrumb.clear();
	OC.Breadcrumb.push('Pictures', '#').click(function () {
		Gallery.view.viewAlbum(OC.currentUser);
	});
	crumbs = albumPath.split('/');
	path = crumbs.splice(0, 1); //first entry is username
	if (path != OC.currentUser) { //remove shareid
		path += '/' + crumbs.splice(0, 1);
	}
	for (i = 0; i < crumbs.length; i++) {
		if (crumbs[i]) {
			path += '/' + crumbs[i];
			Gallery.view.pushBreadCrumb(crumbs[i], path);
		}
	}

	if (albumPath === OC.currentUser) {
		Gallery.view.showUsers();
	}

	Gallery.getAlbumInfo(Gallery.currentAlbum); //preload album info

	$('#gallery').children('a.image').click(function (event) {
		var i = $('#gallery').children('a.image').index(this);
		event.preventDefault();
		Gallery.slideshow.start(i, {play: Gallery.slideshow.playPause.playing});
	});
};

Gallery.view.pushBreadCrumb = function (text, path) {
	OC.Breadcrumb.push(text, '#' + path).click(function () {
		Gallery.view.viewAlbum(path);
	});
};

Gallery.view.showUsers = function () {
	var i, j, user, head, subAlbums, album;
	for (i = 0; i < Gallery.users.length; i++) {
		user = Gallery.users[i];
		head = $('<h2/>');
		head.text(t('gallery', 'Shared by') + ' ' + Gallery.displayNames[user]);
		$('#gallery').append(head);
		subAlbums = Gallery.subAlbums[user];
		if (subAlbums) {
			for (j = 0; j < subAlbums.length; j++) {
				album = subAlbums[j];
				album = Gallery.subAlbums[album][0];//first level sub albums is share source id
				Gallery.view.addAlbum(album);
				Gallery.view.element.append(' '); //add a space for justify
			}
		}
	}
};

Gallery.slideshow = {};
Gallery.slideshow.start = function (start, options) {
	start = start || 0;
	$('a.image').slideShow($('#slideshow'), start, options);
};

Gallery.slideshow.end = function () {
	jQuery.fn.slideShow.stop();
};

Gallery.slideshow.next = function (event) {
	if (event) {
		event.stopPropagation();
	}
	jQuery.fn.slideShow.hideImage();
	jQuery.fn.slideShow.next();
};

Gallery.slideshow.previous = function (event) {
	if (event) {
		event.stopPropagation();
	}
	jQuery.fn.slideShow.hideImage();
	jQuery.fn.slideShow.previous();
};

Gallery.slideshow.pause = function (event) {
	if (event) {
		event.stopPropagation();
	}
	$('#slideshow').children('.play').show();
	$('#slideshow').children('.pause').hide();
	Gallery.slideshow.playPause.playing = false;
	jQuery.fn.slideShow.pause();
};

Gallery.slideshow.play = function (event) {
	if (event) {
		event.stopPropagation();
	}
	$('#slideshow').children('.play').hide();
	$('#slideshow').children('.pause').show();
	Gallery.slideshow.playPause.playing = true;
	jQuery.fn.slideShow.play();
};

Gallery.slideshow.playPause = function () {
	if (Gallery.slideshow.playPause.playing) {
		Gallery.slideshow.pause();
	} else {
		Gallery.slideshow.play();
	}
};
Gallery.slideshow.playPause.playing = true;

$(document).ready(function () {
	Gallery.fillAlbums().then(function () {
		Gallery.view.element = $('#gallery');
		OC.Breadcrumb.container = $('#breadcrumbs');
		var album = location.hash.substr(1);
		if (!album) {
			album = OC.currentUser;
		}
		Gallery.view.viewAlbum(album);

		//close slideshow on esc
		$(document).keyup(function (e) {
			if (e.keyCode === 27) { // esc
				Gallery.slideshow.end();
			} else if (e.keyCode == 37) { // left
				Gallery.slideshow.previous();
			} else if (e.keyCode == 39) { // right
				Gallery.slideshow.next();
			} else if (e.keyCode == 32) { // space
				Gallery.slideshow.playPause();
			}
		});
		var slideshow = $('#slideshow');
		slideshow.children('.next').click(Gallery.slideshow.next);
		slideshow.children('.previous').click(Gallery.slideshow.previous);
		slideshow.children('.exit').click(jQuery.fn.slideShow.stop);
		slideshow.children('.pause').click(Gallery.slideshow.pause);
		slideshow.children('.play').click(Gallery.slideshow.play);
		slideshow.click(Gallery.slideshow.next);

		$('button.share').click(Gallery.share);
	});
});

window.onhashchange = function () {
	var album = location.hash.substr(1);
	if (!album) {
		album = OC.currentUser;
	}
	Gallery.view.viewAlbum(album);
};
