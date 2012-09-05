<div id="appsettings" class="popup bottomleft hidden"></div>
<?php
$id = isset($_['id']) ? $_['id'] : '';
?>
<div id="card" <?php if(!$_['has_contacts']) { echo 'class="hidden"';} ?>>
	<form class="float" id="file_upload_form" action="<?php echo OCP\Util::linkTo('contacts', 'ajax/uploadphoto.php'); ?>" method="post" enctype="multipart/form-data" target="file_upload_target">
		<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>">
		<input type="hidden" name="id" value="<?php echo $_['id'] ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_['uploadMaxFilesize'] ?>" id="max_upload">
		<input type="hidden" class="max_human_file_size" value="(max <?php echo $_['uploadMaxHumanFilesize']; ?>)">
		<input id="file_upload_start" type="file" accept="image/*" name="imagefile" />
	</form>

	<div id="contact_photo">

	<iframe name="file_upload_target" id='file_upload_target' src=""></iframe>
	<div class="tip propertycontainer" id="contacts_details_photo_wrapper" title="<?php echo $l->t('Drop photo to upload'); ?> (max <?php echo $_['uploadMaxHumanFilesize']; ?>)" data-element="PHOTO">
	<ul id="phototools" class="transparent hidden">
		<li><a class="svg delete" title="<?php echo $l->t('Delete current photo'); ?>"></a></li>
		<li><a class="svg edit" title="<?php echo $l->t('Edit current photo'); ?>"></a></li>
		<li><a class="svg upload" title="<?php echo $l->t('Upload new photo'); ?>"></a></li>
		<li><a class="svg cloud" title="<?php echo $l->t('Select photo from ownCloud'); ?>"></a></li>
	</ul>
	</div>
	</div> <!-- contact_photo -->

	<div id="contact_identity" class="contactsection">
	
	<form method="post">
	<input type="hidden" name="id" value="<?php echo $_['id'] ?>">
	<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>">
	<fieldset id="ident" class="contactpart">
	<span class="propertycontainer" data-element="N"><input type="hidden" id="n" class="contacts_property" name="value" value="" /></span>
	<span id="name" class="propertycontainer" data-element="FN">
	<select class="float" id="fn_select" title="<?php echo $l->t('Format custom, Short name, Full name, Reverse or Reverse with comma'); ?>">
	</select><a role="button" id="edit_name" class="action edit" title="<?php echo $l->t('Edit name details'); ?>"></a>
	</span>
	<dl id="identityprops" class="form">
		<dt class="hidden" id="org_label" data-element="ORG"><label for="org"><?php echo $l->t('Organization'); ?></label></dt>
		<dd class="propertycontainer hidden" id="org_value" data-element="ORG"><input id="org" required="required" type="text" class="contacts_property big" name="value" value="" placeholder="<?php echo $l->t('Organization'); ?>" /><a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a></dd>
		<dt class="hidden" id="nickname_label" data-element="NICKNAME"><label for="nickname"><?php echo $l->t('Nickname'); ?></label></dt>
		<dd class="propertycontainer hidden" id="nickname_value" data-element="NICKNAME"><input id="nickname" required="required" type="text" class="contacts_property big" name="value" value="" placeholder="<?php echo $l->t('Enter nickname'); ?>" /><a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a></dd>
		<dt class="hidden" id="url_label" data-element="URL"><label for="url"><?php echo $l->t('Web site'); ?></label></dt>
		<dd class="propertycontainer hidden" id="url_value" data-element="URL"><input id="url" required="required" type="url" class="contacts_property big" name="value" value="" placeholder="<?php echo $l->t('http://www.somesite.com'); ?>" /><a role="button" class="action globe" title="<?php echo $l->t('Go to web site'); ?>"><a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a></dd>
		<dt class="hidden" id="bday_label" data-element="BDAY"><label for="bday"><?php echo $l->t('Birthday'); ?></label></dt>
		<dd class="propertycontainer hidden" id="bday_value" data-element="BDAY"><input id="bday"  required="required" name="value" type="text" class="contacts_property big" value="" placeholder="<?php echo $l->t('dd-mm-yyyy'); ?>" /><a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a></dd>
		<dt class="hidden" id="categories_label" data-element="CATEGORIES"><label for="categories"><?php echo $l->t('Groups'); ?></label></dt>
		<dd class="propertycontainer hidden" id="categories_value" data-element="CATEGORIES"><input id="categories" required="required" type="text" class="contacts_property bold" name="value" value="" placeholder="
