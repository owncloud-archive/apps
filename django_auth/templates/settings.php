<form id="django_auth" action="#" method="post">
	<fieldset class="personalblock">
		<h2>Django Authentification Backend</h2>
		<p>
			<input type="checkbox" name="staff_is_admin"     id="staff_is_admin"     <?php if ($_['staff_is_admin']     == true) { print_unescaped('checked="checked"');} ?>/><label for="staff_is_admin"    ><?php p($l->t('Django Staffusers get administration privileges'));?></label><br/>
			<input type="checkbox" name="superuser_is_admin" id="superuser_is_admin" <?php if ($_['superuser_is_admin'] == true) { print_unescaped('checked="checked"');} ?>/><label for="superuser_is_admin"><?php p($l->t('Django Superusers get administration privileges'));?></label>
		</p>
		<input type="submit" name="django_auth" value="Save" />
	</fieldset>
</form>
