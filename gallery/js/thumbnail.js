function Thumbnail(path, square) {
	this.square = square;
	this.path = path;
	this.url = Thumbnail.getUrl(path, square);
	this.image = null;
	this.loadingDeferred = new $.Deferred();
}

Thumbnail.map = {};
Thumbnail.squareMap = {};

Thumbnail.get = function (path, square) {
	var map = (square) ? Thumbnail.squareMap : Thumbnail.map
	if (!map[path]) {
		map[path] = new Thumbnail(path, square);
	}
	return map[path];
};

Thumbnail.getUrl = function (path, square) {
	if (square) {
		return OC.filePath('gallery', 'ajax', 'thumbnail.php') + '?file=' + encodeURIComponent(path) + '&square=1';
	} else {
		return OC.filePath('gallery', 'ajax', 'thumbnail.php') + '?file=' + encodeURIComponent(path);
	}
};

Thumbnail.prototype.load = function () {
	var that = this;
	if (!this.image) {
		this.image = new Image();
		this.image.onload = function () {
			that.loadingDeferred.resolve(that.image);
		};
		this.image.src = this.url;
	}
	return this.loadingDeferred;
};
