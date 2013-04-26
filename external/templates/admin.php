<?php 
if(OCP\Config::getAppValue('external', 'allowUsers') == 'true') $checked = 'checked';
else $checked ='';
?>
<form id="allowUsersForm">
	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('External Sites'));?></strong></legend>
	
		<input type="checkbox" name"allowUsers" id="allowUsers" <?php echo $checked; ?> value="true" ><label for="allowUsers">Allow users to add personal links</label>
		<span class="msg"></span>
	


	</fieldset>
</form>
