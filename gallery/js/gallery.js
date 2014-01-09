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
	var token = $('#gallery').data('token');
	$.getJSON(OC.filePath('gallery', 'ajax', 'getimages.php'), {token: token}).then(function (data) {
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
	images.sort();
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
	if (album === $('#gallery').data('token')) {
		return [];
	}
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
Gallery.getAlbumThumbnailPaths = function (album) {
	var paths = [];
	if (Gallery.albums[album].length) {
		paths = Gallery.albums[album].slice(0, 10);
	}
	if (Gallery.subAlbums[album]) {
		for (var i = 0; i < Gallery.subAlbums[album].length; i++) {
			if (paths.length < 10) {
				paths = paths.concat(Gallery.getAlbumThumbnailPaths(Gallery.subAlbums[album][i]));
			}
		}
	}
	return paths;
};
Gallery.share = function (event) {
	if (!OC.Share.droppedDown) {
		event.preventDefault();
		event.stopPropagation();

		(function () {
			var target = OC.Share.showLink;
			OC.Share.showLink = function () {
				var r = target.apply(this, arguments);
				$('#linkText').val($('#linkText').val().replace('service=files', 'service=gallery'));
				return r;
			};
		})();

		Gallery.getAlbumInfo(Gallery.currentAlbum).then(function (info) {
			$('a.share').data('item', info.fileid).data('link', true)
				.data('possible-permissions', info.permissions).
				click();
			if (!$('#linkCheckbox').is(':checked')) {
				$('#linkText').hide();
			}
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
		thumb = Thumbnail.get(image);
		thumb.queue();
	} else {
		link = $('<a/>');
		link.addClass('image loading');
		link.attr('data-path', image);
		link.attr('href', Gallery.getImage(image)).attr('rel', 'album').attr('alt', OC.basename(image)).attr('title', OC.basename(image));

		thumb = Thumbnail.get(image);
		thumb.queue().then(function (thumb) {
			link.removeClass('loading');
			link.append(thumb);
		});

		Gallery.view.element.append(link);
		Gallery.view.cache[image] = link;
	}
};

Gallery.view.addAlbum = function (path, name) {
	var link, image, label, thumbs, thumb;
	name = name || OC.basename(path);
	if (Gallery.view.cache[path]) {
		thumbs = Gallery.view.addAlbum.thumbs[path];
		Gallery.view.element.append(Gallery.view.cache[path]);
		//event handlers are removed when using clear()
		Gallery.view.cache[path].click(function () {
			Gallery.view.viewAlbum(path);
		});
		Gallery.view.cache[path].mousemove(function (event) {
			Gallery.view.addAlbum.mouseEvent.call(Gallery.view.cache[path], thumbs, event);
		});
		thumb = Thumbnail.get(thumbs[0], true);
		thumb.queue();
	} else {
		thumbs = Gallery.getAlbumThumbnailPaths(path);
		Gallery.view.addAlbum.thumbs[path] = thumbs;
		link = $('<a/>');
		label = $('<label/>');
		link.attr('href', '#' + path);
		link.addClass('album loading');
		link.click(function () {
			Gallery.view.viewAlbum(path);
		});
		link.data('path', path);
		link.data('offset', 0);
		link.attr('title', OC.basename(path));
		label.text(name);
		link.append(label);
		thumb = Thumbnail.get(thumbs[0], true);
		thumb.queue().then(function (image) {
			link.removeClass('loading');
			link.append(image);
		});

		link.mousemove(function (event) {
			Gallery.view.addAlbum.mouseEvent.call(link, thumbs, event);
		});

		Gallery.view.element.append(link);
		Gallery.view.cache[path] = link;
	}
};
Gallery.view.addAlbum.thumbs = {};

Gallery.view.addAlbum.mouseEvent = function (thumbs, event) {
	var mousePos = event.pageX - $(this).offset().left,
		offset = ((Math.floor((mousePos / 200) * thumbs.length - 1) % thumbs.length) + thumbs.length) % thumbs.length, //workaround js modulo "feature" with negative numbers
		link = this,
		oldOffset = $(this).data('offset');
	if (offset !== oldOffset && !link.data('loading')) {
		if (!thumbs[offset]) {
			console.log(offset);
		}
		var thumb = Thumbnail.get(thumbs[offset], true);
		link.data('loading', true);
		thumb.load().then(function (image) {
			link.data('loading', false);
			$('img', link).remove();
			link.append(image);
		});
		$(this).data('offset', offset);
	}
};
Gallery.view.addAlbum.thumbs = {};

Gallery.view.viewAlbum = function (albumPath) {
	if (!albumPath) {
		albumPath = $('#gallery').data('token');
	}
	Thumbnail.queue = [];
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
	var albumName = $('#content').data('albumname');
	if (!albumName) {
		albumName = t('gallery', 'Pictures');
	}
	OC.Breadcrumb.push(albumName, '#').click(function () {
		Gallery.view.viewAlbum(OC.currentUser);
	});
	crumbs = albumPath.split('/');
	//first entry is username
	path = crumbs.splice(0, 1);
	//remove shareid
	if (path[0] !== OC.currentUser && path[0] !== $('#gallery').data('token')) {
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
		subAlbums = Gallery.subAlbums[user];
		if (subAlbums) {
			if (subAlbums.length > 0) {
				head = $('<h2/>');
				head.text(t('gallery', 'Shared by') + ' ' + Gallery.displayNames[user]);
				$('#gallery').append(head);
				for (j = 0; j < subAlbums.length; j++) {
					album = subAlbums[j];
					album = Gallery.subAlbums[album][0];//first level sub albums is share source id
					Gallery.view.addAlbum(album);
					Gallery.view.element.append(' '); //add a space for justify
				}
			}
		}
	}
};

$(document).ready(function () {
	Gallery.fillAlbums().then(function () {
		Gallery.view.element = $('#gallery');
		OC.Breadcrumb.container = $('#breadcrumbs');
		window.onhashchange();
		$('button.share').click(Gallery.share);
	});

	$('#gallery').on('click', 'a.image', function (event) {
		var images = $('#gallery').children('a.image');
		var i = images.index(this),
			image = $(this).data('path');
		event.preventDefault();
		if (location.hash !== image) {
			location.hash = image;
			Thumbnail.paused = true;
			Slideshow.start(images, i);
		}
	});

	$('#openAsFileListButton').click(function (event) {
		window.location.href = window.location.href.replace('service=gallery', 'service=files');
	});

	jQuery.fn.slideShow.onstop = function () {
		$('#content').show();
		Thumbnail.paused = false;
		$(window).scrollTop(Gallery.scrollLocation);
		location.hash = Gallery.currentAlbum;
		Thumbnail.concurrent = 3;
	};
});

window.onhashchange = function () {
	var album = location.hash.substr(1);
	if (!album) {
		album = OC.currentUser;
	}
	if (!album) {
		album = $('#gallery').data('token');
	}
	if (Gallery.images.indexOf(album) === -1) {
		Slideshow.end();
		Gallery.view.viewAlbum(decodeURIComponent(album));
	} else {
		Gallery.view.viewAlbum(OC.dirname(album));
		$('#gallery').find('a.image[data-path="' + album + '"]').click();
	}
};
