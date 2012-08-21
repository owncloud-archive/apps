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
    $(document).ready(function(){
		$('#updater_backup').click(function(){
			$.post(OC.filePath('updater', 'ajax', 'admin.php'),
			{},
			function(response){
				if (response.status && response.status == 'success'){
					alert('done');
				} else {
					var error = response.msg ? ': '+response.msg : '';
					alert('Error' + error);
				}
			}
		)
		});
    });    
</script>