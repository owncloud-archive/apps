<form id="pwauth" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong>Unix Authentication</strong></legend>
		<p>
		<label for="pwauth_path"><?php echo $l->t('pwauth_path'); ?></label><input type="text" id="pwauth_path" name="pwauth_path" value="<?php echo $_['pwauth_path']; ?>" />
		</p><p>
		<label for="uid_list"><?php echo $l->t('uid_list');?></label><input type="text" id="uid_list" name="uid_list" value="<?php echo $_['uid_list']; ?>"  original-title="<?php echo $l->t('uid_list_original-title'); ?>"/>
		</p>
		<input type="submit" value="Save" />
	</fieldset>
</form>
