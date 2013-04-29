$(document).ready(function(){
	OC.search.customResults.Music=function(row,item){
		var parts=item.link.substr(item.link.indexOf('#')+1).split('&');
		var data={};
		for(var i=0;i<parts.length;i++){
			var itemParts=parts[i].split('=');
			data[itemParts[0]]=itemParts[1].replace(/\+/g,' ');
		}
		var media=Collection.find(data.artist,data.album,data.song);
		var a=row.find('a');
		a.attr('href','#');
		a.click(function(){
			var oldSize=PlayList.items.length;
			PlayList.add(media);
			PlayList.play(oldSize);
			PlayList.render();
		});
		var button=$('<input type="button" title="'+t('media','Add album to playlist')+'" class="add"></input>');
		button.css('background-image','url('+OC.imagePath('core','actions/play-add')+')');
		button.click(function(event){
			event.stopPropagation();
			PlayList.add(media);
			PlayList.render();
		});
		row.find('div.name').append(button);
		button.tipsy({gravity:'n', fade:true, delayIn: 400, live:true});
	};
	Collection.display();

	Collection.load(function(){
		var urlVars=getUrlVars();
		if(urlVars.artist){
			var song=Collection.find(urlVars.artist,urlVars.album,urlVars.song);
			PlayList.add(song);
			PlayList.play(0);
		}
	});

	$('.jp-progress').hover(function(){
		$('.jp-current-time').stop().fadeToggle();
	});
	$('.jp-current-time').mouseenter(function(){
		$('.jp-current-time').hide();
	});

	$('.ampache-link').click(function() {
		$(this).find('input').select();
	});
	$('#media-settings').find('.settings').on('click keydown',function(event) {
			if((event.type === 'keydown' || event.type === 'keypress') 
				&& (event.keyCode !== 32 && event.keyCode !== 13)
			) {
				return;
			}
			var bodyListener = function(e) {
				if($('#media-settings').find($(e.target)).length == 0) {
					$('#media-settings').switchClass('open', '');
				}
			};
			if($('#media-settings').hasClass('open')) {
				$('#media-settings').switchClass('open', '');
				$('body').unbind('click', bodyListener);
			} else {
				$('#media-settings').find('h2').trigger('click');
				$('#media-settings').switchClass('', 'open');
				$('body').bind('click', bodyListener);
			}
		});
});

function getUrlVars(){
	var vars = {}, hash;
	var hashes = window.location.hash.substr(1).split('&');
	for(var i = 0; i < hashes.length; i++){
		hash = hashes[i].split('=');
		vars[hash[0]] = decodeURIComponent(hash[1]).replace(/\+/g,' ');
	}
	return vars;
}

function musicTypeFromFile(file){
	var extension=file.split('.').pop().toLowerCase();
	if(extension=='ogg'){
		return 'oga';
	}
	//TODO check for more specific cases
	return extension;
}
