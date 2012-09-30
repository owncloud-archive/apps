<div id='notification'></div>
<div id="appsettings" class="popup topright hidden"></div>
<script type='text/javascript'>
	var totalurl = '<?php echo OCP\Util::linkToRemote('carddav'); ?>addressbooks';
	var categories = <?php echo json_encode($_['categories']); ?>;
	var id = '<?php echo $_['id']; ?>';
	var lang = '<?php echo OCP\Config::getUserValue(OCP\USER::getUser(), 'core', 'lang', 'en'); ?>';
</script>
<div id="leftcontent">
	<div class="hidden" id="statusbar"></div>
	<nav id="grouplist">
	</nav>
	<div id="uploadprogressbar"></div>
	<div id="bottomcontrols">
			<button class="control newcontact" id="contacts_newcontact" title="<?php echo $l->t('Add Contact'); ?>"></button>
			<button class="control import" title="<?php echo $l->t('Import'); ?>"></button>
			<button class="control settings" title="<?php echo $l->t('Settings'); ?>"></button>
		<form id="import_upload_form" action="<?php echo OCP\Util::linkTo('contacts', 'ajax/uploadimport.php'); ?>" method="post" enctype="multipart/form-data" target="import_upload_target">
			<input class="float" id="import_upload_start" type="file" accept="text/directory,text/vcard,text/x-vcard" name="importfile" />
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_['uploadMaxFilesize'] ?>" id="max_upload">
		</form>
		<iframe name="import_upload_target" id='import_upload_target' src=""></iframe>
	</div>
</div>
<div id="contactsheader">
	<div class="list">
		<input type="checkbox" id="toggle_all" title="<?php echo $l->t('(De-)select all'); ?>" />
		<button class="add"></button>
	</div>
	<div class="single hidden">
		<button class="back" title="<?php echo $l->t('Back'); ?>"><?php echo $l->t('Back'); ?></button>
		<button class="add" title="<?php echo $l->t('Add Contact'); ?>"></button>
		<button class="delete" title="<?php echo $l->t('Delete Contact'); ?>"></button>
	</div>
	<button class="settings"></button>
</div>
<div id="rightcontent" class="loading">
<table>
	<tbody id="contactlist">
	</tbody>
</table>
</div>
<script id="contactListItemTemplate" type="text/template">
	<tr class="contact" data-id="{id}">
		<td class="name" 
			style="background: url('<?php echo OCP\Util::linkTo('contacts', 'thumbnail.php'); ?>?id={id}')">
			<input type="checkbox" name="id" value="{id}" />{name}
		</td>
		<td class="email">
			<span>{email}</span>
			<a class="mailto hidden" title="<?php echo $l->t('Compose mail'); ?>"></a>
		</td>
		<td class="tel">{tel}</td>
		<td class="adr">{adr}</td>
		<td class="categories">{categories}</td>
	</tr>
</script>

<script id="groupListItemTemplate" type="text/template">
	<h3 class="group" data-type="{type}" data-id="{id}">{name} <span class="numcontacts">{num}<span></h3>
</script>

<script id="contactFullTemplate" type="text/template">
	<section id="contact" data-id="{id}">
		<form>
		<section class="singlevalues">
			<figure id="profilepicture" tabindex="1">
				<img src="<?php echo OCP\Util::linkTo('contacts', 'photo.php'); ?>?id={id}" />
			</figure>
			<div style="float: left;" data-element="fn" class="propertycontainer">
			<input class="huge value" type="text" name="value" value="{name}" />
			<dl class="form">
				<dt data-element="nickname" class="hidden">
					<?php echo $l->t('Nickname'); ?>
				</dt>
				<dd data-element="nickname" class="propertycontainer hidden">
					<input class="value" type="text" name="value" value="{nickname}" />
				</dd>
				<dt data-element="title" class="hidden">
					<?php echo $l->t('Title'); ?>
				</dt>
				<dd data-element="title" class="propertycontainer hidden">
					<input class="value" type="text" name="value" value="{title}" />
				</dd>
				<dt data-element="org" class="hidden">
					<?php echo $l->t('Organization'); ?>
				</dt>
				<dd data-element="org" class="propertycontainer hidden">
					<input class="value" type="text" name="value" value="{org}" />
				</dd>
				<dt data-element="bday" class="hidden">
					<?php echo $l->t('Birthday'); ?>
				</dt>
				<dd data-element="bday" class="propertycontainer hidden">
					<input class="value" type="text" name="value" value="{bday}" />
				</dd>
			</dl>
			</div>
			<section class="note" data-element="note">
				<textarea class="value">Some text here</textarea>
			</section>
		</section>
		<section class="multivalues">
			<ul class="email propertylist hidden">
			</ul>
			<ul class="tel propertylist hidden">
			</ul>
			<ul class="adr propertylist hidden">
			</ul>
			<ul class="url propertylist hidden">
			</ul>
			<ul class="impp propertylist hidden">
			</ul>
		</section>
		</form>
		<footer>
		<select id="addproperty">
			<option value=""><?php echo $l->t('Add'); ?></option>
			<option value="ORG"><?php echo $l->t('Organization'); ?></option>
			<option value="NICKNAME"><?php echo $l->t('Nickname'); ?></option>
			<option value="BDAY"><?php echo $l->t('Birthday'); ?></option>
			<option value="TEL"><?php echo $l->t('Phone'); ?></option>
			<option value="EMAIL"><?php echo $l->t('Email'); ?></option>
			<option value="IMPP"><?php echo $l->t('Instant Messaging'); ?></option>
			<option value="ADR"><?php echo $l->t('Address'); ?></option>
			<option value="NOTE"><?php echo $l->t('Note'); ?></option>
			<option value="URL"><?php echo $l->t('Web site'); ?></option>
			<option value="CATEGORIES"><?php echo $l->t('Groups'); ?></option>
		</select>
		Add button here</footer>
	</section>
