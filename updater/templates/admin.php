<?php

/**
 * ownCloud - Updater plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

?>

<fieldset class="personalblock">
	<strong>Updater</strong>
	<br />
	<?php echo OC_Updater::ShowUpdatingHint() ?>
	<br />
	<?php $data=OC_Updater::check();
		if(isset($data['version']) && !empty($data['version'])) { ?>
			<button id="updater_backup"><?php echo $l->t('Update') ?></button>
			
	<?php	}		?>
</fieldset>
<script type="text/javascript">
    $(document).ready(function() {
		$('#updater_backup').click(function() {
			$('#updater_backup').attr('disabled', 'disabled');
			$('#updater_backup').after('<div id="upgrade_status">' + t('updater', 'In progress...') + '</div>');
			$.post(OC.filePath('updater', 'ajax', 'admin.php'),
			{},
			function(response) {
				if (response.status && response.status == 'success') {
					$('#upgrade_status').html(t('updater', 'Done. Reload the page to proceed.'));
				} else {
					var error = response.msg ? ': '+response.msg : '';
					$('#upgrade_status').html(t('updater', 'Error') + error);
				}
			}
		)
		});
    });    
</script>