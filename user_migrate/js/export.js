$(document).ready(function(){
    // Do the export
	$('#exportbtn').click(function(){
		// Show loader
		$('.loadingexport').show();
		$.getJSON(
			OC.filePath('user_migrate','ajax','export.php'),
			{operation:'create'},
			function(result){
				if(result.status == 'success'){
					// Download the file
					window.location = OC.linkTo('user_migrate','ajax/export.php') + '?operation=download&requesttoken=' + oc_requesttoken;
					$('.loading').hide();
					$('#exportbtn').val(t('user_migrate', 'Export'));
				} else {
					// Cancel loading
					$('#exportbtn').html('Failed');
					// Show Dialog
					OC.dialogs.alert(t('user_migrate', 'Something went wrong while the export file was being generated'), t('user_migrate', 'An error has occurred'), function(){
						$('#exportbtn').html(t('user_migrate', 'Export')+'<img class="loadingexport" src="'+OC.filePath('core','img','loading.gif')+'" />');
					});
				}
			}
		// End ajax
		);
	});
	
	// Do the import
	$('#importbtn').click(function(){
		// Show loader
		$('.loadingimport').show();
		$('#import').submit();
		
	});
});