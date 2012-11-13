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
	<?php /* I know, it's crap. But it's a fast and working crap ;) */ ?>
	<div id="upd-progress" style="display:none;height:20px;margin:5px 3px;width:200px;border:1px #ccc solid;"><div style="width:0;background-color:#5CE228;min-height:20px;"></div></div>
	<?php $data=OC_Updater::check();
		if(isset($data['version']) && strlen($data['version'])) { ?>
			<button id="updater_backup"><?php echo $l->t('Update') ?></button>
	<?php }		?>
</fieldset>
<script type="text/javascript">
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
								$('#upgrade_status').html(t('updater', 'Done. Reload the page to proceed.'));
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
</script>