$(function(){
	$('#user_openid_provider .delete').click(function() {
		var tr = $(this).closest('tr'),
			site = tr.find('td:first').text();

		$.post(OC.filePath('user_openid_provider', 'ajax', 'remove.php'), {url:site}, function(jsondata){
			if (jsondata.status=='success') {
				tr.remove();
			}
		});
		return false;
	});
});
