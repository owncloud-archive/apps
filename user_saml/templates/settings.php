
<link rel="stylesheet" type="text/css" href="../apps/user_saml/css/saml.css" />

<form id="saml" action="#" method="post">
	<div id="samlSettings" class="personalblock">
    <strong><?php echo $l->t('SAML Authentication backend');?></strong>
	<ul>
		<li><a href="#samlSettings-1"><?php echo $l->t('Basic');?></a></li>
        <li><a href="#samlSettings-2"><?php echo $l->t('Mapping');?></a></li>
	</ul>
	<fieldset id="samlSettings-1">
		<p><label for="saml_ssp_path"><?php echo $l->t('SimpleSAMLphp path');?></label><input type="text" id="saml_ssp_path" name="saml_ssp_path" value="<?php echo $_['saml_ssp_path']; ?>"></p>
        <p><label for="saml_sp_source"><?php echo $l->t('SimpleSAMLphp SP source');?></label><input type="text" id="saml_sp_source" name="saml_sp_source" value="<?php echo $_['saml_sp_source']; ?>"></p>
        <p><label for="saml_autocreate"><?php echo $l->t('Autocreate user after saml login?');?></label><input type="checkbox" id="saml_autocreate" name="saml_autocreate" <?php echo (($_['saml_autocreate'] != false) ? 'checked="checked"' : ''); ?>"></p>
       <p><label for="saml_update_user_data"><?php echo $l->t('Update user data after login?');?></label><input type="checkbox" id="saml_update_user_data" name="saml_update_user_data" <?php echo (($_['saml_update_user_data'] != false) ? 'checked="checked"' : ''); ?>"></p>
       <p><label for="saml_protected_groups"><?php echo $l->t('Groups that will not be unlinked from the user when sync the IdP and the owncloud');?></label><input type="text" id="saml_protected_groups" name="saml_protected_groups" value="<?php echo $_['saml_protected_groups']; ?>" /></p> <?php echo $l->t('(protected grop are multivalued, use comma to separate the values)'); ?>
        <p><label for="saml_default_group"><?php echo $l->t('Default group when autocreating users and not group data found for the user');?></label><input type="text" id="saml_default_group" name="saml_default_group" value="<?php echo $_['saml_default_group']; ?>"></p>
	</fieldset>
	<fieldset id="samlSettings-2">
		<p><label for="saml_username_mapping"><?php echo $l->t('Username');?></label><input type="text" id="saml_username_mapping" name="saml_username_mapping" value="<?php echo $_['saml_username_mapping']; ?>" /></p>
		<p><label for="saml_email_mapping"><?php echo $l->t('Email');?></label><input type="text" id="saml_email_mapping" name="saml_email_mapping" value="<?php echo $_['saml_email_mapping']; ?>" /></p>
		<p><label for="saml_group_mapping"><?php echo $l->t('Group');?></label><input type="text" id="saml_group_mapping" name="saml_group_mapping" value="<?php echo $_['saml_group_mapping']; ?>" /></p>
	</fieldset>
	<input type="submit" value="Save" />
	</div>

</form>
