<div id='notification'></div>
<div id="appsettings" class="popup topright hidden"></div>
<script type='text/javascript'>
	var totalurl = '<?php echo OCP\Util::linkToRemote('carddav'); ?>addressbooks';
	var categories = <?php echo json_encode($_['categories']); ?>;
	var id = '<?php echo $_['id']; ?>';
	var lang = '<?php echo OCP\Config::getUserValue(OCP\USER::getUser(), 'core', 'lang', 'en'); ?>';
</script>
<form class="float" id="file_upload_form" action="<?php echo OCP\Util::linkTo('contacts', 'ajax/uploadphoto.php'); ?>" method="post" enctype="multipart/form-data" target="file_upload_target">
	<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>">
	<input type="hidden" name="id" value="<?php echo $_['id'] ?>">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_['uploadMaxFilesize'] ?>" id="max_upload">
	<input type="hidden" class="max_human_file_size" value="(max <?php echo $_['uploadMaxHumanFilesize']; ?>)">
	<input id="contactphoto_fileupload" type="file" accept="image/*" name="imagefile" />
</form>
<iframe name="file_upload_target" id='file_upload_target' src=""></iframe>
<div id="leftcontent" class="loading">
	<div class="hidden" id="statusbar"></div>
	<div id="groupactions">
		<button class="addgroup"><?php echo $l->t('New Group'); ?></button>
	</div>
	<nav id="grouplist">
	</nav>
	<div id="contacts-settings">
		<ul>
			<li><button class="settings" title="<?php echo $l->t('Settings'); ?>"></button></li>
			<li><button><?php echo $l->t('Import'); ?></button></li>
			<li><button><?php echo $l->t('Export'); ?></button></li>
		</ul>
	</div>
</div>
<div id="contactsheader">
	<input type="checkbox" id="toggle_all" title="<?php echo $l->t('(De-)select all'); ?>" />
	<div class="actions">
		<button class="addcontact"><?php echo $l->t('New Contact'); ?></button>
		<button class="back control" title="<?php echo $l->t('Back'); ?>"><?php echo $l->t('Back'); ?></button>
		<button class="download control" title="<?php echo $l->t('Download Contact'); ?>"></button>
		<button class="delete control" title="<?php echo $l->t('Delete Contact'); ?>"></button>
		<select class="groups control" name="groups">
			<option value="-1" disabled="disabled" selected="selected"><?php echo $l->t('Groups'); ?></option>
		</select>
	</div>
</div>
<div id="rightcontent" class="loading">
	<table id="contactlist">
	</table>
<div class="hidden popup" id="ninjahelp">
	<a class="close" tabindex="0" role="button" title="<?php echo $l->t('Close'); ?>"></a>
	<h2><?php echo $l->t('Keyboard shortcuts'); ?></h2>
	<div class="help-section">
		<h3><?php echo $l->t('Navigation'); ?></h3>
		<dl>
			<dt>j/Down</dt>
			<dd><?php echo $l->t('Next contact in list'); ?></dd>
			<dt>k/Up</dt>
			<dd><?php echo $l->t('Previous contact in list'); ?></dd>
			<dt>o</dt>
			<dd><?php echo $l->t('Expand/collapse current addressbook'); ?></dd>
			<dt>n/PageDown</dt>
			<dd><?php echo $l->t('Next addressbook'); ?></dd>
			<dt>p/PageUp</dt>
			<dd><?php echo $l->t('Previous addressbook'); ?></dd>
		</dl>
	</div>
	<div class="help-section">
		<h3><?php echo $l->t('Actions'); ?></h3>
		<dl>
			<dt>r</dt>
			<dd><?php echo $l->t('Refresh contacts list'); ?></dd>
			<dt>a</dt>
			<dd><?php echo $l->t('Add new contact'); ?></dd>
			<!-- dt>Shift-a</dt>
			<dd><?php echo $l->t('Add new addressbook'); ?></dd -->
			<dt>Shift-Delete</dt>
			<dd><?php echo $l->t('Delete current contact'); ?></dd>
		</dl>
	</div>
</div>
</div>
<script id="cropBoxTemplate" type="text/template">
	<form id="cropform"
		class="coords"
		method="post"
		enctype="multipart/form-data"
		target="crop_target"
		action="<?php echo OCP\Util::linkToAbsolute('contacts', 'ajax/savecrop.php'); ?>">
		<input type="hidden" id="id" name="id" value="{id}" />
		<input type="hidden" id="tmpkey" name="tmpkey" value="{tmpkey}" />
		<fieldset id="coords">
		<input type="hidden" id="x1" name="x1" value="" />
		<input type="hidden" id="y1" name="y1" value="" />
		<input type="hidden" id="x2" name="x2" value="" />
		<input type="hidden" id="y2" name="y2" value="" />
		<input type="hidden" id="w" name="w" value="" />
		<input type="hidden" id="h" name="h" value="" />
		</fieldset>
	</form>
</script>

<script id="addGroupTemplate" type="text/template">
	<div id="dialog-form" title="<?php echo $l->t('Add group'); ?>">
		<fieldset>
			<input type="text" name="name" id="name" />
		</fieldset>
	</div>
</script>

