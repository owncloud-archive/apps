
$(function() {
	if (typeof FileActions!=='undefined') {
		FileActions.register('image', '', OC.PERMISSION_READ, function() {
			var link = FileActions.currentFile.find('a.name');
			link.attr('rel', "image").fancybox({
				"titleFormat": function() {
					return link.find('.nametext').text();
				},
				"titlePosition": "inside"
			});
		}, function() {});
	}

	OC.search.customResults.Images = function(row){
		row.find('a').fancybox({
			"titleFormat": function() {
				return this.orig.find('.name').text();
			},
			"titlePosition": "inside"
		});
	};
});

