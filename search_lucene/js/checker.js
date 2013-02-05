function lucene_index_files(ids) {
	if ( $.isArray(ids) && ids.length > 0 ) {
		if ( $('form.searchbox #spinner').length == 0 ) {
			$('#searchbox').addClass('indexing');
			$('form.searchbox').append('<div id="spinner"/>');
			$('form.searchbox #spinner').tipsy({trigger:'manual', gravity:'e', fade:false});
		}
		var id = ids.pop();
		
		var updateEventSource = new OC.EventSource(OC.filePath('search_lucene','ajax','lucene.php'),{operation:'index', id:id});
		updateEventSource.listen('error', function(message) {
			console.log(message.message);
			/*todo log in browser?*/
		});
		updateEventSource.listen('indexing', function(message) {
			console.log(t('search_lucene','Indexing {filename}, {count} files in queue',
				{filename:OC.basename(message.file),count:ids.length}));
			$('form.searchbox #spinner').attr('title',t('search_lucene','Indexing... {count} files left',
				{filename:OC.basename(message.file),count:ids.length}));
			$('form.searchbox #spinner').tipsy('show');
		});
		updateEventSource.listen('done', function(message) {
			console.log('done');
			if (ids.length > 0) {
				setTimeout(function () {
					lucene_index_files(ids)
				}, 100);
			} else {
				console.log('finished');
				$('#searchbox').removeClass('indexing');
				$('form.searchbox #spinner').tipsy('hide');
				$('form.searchbox #spinner').remove();
			}
		});
		
	}
}

$(document).ready(function () {
	//add listerner to the search box
	$('#searchbox').on('click',function(){
		//check status of indexer
		if ( $('form.searchbox #spinner').length == 0 ) {
			$.get( OC.filePath('search_lucene','ajax','status.php'), {}, function(result){
				lucene_index_files(result.files);
			}).fail(/*TODO show notification to user?*/);
		}
	})
	
	//clock that shows progress ○◔◑◕●.
	//hovering over it shows the current file
	//clicking it stops the indexer: ⌛ 
	
	
});