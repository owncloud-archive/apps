<form id="django_auth" action="#" method="post">
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" id="requesttoken" >
	<fieldset class="personalblock">
		<h2>Django Authentification Backend</h2>
		<p>
			<input type="checkbox" name="staff_is_admin"     id="staff_is_admin"     <?php if ($_['staff_is_admin']     == true) { print_unescaped('checked="checked"');} ?> ><label for="staff_is_admin"    ><?php p($l->t('Django Staffusers get administration privileges'));?></label><br/>
			<input type="checkbox" name="superuser_is_admin" id="superuser_is_admin" <?php if ($_['superuser_is_admin'] == true) { print_unescaped('checked="checked"');} ?> ><label for="superuser_is_admin"><?php p($l->t('Django Superusers get administration privileges'));?></label>
		</p>
		<p>
			<label for="django_db_host"><?php p($l->t('DB Host'));?></label>
			<input type="text" id="django_db_host" name="django_db_host" value="<?php p($_['django_db_host']); ?>" >

			<label for="django_db_name"><?php p($l->t('DB Name'));?></label>
			<input type="text" id="django_db_name" name="django_db_name" value="<?php p($_['django_db_name']); ?>" >

			<label for="django_db_driver"><?php p($l->t('DB Driver'));?></label>
			<?php $db_driver = array('mysql' => 'MySQL', 'pgsql' => 'PostgreSQL');?>
			<select id="django_db_driver" name="django_db_driver">
				<?php foreach ($db_driver as $driver => $name): ?>
					<?php p($_['django_db_driver']); ?>
					<?php if ($_['django_db_driver'] == $driver): ?>
						<option selected="selected" value="<?php p($driver); ?>"><?php p($name); ?></option>
					<?php else: ?>
						<option value="<?php p($driver); ?>"><?php p($name); ?></option>
					<?php endif ?>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="django_db_user"><?php p($l->t('DB User'));?></label>
			<input type="text" id="django_db_user" name="django_db_user" value="<?php p($_['django_db_user']); ?>" >

			<label for="django_db_password"><?php p($l->t('DB Password'));?></label>
			<input type="password" id="django_db_password" name="django_db_password" value="<?php p($_['django_db_password']); ?>" >
		</p>
		<input type="submit" name="django_auth" value="Save" >
	</fieldset>
</form>