<script id="contactListItemTemplate" type="text/template">
	<tr class="contact" data-id="{id}">
		<td class="name"
			style="background: url('<?php echo OC_Helper::linkToRemoteBase('contactthumbnail'); ?>?id={id}')">
			<input type="checkbox" name="id" value="{id}" /><span class="nametext">{name}</span>
		</td>
		<td class="email">
			<a href="mailto:{email}">{email}</a>
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
<form action="<?php echo OCP\Util::linkTo('contacts', 'index.php'); ?>" method="post" enctype="multipart/form-data">
	<section id="contact" data-id="{id}">
	<ul>
		<li>
			<div id="photowrapper" class="propertycontainer" data-element="photo">
				<ul id="phototools" class="transparent hidden">
					<li><a class="svg delete" title="<?php echo $l->t('Delete current photo'); ?>"></a></li>
					<li><a class="svg edit" title="<?php echo $l->t('Edit current photo'); ?>"></a></li>
					<li><a class="svg upload" title="<?php echo $l->t('Upload new photo'); ?>"></a></li>
					<li><a class="svg cloud" title="<?php echo $l->t('Select photo from ownCloud'); ?>"></a></li>
				</ul>
			</div>
			<a class="favorite" title="<?php echo $l->t('Favorite'); ?>"></a>
			<div class="singleproperties">
			<input data-element="fn" class="fullname value propertycontainer" type="text" name="value" value="{name}" required />
			<a class="action edit"></a>
			<dl class="form">
				<dt data-element="nickname">
					<?php echo $l->t('Nickname'); ?>
				</dt>
				<dd data-element="nickname" class="propertycontainer">
					<input class="value" type="text" name="value" value="{nickname}" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
				<dt data-element="title">
					<?php echo $l->t('Title'); ?>
				</dt>
				<dd data-element="title" class="propertycontainer">
					<input class="value" type="text" name="value" value="{title}" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
				<dt data-element="org">
					<?php echo $l->t('Organization'); ?>
				</dt>
				<dd data-element="org" class="propertycontainer">
					<input class="value" type="text" name="value" value="{org}" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
				<dt data-element="bday">
					<?php echo $l->t('Birthday'); ?>
				</dt>
				<dd data-element="bday" class="propertycontainer">
					<input class="value" type="text" name="value" value="{bday}" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
			</dl>
			</div>
		</li>
		<li>
			<ul class="email propertylist hidden">
			</ul>
		</li>
		<li>
			<ul class="tel propertylist hidden">
			</ul>
		</li>
		<li>
			<ul class="adr propertylist hidden">
			</ul>
		</li>
		<li>
			<ul class="url propertylist hidden">
			</ul>
		</li>
		<li>
			<ul class="impp propertylist hidden">
			</ul>
		</li>
		<li>
			<section class="note" data-element="note">
				<textarea class="value" placeholder="<?php echo $l->t('Notes go here...'); ?>"></textarea>
			</section>
		</li>
	</ul>
	</section>
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
	</footer>
</form>
</script>

<script id="contactDetailsTemplate" class="hidden" type="text/template">
	<div class="email">
		<li data-element="email" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['email_types'], array()) ?>
			</select>
			<input type="checkbox" class="parameter tip" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="email" class="nonempty value" name="value" value="{value}" x-moz-errormessage="<?php echo $l->t('Please specify a valid email address.'); ?>" placeholder="<?php echo $l->t('someone@example.com'); ?>" required />
			<span class="listactions">
				<a class="action mail" title="<?php echo $l->t('Mail to address'); ?>"></a>
				<a role="button" class="action delete" title="<?php echo $l->t('Delete email address'); ?>"></a>
			</span>
		</li>
	</div>
	<div class="tel">
		<li data-element="tel" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['phone_types'], array()) ?>
			</select>
			<input type="checkbox" class="parameter tip" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="tel" class="nonempty value" name="value" value="{value}" placeholder="<?php echo $l->t('Enter phone number'); ?>" required />
			<span class="listactions">
				<a role="button" class="action delete" title="<?php echo $l->t('Delete phone number'); ?>"></a>
			</span>
		</li>
	</div>
	<div class="url">
		<li data-element="url" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['email_types'], array()) ?>
			</select>
			<input type="checkbox" class="parameter tip" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<input type="url" class="nonempty value" name="value" value="{value}" placeholder="http://www.example.com/" required />
			<span class="listactions">
				<a role="button" class="action globe" title="<?php echo $l->t('Go to web site'); ?>">
				<a role="button" class="action delete" title="<?php echo $l->t('Delete URL'); ?>"></a>
			</span>
		</li>
	</div>
	<div class="adr">
		<li data-element="adr" data-checksum="{checksum}" class="propertycontainer">
			<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['adr_types'], array()) ?>
			</select>
			<input type="checkbox" class="parameter tip" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<span class="float adr">{value}</span>
			<span class="listactions">
				<a class="action globe" title="<?php echo $l->t('View on map'); ?>"></a>
				<a class="action edit" title="<?php echo $l->t('Edit address details'); ?>"></a>
				<a class="action delete" title="<?php echo $l->t('Delete address'); ?>"></a>
			</span>
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
			<select class="type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
				<?php echo OCP\html_select_options($_['impp_types'], array()) ?>
			</select>
			<input type="checkbox" class="parameter impp tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<div class="select_wrapper">
			<select class="rtl parameter label impp" data-parameter="X-SERVICE-TYPE" name="parameters[X-SERVICE-TYPE]">
				<?php echo OCP\html_select_options($_['im_protocols'], array()) ?>
			</select>
			</div>
			<input type="text" class="nonempty value" name="value" value="{value}"
					placeholder="<?php echo $l->t('Instant Messenger'); ?>" required />
			<span class="listactions">
				<a role="button" class="action delete" title="<?php echo $l->t('Delete IM'); ?>"></a>
			</span>
		</li>
	</div>
</script>
