$(document).ready(function() {
	$('#updater_backup').click(function() {
		$('#upd-progress').show();
		$('#updater_backup').attr('disabled', 'disabled');
		$('#updater_backup').after('<div id="upgrade_status">' + t('updater', 'In progress...') + '</div>');
		$.post(OC.filePath('updater', 'ajax', 'admin.php'),
			{},
			function(response) {
				if (response.status && response.status == 'success') {
					$('#upd-progress div').css({width : '50%'});
					$.post(OC.filePath('updater', 'ajax', 'admin.php'),
						{},
						function(response) {
							if (response.status && response.status == 'success') {
								$('#upd-progress div').css({width : '100%'});
								$('#upgrade_status').html(t('updater', 'Done.'));
                                var successMsg = t('updater', 'Here is your backup: ');
                                OC.dialogs.info(successMsg + response.backup, 'Updater', function(){}, true);
							} else {
								var error = response.msg ? ': '+response.msg : '';
								$('#upgrade_status').html(t('updater', 'Error') + error);
							}
						}
						);
				} else {
					var error = response.msg ? ': '+response.msg : '';
					$('#upgrade_status').html(t('updater', 'Error') + error);
				}
			}
			)
	});
});