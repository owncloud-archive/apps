<form id="django_auth" action="#" method="post">
	<fieldset class="personalblock">
		<legend><strong>Django Authentification Backend</strong></legend>
		<p>
			<input type="checkbox" name="staff_is_admin"     id="staff_is_admin"     <?php if ($_['staff_is_admin']     == true) { echo 'checked="checked"';} ?>/><label for="staff_is_admin"    ><?php echo $l->t('Django Staffusers get administration privileges');?></label><br>
			<input type="checkbox" name="superuser_is_admin" id="superuser_is_admin" <?php if ($_['superuser_is_admin'] == true) { echo 'checked="checked"';} ?>/><label for="superuser_is_admin"><?php echo $l->t('Django Superusers get administration privileges');?></label>
		</p>
		<p>
			<label for="django_db_host"><?php echo $l->t('DB Host');?></label>
			<input type="text" id="django_db_host" name="django_db_host" value="<?php echo $_['django_db_host']; ?>" />

			<label for="django_db_name"><?php echo $l->t('DB Name');?></label>
			<input type="text" id="django_db_name" name="django_db_name" value="<?php echo $_['django_db_name']; ?>" />

			<label for="django_db_driver"><?php echo $l->t('DB Driver');?></label>
			<?php $db_driver = array('mysql' => 'MySQL', 'pgsql' => 'PostgreSQL');?>
			<select id="django_db_driver" name="django_db_driver">
				<?php foreach ($db_driver as $driver => $name): ?>
					<?php echo $_['django_db_driver']; ?>
					<?php if ($_['django_db_driver'] == $driver): ?>
						<option selected="selected" value="<?php echo $driver; ?>"><?php echo $name; ?></option>
					<?php else: ?>
						<option value="<?php echo $driver; ?>"><?php echo $name; ?></option>
					<?php endif ?>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="django_db_user"><?php echo $l->t('DB User');?></label>
			<input type="text" id="django_db_user" name="django_db_user" value="<?php echo $_['django_db_user']; ?>" />

			<label for="django_db_password"><?php echo $l->t('DB Password');?></label>
			<input type="password" id="django_db_password" name="django_db_password" value="<?php echo $_['django_db_password']; ?>" />
		</p>
		<input type="submit" name="django_auth" value="Save" />
	</fieldset>
</form>
