$(document).ready(function() {
	if(typeof FileActions!=='undefined'){
		FileActions.register('image','View', OC.PERMISSION_READ, '',function(filename){
			//viewImage($('#dir').val(),filename);
			if(filename.indexOf('.psd')>0)
				return;
			var pos = null;
			var gallerie = $('#filestable tr[data-mime^="image/"]').map(function(index){
				if($(this).attr('data-file') == filename)
					pos = index;

				var href = $(this).find('a.name').attr('href');

				if(href.indexOf('.psd')>0)
					return;
				else
					return href;
			}).get();
			$.fancybox(gallerie, {
				type: 'image', 
				index: pos, 
				onStart:function(){
					this.title = this.content.substr(this.content.lastIndexOf('/') + 1);
				},
				titlePosition: "inside"
			});
		});
		FileActions.setDefault('image','View');
	}
	OC.search.customResults.Images=function(row,item){
		var image=item.link.substr(item.link.indexOf('download')+8);
		var a=row.find('a');
		a.attr('href','#');
		a.click(function(){
			image = decodeURIComponent(image);
			var pos=image.lastIndexOf('/')
			var file=image.substr(pos + 1);
			var dir=image.substr(0,pos);
			viewImage(dir,file);
		});
	}
});

function viewImage(dir, file) {
	if(file.indexOf('.psd')>0){//can't view those
		return;
	}
	
	var location = fileDownloadPath(dir, file);
alert(dir+'\n\n'+file+'\n\n'+location);
	$.fancybox({
		"href": location,
		"title": file.replace(/</, "&lt;").replace(/>/, "&gt;"),
		"titlePosition": "inside"
	});
}
