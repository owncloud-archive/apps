
/**
 * Handle indexing events from the 'Personal' page
 */
$(document).ready(function() {
    $('#lucene-reindex').click(function(){
        indexFiles();
    });
    $('#lucene-enabled').click(function(){
        var action = $(this).attr('checked') ? 'enable' : 'disable'; // this is backwards due to when click is called
        var url = OC.filePath('','','')+'index.php/apps/search/ajax/settings.php';
        $.get(url, {
            operation: action
        });
    });
});

/**
 * Index files using AJAX
 */
function indexFiles(){
    indexFiles.indexing=true;
    $('#index-message').show();
    // link to event source
    var url = OC.filePath('','','')+'index.php/apps/search/ajax/settings.php';
    var indexerEventSource = new OC.EventSource(url, {
        operation:'reindex'
    });
    indexFiles.cancel = indexerEventSource.close.bind(indexerEventSource);
    // handle 'indexing' events
    indexerEventSource.listen('indexing',function(data){
        $('#index-count').text(data.count);
        $('#objects-count').text(data.total);
        $('#index-file').text(data.name);
    });
    // handle 'success' events
    indexerEventSource.listen('success',function(data){
        indexFiles.indexing=false;
        if(data < 1){
            alert(t('files', 'Error while re-indexing files.'));
        }
        else{
            $('#index-size').text(data);
            $('#index-message').hide();
        }
    });
}
indexFiles.indexing=false;