/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$(document).ready(function() {
	if(OC.currentUser) {
		if(typeof FileActions!=='undefined'){
			FileActions.register('application/zip','Open', OC.PERMISSION_READ, '',function(filename){
				window.location=OC.linkTo('files', 'index.php')+'?dir='+encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+encodeURIComponent(filename);
			});
			FileActions.setDefault('application/zip','Open');
			FileActions.register('application/x-gzip','Open', OC.PERMISSION_READ, '',function(filename){
				window.location=OC.linkTo('files', 'index.php')+'?dir='+encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+encodeURIComponent(filename);
			});
			FileActions.setDefault('application/x-compressed','Open');
			FileActions.register('application/x-compressed','Open', OC.PERMISSION_READ, '',function(filename){
				window.location=OC.linkTo('files', 'index.php')+'?dir='+encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+encodeURIComponent(filename);
			});
			FileActions.setDefault('application/x-compressed','Open');
			FileActions.register('application/x-tar','Open', OC.PERMISSION_READ, '',function(filename){
				window.location=OC.linkTo('files', 'index.php')+'?dir='+encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/'+encodeURIComponent(filename);
			});
			FileActions.setDefault('application/x-tar','Open');
		}
	}
});
