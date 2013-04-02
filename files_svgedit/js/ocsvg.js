$(document).ready(function() {
	if(typeof FileActions!=='undefined'){
		FileActions.register('image/svg+xml','Edit','',function(filename){
            window.location = OC.filePath('files_svgedit', '', 'index.php')
                            + "?file=" + $('#dir').val() + "/" + filename;
		});
		FileActions.setDefault('image/svg+xml','Edit');
	}
    if(location.href.match(/\/files\/index\.php/)) {
        getMimeIcon('image/svg+xml', function(icon) {
            $('<li><p>' + t('files_svgedit', 'Graphic') + '</p></li>')
                .attr('id', 'newSvgLi')
                .appendTo('div#new>ul')
                .css('background-image', 'url(' + icon + ')')
                .data('type', 'svg')
                .children('p')
                .click(function() {
                    $(this).hide();
                    $('<input>').appendTo('#newSvgLi').focus().change(function() {
                        window.location = OC.filePath('files_svgedit', '', 'index.php')
                            + "?file=" + $('#dir').val() + "/" + $(this).val().replace(/(\..{3})?$/, '.svg');
                    }).blur(function() {
                        $(this).remove();
                        $('#newSvgLi>p').show();
                    });
                });
        });
    }
});
