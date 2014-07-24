/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$(document).ready(function () {
	var registerMime = function (mime) {
		OCA.Files.fileActions.register(mime, 'Open', OC.PERMISSION_READ, '', function (filename, context) {
			context.fileList.changeDirectory(context.dir + '/' + filename);
		});
		OCA.Files.fileActions.setDefault(mime, 'Open');
	};

	if (OCA.Files) {
		registerMime('application/zip');
		registerMime('application/x-gzip');
		registerMime('application/x-compressed');
		registerMime('application/x-tar');
	}
});
