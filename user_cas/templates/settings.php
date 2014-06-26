
<link rel="stylesheet" type="text/css" href="../apps/user_cas/css/cas.css" />

<form id="cas" action="#" method="post">
	<div id="casSettings" class="personalblock">
    <strong><?php p($l->t('CAS Authentication backend'));?></strong>
	<ul>
		<li><a href="#casSettings-1"><?php p($l->t('CAS Server'));?></a></li>
	        <li><a href="#casSettings-2"><?php p($l->t('Basic'));?></a></li>
		<li><a href="#casSettings-3"><?php p($l->t('Mapping'));?></a></li>
	</ul>

	<fieldset id="casSettings-1">
		<p><label for="cas_server_version"><?php p($l->t('CAS Server Version'));?></label>
		<select id="cas_server_version" name="cas_server_version">
			<?php $version = $_['cas_server_version'];?>
			<option value="S1" <?php echo $version=='S1'?'selected':''; ?>>SAML 1.1</option>
			<option value="2.0" <?php echo $version=='2.0'?'selected':''; ?>>CAS 2.0</option>
			<option value="1.0" <?php echo $version=='1.0'?'selected':''; ?>>CAS 1.0</option>
		</select>
		</p>
		<p><label for="cas_server_hostname"><?php p($l->t('CAS Server Hostname'));?></label><input type="text" id="cas_server_hostname" name="cas_server_hostname" value="<?php p($_['cas_server_hostname']); ?>"></p>
		<p><label for="cas_server_port"><?php p($l->t('CAS Server Port'));?></label><input type="text" id="cas_server_port" name="cas_server_port" value="<?php p($_['cas_server_port']); ?>"></p>
		<p><label for="cas_server_path"><?php p($l->t('CAS Server Path'));?></label><input type="text" id="cas_server_path" name="cas_server_path" value="<?php p($_['cas_server_path']); ?>"></p>
                <p><label for="cas_cert_path"><?php p($l->t('Certification file path (.crt). Leave empty if dont want to validate'));?></label><input type="text" id="cas_cert_path" name="cas_cert_path" value="<?php p($_['cas_cert_path']); ?>"></p>


	</fieldset>
	<fieldset id="casSettings-2">
	<p><label for="cas_autocreate"><?php p($l->t('Autocreate user after CAS login?'));?></label><input type="checkbox" id="cas_autocreate" name="cas_autocreate" <?php print_unescaped((($_['cas_autocreate'] != false) ? 'checked="checked"' : '')); ?>></p>
	<p><label for="cas_update_user_data"><?php p($l->t('Update user data after login?'));?></label><input type="checkbox" id="cas_update_user_data" name="cas_update_user_data" <?php print_unescaped((($_['cas_update_user_data'] != false) ? 'checked="checked"' : '')); ?>></p>
	<p><label for="cas_protected_groups"><?php p($l->t('Groups that will not be unlinked from the user when sync the CAS server and the owncloud'));?></label><input type="text" id="cas_protected_groups" name="cas_protected_groups" value="<?php p($_['cas_protected_groups']); ?>" /></p> <?php p($l->t('(protected grop are multivalued, use comma to separate the values)')); ?>
        <p><label for="cas_default_group"><?php p($l->t('Default group when autocreating users and no group data was found for the user'));?></label><input type="text" id="cas_default_group" name="cas_default_group" value="<?php p($_['cas_default_group']); ?>"></p>
	<input type="hidden" value="<?php p($_['requesttoken']); ?>" name="requesttoken" />
	</fieldset>
	<fieldset id="casSettings-3">
		<p><label for="cas_email_mapping"><?php p($l->t('Email'));?></label><input type="text" id="cas_email_mapping" name="cas_email_mapping" value="<?php p($_['cas_email_mapping']); ?>" /></p>
		<p><label for="cas_displayName_mapping"><?php p($l->t('Display Name'));?></label><input type="text" id="cas_displayName_mapping" name="cas_displayName_mapping" value="<?php p($_['cas_displayName_mapping']); ?>" /></p>
		<p><label for="cas_group_mapping"><?php p($l->t('Group'));?></label><input type="text" id="cas_group_mapping" name="cas_group_mapping" value="<?php p($_['cas_group_mapping']); ?>" /></p>
	</fieldset>
	<input type="submit" value="Save" />
	</div>

</form>
