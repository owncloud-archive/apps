<form id="pwauth" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong>Unix Authentication</strong></legend>
		<p><label for="min_uid"><?php echo $l->t('min_uid');?><input type="text" id="min_uid" name="min_uid" value="<?php echo $_['min_uid']; ?>"></label>
		<label for="max_uid"><?php echo $l->t('max_uid');?></label><input type="text" id="max_uid" name="max_uid" value="<?php echo $_['max_uid']; ?>" /></p>
		<input type="submit" value="Save" />
	</fieldset>
</form>