</script>

<script id="contactDetailsTemplate" class="hidden" type="text/template">
	<div class="email">
		<li data-element="email" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type value" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['email_types'], array()) ?>
			</select>
			<input type="checkbox" class="value tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="email" required="required" class="nonempty value" name="value" value="{value}" x-moz-errormessage="<?php echo $l->t('Please specify a valid email address.'); ?>" placeholder="<?php echo $l->t('someone@example.com'); ?>" />
			<span class="listactions"><a class="action mail" title="<?php echo $l->t('Mail to address'); ?>"></a>
			<a role="button" class="action delete" title="<?php echo $l->t('Delete email address'); ?>"></a></span>
		</li>
	</div>
	<div class="tel">
		<li data-element="tel" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type value" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['phone_types'], array()) ?>
			</select>
			<input type="checkbox" class="value tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="tel" required="required" class="nonempty value" name="value" value="{value}" placeholder="<?php echo $l->t('Enter phone number'); ?>" />
			<span class="listactions">
			<a role="button" class="action delete" title="<?php echo $l->t('Delete phone number'); ?>"></a></span>
		</li>
	</div>
	<div class="url">
		<li data-element="url" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type value" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['email_types'], array()) ?>
			</select>
			<input type="checkbox" class="value tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="url" required="required" class="nonempty value" name="value" value="{value}" placeholder="http://www.example.com/" />
			<span class="listactions">
			<a role="button" class="action globe" title="<?php echo $l->t('Go to web site'); ?>">
			<a role="button" class="action delete" title="<?php echo $l->t('Delete URL'); ?>"></a></span>
		</li>
	</div>
	<div class="adr">
		<li data-element="adr" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type value" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['adr_types'], array()) ?>
			</select>
			<input type="checkbox" class="value tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<span class="float adr">{value}</span>
			<span class="listactions">
			<a class="action globe" title="<?php echo $l->t('View on map'); ?>"></a>
			<a class="action edit" title="<?php echo $l->t('Edit address details'); ?>"></a>
			<a class="action delete" title="<?php echo $l->t('Delete address'); ?>"></a></span>
			<input type="hidden" id="adr_0" name="value[ADR][0]" value="{adr0}" />
			<input type="hidden" id="adr_1" name="value[ADR][1]" value="{adr1}" />
			<input type="hidden" id="adr_2" name="value[ADR][2]" value="{adr2}" />
			<input type="hidden" id="adr_3" name="value[ADR][3]" value="{adr3}" />
			<input type="hidden" id="adr_4" name="value[ADR][4]" value="{adr4}" />
			<input type="hidden" id="adr_5" name="value[ADR][5]" value="{adr5}" />
		</li>
	</div>
	<div class="impp">
		<li data-element="impp" data-checksum="{checksum}" class="propertycontainer">
			<select class="type value" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['impp_types'], array()) ?>
			</select>
			<input type="checkbox" class="contacts_property impp tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<div class="select_wrapper">
			<select class="rtl value label impp" name="parameters[X-SERVICE-TYPE]">
				<?php echo OCP\html_select_options($_['im_protocols'], array()) ?>
			</select>
			</div>
			<input type="text" required="required" class="nonempty contacts_property" name="value" value="{value}"
					placeholder="<?php echo $l->t('Instant Messenger'); ?>" />
			<a role="button" class="action delete" title="<?php echo $l->t('Delete IM'); ?>"></a>
		</li>
	</div>
</script>
