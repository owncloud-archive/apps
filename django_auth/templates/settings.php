<form id="django_auth" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong>Django Authentification Backend</strong></legend>
		<p>
			<input type="checkbox" name="staff_is_admin"     id="staff_is_admin"     <?php if ($_['staff_is_admin']     == true) { echo 'checked="checked"';} ?>/><label for="staff_is_admin"    ><?php echo $l->t('Django Staffusers get administration privileges');?></label><br/>
			<input type="checkbox" name="superuser_is_admin" id="superuser_is_admin" <?php if ($_['superuser_is_admin'] == true) { echo 'checked="checked"';} ?>/><label for="superuser_is_admin"><?php echo $l->t('Django Superusers get administration privileges');?></label>
		</p>
		<input type="submit" name="django_auth" value="Save" />
	</fieldset>
</form>
