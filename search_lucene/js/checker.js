function luceneIndexFiles() {
	var spinner, count, updateEventSource;
	if (luceneIndexFiles.active) {
		return;
	}
	t('search_lucene', 'Indexing... {count} files left', {count: 0}); //preload translations
	luceneIndexFiles.active = true;
	updateEventSource = new OC.EventSource(OC.filePath('search_lucene', 'ajax', 'lucene.php'), {operation: 'index'});
	updateEventSource.listen('count', function (unIndexedCount) {
		count = unIndexedCount;
		if (count > 0) {
			spinner = $('form.searchbox #spinner');
			if (spinner.length == 0) {
				$('#searchbox').addClass('indexing');
				spinner = $('<div id="spinner"/>');
				$('form.searchbox').append(spinner);
				spinner.tipsy({trigger: 'manual', gravity: 'e', fade: false});
				spinner.attr('title', t('search_lucene', 'Indexing... {count} files left', {count: count}));
				spinner.tipsy('show');
			}
		}
	});

	updateEventSource.listen('error', function (path) {
		console.log('error while indexing ' + path);
	});

	updateEventSource.listen('indexing', function (path) {
		count--;
		spinner.attr('title', t('search_lucene', 'Indexing... {count} files left', {count: count}));
		spinner.tipsy('show');
	});

	updateEventSource.listen('done', function (path) {
		if (spinner) {
			spinner.tipsy('hide');
			spinner.remove();
		}
	});
}
luceneIndexFiles.active = false;

$(document).ready(function () {
	//add listener to the search box
	$('#searchbox').on('click', function () {
		setTimeout(function () { //load other stuff first
			//check status of indexer
			luceneIndexFiles();
		}, 100);
	});

	//clock that shows progress ○◔◑◕●.
	//hovering over it shows the current file
	//clicking it stops the indexer: ⌛


});