<?php echo $l->t('Separate groups with commas'); ?>" />
		<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a><a role="button" class="action edit" title="<?php echo $l->t('Edit groups'); ?>"></a></dd>
	</dl>
	</fieldset>
	</div> <!-- contact_identity -->

	<!-- email addresses -->
	<div id="emails" class="hidden contactsection">
		<ul id="emaillist" class="propertylist">
		<li class="template hidden" data-element="EMAIL">
			<input type="checkbox" class="contacts_property tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="email" required="required" class="nonempty contacts_property" name="value" value="" x-moz-errormessage="<?php echo $l->t('Please specify a valid email address.'); ?>" placeholder="<?php echo $l->t('Enter email address'); ?>" />
			<select multiple="multiple" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['email_types'], array()) ?>
			</select>
			<span class="listactions"><a class="action mail" title="<?php echo $l->t('Mail to address'); ?>"></a>
			<a role="button" class="action delete" title="<?php echo $l->t('Delete email address'); ?>"></a></span></li>
		</ul>
	</div> <!-- email addresses-->

	<!-- Phone numbers -->
	<div id="phones" class="hidden contactsection">
		<ul id="phonelist" class="propertylist">
			<li class="template hidden" data-element="TEL">
			<input type="checkbox" class="contacts_property tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="text" required="required" class="nonempty contacts_property" name="value" value=""
					placeholder="<?php echo $l->t('Enter phone number'); ?>" />
			<select multiple="multiple" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['phone_types'], array()) ?>
			</select>
			<a role="button" class="action delete" title="<?php echo $l->t('Delete phone number'); ?>"></a></li>
		</ul>
	</div> <!-- Phone numbers -->

	<!-- IMPP -->
	<div id="ims" class="hidden contactsection">
		<ul id="imlist" class="propertylist">
			<li class="template hidden" data-element="IMPP">
			<div class="select_wrapper">
			<select class="impp" name="parameters[X-SERVICE-TYPE]">
				<?php echo OCP\html_select_options($_['im_protocols'], array()) ?>
			</select>
			</div>
			<div class="select_wrapper">
			<select class="types" name="parameters[TYPE][]">
				<option></option>
				<?php echo OCP\html_select_options($_['impp_types'], array()) ?>
			</select>
			</div>
			<input type="checkbox" class="contacts_property impp tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="text" required="required" class="nonempty contacts_property" name="value" value=""
					placeholder="<?php echo $l->t('Instant Messenger'); ?>" />
			<a role="button" class="action delete" title="<?php echo $l->t('Delete IM'); ?>"></a></li>
		</ul>
	</div> <!-- IMPP -->

	<!-- Addresses -->
	<div id="addresses" class="hidden contactsection">
		<dl class="addresscard template hidden" data-element="ADR"><dt>
		<input class="adr contacts_property" name="value" type="hidden" value="" />
		<input type="hidden" class="adr_type contacts_property" name="parameters[TYPE][]" value="" />
		<span class="adr_type_label"></span><a class="action globe" title="<?php echo $l->t('View on map'); ?>"></a><a class="action edit" title="<?php echo $l->t('Edit address details'); ?>"></a><a role="button" class="action delete" title="Delete address"></a>
		</dt><dd><ul class="addresslist"></ul></dd></dl>
	</div> <!-- Addresses -->

	<div id="contact_note" class="hidden contactsection">
		<div id="note" class="propertycontainer" data-element="NOTE">
			<textarea class="contacts_property" name="value" required="required" placeholder="<?php echo $l->t('Add notes here.'); ?>" cols="60" wrap="hard"></textarea>
		</div>
	</div> <!-- contact_note -->

	</form>

	<div id="actionbar">
		<div id="contacts_propertymenu">
		<button class="button" id="contacts_propertymenu_button"><?php echo $l->t('Add field'); ?></button>
		<ul id="contacts_propertymenu_dropdown" role="menu" class="hidden">
			<li><a role="menuitem" data-type="ORG"><?php echo $l->t('Organization'); ?></a></li>
			<li><a role="menuitem" data-type="NICKNAME"><?php echo $l->t('Nickname'); ?></a></li>
			<li><a role="menuitem" data-type="BDAY"><?php echo $l->t('Birthday'); ?></a></li>
			<li><a role="menuitem" data-type="TEL"><?php echo $l->t('Phone'); ?></a></li>
			<li><a role="menuitem" data-type="EMAIL"><?php echo $l->t('Email'); ?></a></li>
			<li><a role="menuitem" data-type="IMPP"><?php echo $l->t('Instant Messaging'); ?></a></li>
			<li><a role="menuitem" data-type="ADR"><?php echo $l->t('Address'); ?></a></li>
			<li><a role="menuitem" data-type="NOTE"><?php echo $l->t('Note'); ?></a></li>
			<li><a role="menuitem" data-type="URL"><?php echo $l->t('Web site'); ?></a></li>
			<li><a role="menuitem" data-type="CATEGORIES"><?php echo $l->t('Groups'); ?></a></li>
		</ul>
		</div>
		<button class="svg action" id="contacts_downloadcard" title="<?php echo $l->t('Download contact');?>"></button>
		<button class="svg action" id="contacts_deletecard" title="<?php echo $l->t('Delete contact');?>"></button>
	</div>

</div> <!-- card -->
<div id="edit_photo_dialog" title="Edit photo">
		<div id="edit_photo_dialog_img"></div>
</div>
