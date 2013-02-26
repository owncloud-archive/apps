<form class="float" id="file_upload_form" action="<?php echo OCP\Util::linkTo('contacts', 'ajax/uploadphoto.php'); ?>" method="post" enctype="multipart/form-data" target="file_upload_target">
	<input type="hidden" name="requesttoken" value="<?php echo $_['requesttoken'] ?>">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_['uploadMaxFilesize'] ?>" id="max_upload">
	<input type="hidden" class="max_human_file_size" value="(max <?php echo $_['uploadMaxHumanFilesize']; ?>)">
	<input id="contactphoto_fileupload" type="file" accept="image/*" name="imagefile" />
</form>
<iframe name="file_upload_target" id='file_upload_target' src=""></iframe>
<div id="groupsheader">
	<button class="addgroup icon-plus text"><?php echo $l->t('New Group'); ?></button>
</div>
<div id="leftcontent" class="loading">
	<nav id="grouplist">
	</nav>
	<div id="contacts-settings">
			<h3 class="settings action text" tabindex="0" role="button" title="<?php echo $l->t('Settings'); ?>"></h3>
			<h2 data-id="addressbooks" tabindex="0" role="button"><?php echo $l->t('Address books'); ?></h3>
				<ul class="hidden">
				</ul>
			<h2 data-id="import" tabindex="0" role="button"><?php echo $l->t('Import'); ?></h3>
				<ul class="hidden">
					<li class="import-upload">
						<form id="import_upload_form" action="<?php echo OCP\Util::linkTo('contacts', 'ajax/uploadimport.php'); ?>" method="post" enctype="multipart/form-data" target="import_upload_target">
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_['uploadMaxFilesize'] ?>" id="max_upload">
						<label for="import_fileupload"><?php echo $l->t('Select files to import'); ?>
							<button class="import-upload-button" title="<?php echo $l->t('Select files'); ?>"></button>
						</label>
						<input id="import_fileupload" type="file" accept="text/vcard,text/x-vcard,text/directory" multiple="multiple" name="importfile" />
						</form>
						<iframe name="import_upload_target" id='import_upload_target' src=""></iframe>
					</li>
					<li class="import-select hidden"><label><?php echo $l->t('Import into:'); ?></label></li>
					<li class="import-select hidden">
						<select id="import_into" title="<?php echo $l->t('Import into:'); ?>">
						</select>
					<button class="doImport"><?php echo $l->t('OK'); ?></button>
					</li>
					<li>
						<label id="import-status-text"></label>
						<div id="import-progress"></div>
					</li>
				</ul>
	</div>
</div>
<div id="contactsheader">
	<input type="checkbox" id="toggle_all" title="<?php echo $l->t('(De-)select all'); ?>" />
	<div class="actions">
		<button class="add svg icon-plus action text"><?php echo $l->t('New Contact'); ?></button>
		<button class="download svg action text"><?php echo $l->t('Download Contact(s)'); ?></button>
		<select class="groups svg action text button" name="groups">
			<option value="-1" disabled="disabled" selected="selected"><?php echo $l->t('Groups'); ?></option>
		</select>
		<button class="favorite action svg inactive control" title="<?php echo $l->t('Favorite'); ?>"></button>
		<a class="delete action" title="<?php echo $l->t('Delete Contact'); ?>"></a>
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
	<div id="firstrun" class="hidden">
		<?php echo $l->t('<h3>You have no contacts in your addressbook.</h3>'
			. '<p>Add a new contact or import existing contacts from a VCF file.</p>') ?>
		<div id="selections">
			<button class="addcontact icon-plus text"><?php echo $l->t('Add contact') ?></button>
			<button class="import icon text"><?php echo $l->t('Import') ?></button>
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
			<a class="svg mailto hidden" title="<?php echo $l->t('Compose mail'); ?>"></a>
		</td>
		<td class="tel">{tel}</td>
		<td class="adr">{adr}</td>
		<td class="categories">{categories}</td>
	</tr>
</script>

<script id="groupListItemTemplate" type="text/template">
	<h3 class="group" data-type="{type}" data-id="{id}">
		{name}
		<a class="action delete tooltipped rightwards" title="<?php echo $l->t('Delete group'); ?>"></a>
		<span class="action numcontacts">{num}</span>
	</h3>
</script>

<script id="contactFullTemplate" type="text/template">
<form action="<?php echo OCP\Util::linkTo('contacts', 'index.php'); ?>" method="post" enctype="multipart/form-data">
	<section id="contact" data-id="{id}">
	<span class="arrow"></span>
	<ul>
		<li>
			<div id="photowrapper" class="propertycontainer" data-element="photo">
				<ul id="phototools" class="transparent hidden">
					<li><a class="action delete" title="<?php echo $l->t('Delete current photo'); ?>"></a></li>
					<li><a class="action edit" title="<?php echo $l->t('Edit current photo'); ?>"></a></li>
					<li><a class="action upload" title="<?php echo $l->t('Upload new photo'); ?>"></a></li>
					<li><a class="action cloud" title="<?php echo $l->t('Select photo from ownCloud'); ?>"></a></li>
				</ul>
				<a class="favorite action {favorite}"></a>
			</div>
			<div class="singleproperties">
			<input data-element="fn" class="fullname value propertycontainer" type="text" name="value" value="{name}" required />
			<a class="action edit"></a>
 			<fieldset class="n hidden editor propertycontainer" data-element="n">
			<ul>
				<li>
					<input class="value tooltipped rightwards onfocus" type="text" id="n_1" name="value[1]" value="{n1}" 
						placeholder="<?php echo $l->t('First name'); ?>" 
						title="<?php echo $l->t('First name'); ?>" />
				</li>
				<li>
					<input class="value tooltipped rightwards onfocus" type="text" id="n_2" name="value[2]" value="{n2}" 
						placeholder="<?php echo $l->t('Additional names'); ?>" 
						title="<?php echo $l->t('Additional names'); ?>" />
				</li>
				<li>
					<input class="value tooltipped rightwards onfocus" type="text" id="n_0" name="value[0]" value="{n0}" 
						placeholder="<?php echo $l->t('Last name'); ?>" 
						title="<?php echo $l->t('Last name'); ?>" />
				</li>
			</ul>
			<input class="value" type="hidden" id="n_3" name="value[3]" value="{n3}" />
			<input class="value" type="hidden" id="n_4" name="value[4]" value="{n4}" />
			</fieldset>
			<div class="groupscontainer propertycontainer" data-element="categories">
				<select id="contactgroups" title="<?php echo $l->t('Select groups'); ?>" name="value" multiple></select>
			</div>
			<dl class="form">
				<dt data-element="nickname">
					<?php echo $l->t('Nickname'); ?>
				</dt>
				<dd data-element="nickname" class="propertycontainer">
					<input class="value tooltipped rightwards onfocus" type="text" name="value" value="{nickname}" title="<?php echo $l->t('Enter nickname'); ?>" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
				<dt data-element="title">
					<?php echo $l->t('Title'); ?>
				</dt>
				<dd data-element="title" class="propertycontainer">
					<input class="value tooltipped rightwards onfocus" type="text" name="value" value="{title}" title="<?php echo $l->t('Enter title'); ?>" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
				<dt data-element="org">
					<?php echo $l->t('Organization'); ?>
				</dt>
				<dd data-element="org" class="propertycontainer">
					<input class="value tooltipped rightwards onfocus" type="text" name="value" value="{org}" title="<?php echo $l->t('Enter organization'); ?>" required />
					<a role="button" class="action delete" title="<?php echo $l->t('Delete'); ?>"></a>
				</dd>
				<dt data-element="bday">
					<?php echo $l->t('Birthday'); ?>
				</dt>
				<dd data-element="bday" class="propertycontainer">
					<input class="value tooltipped rightwards onfocus" type="text" name="value" value="{bday}" required />
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
			<section class="note propertycontainer" data-element="note">
				<textarea class="value" placeholder="<?php echo $l->t('Notes go here...'); ?>">{note}</textarea>
			</section>
		</li>
	</ul>
	<footer>
		<button class="cancel action text tooltipped downwards" title="<?php echo $l->t('Cancel'); ?>"><?php echo $l->t('Cancel'); ?></button>
		<button class="close text tooltipped downwards" title="<?php echo $l->t('Close'); ?>"><?php echo $l->t('Close'); ?></button>
		<button class="export action text tooltipped downwards" title="<?php echo $l->t('Export as VCF'); ?>"><?php echo $l->t('Download'); ?></button>
		<select class="add action text icon-plus button" id="addproperty">
			<option value=""><?php echo $l->t('Add'); ?></option>
			<option value="ORG"><?php echo $l->t('Organization'); ?></option>
			<option value="TITLE"><?php echo $l->t('Title'); ?></option>
			<option value="NICKNAME"><?php echo $l->t('Nickname'); ?></option>
			<option value="BDAY"><?php echo $l->t('Birthday'); ?></option>
			<option value="TEL"><?php echo $l->t('Phone'); ?></option>
			<option value="EMAIL"><?php echo $l->t('Email'); ?></option>
			<option value="IMPP"><?php echo $l->t('Instant Messaging'); ?></option>
			<option value="ADR"><?php echo $l->t('Address'); ?></option>
			<option value="NOTE"><?php echo $l->t('Note'); ?></option>
			<option value="URL"><?php echo $l->t('Web site'); ?></option>
		</select>
		<button class="delete action text float right tooltipped downwards" title="<?php echo $l->t('Delete contact'); ?>"><?php echo $l->t('Delete'); ?></button>
	</footer>
	</section>
</form>
</script>

<script id="contactDetailsTemplate" class="hidden" type="text/template">
	<div class="email">
		<li data-element="email" data-checksum="{checksum}" class="propertycontainer">
			<span class="parameters">
				<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
					<?php echo OCP\html_select_options($_['email_types'], array()) ?>
				</select>
				<input type="checkbox" class="parameter tooltipped rightwards" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			</span>
			<input type="email" class="nonempty value" name="value" value="{value}" x-moz-errormessage="<?php echo $l->t('Please specify a valid email address.'); ?>" placeholder="<?php echo $l->t('someone@example.com'); ?>" required />
			<span class="listactions">
				<a class="action mail tooltipped leftwards" title="<?php echo $l->t('Mail to address'); ?>"></a>
				<a role="button" class="action delete tooltipped leftwards" title="<?php echo $l->t('Delete email address'); ?>"></a>
			</span>
		</li>
	</div>
	<div class="tel">
		<li data-element="tel" data-checksum="{checksum}" class="propertycontainer">
			<span class="parameters">
				<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
					<?php echo OCP\html_select_options($_['phone_types'], array()) ?>
				</select>
				<input type="checkbox" class="parameter tooltipped rightwards" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			</span>
			<input type="tel" class="nonempty value" name="value" value="{value}" placeholder="<?php echo $l->t('Enter phone number'); ?>" required />
			<span class="listactions">
				<a role="button" class="action delete tooltipped leftwards" title="<?php echo $l->t('Delete phone number'); ?>"></a>
			</span>
		</li>
	</div>
	<div class="url">
		<li data-element="url" data-checksum="{checksum}" class="propertycontainer">
			<span class="parameters">
				<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
					<?php echo OCP\html_select_options($_['email_types'], array()) ?>
				</select>
				<input type="checkbox" class="parameter tooltipped rightwards" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			</span>
			<input type="url" class="nonempty value" name="value" value="{value}" placeholder="http://www.example.com/" required />
			<span class="listactions">
				<a role="button" class="action globe tooltipped leftwards" title="<?php echo $l->t('Go to web site'); ?>">
				<a role="button" class="action delete tooltipped leftwards" title="<?php echo $l->t('Delete URL'); ?>"></a>
			</span>
		</li>
	</div>
	<div class="adr">
		<li data-element="adr" data-checksum="{checksum}" data-lang="<?php echo OCP\Config::getUserValue(OCP\USER::getUser(), 'core', 'lang', 'en'); ?>" class="propertycontainer">
			<span class="float display">
				<label class="meta parameters"></label>
				<span class="adr">{value}</span>
			</span>
			<span class="listactions">
				<a class="action globe tooltipped leftwards" title="<?php echo $l->t('View on map'); ?>"></a>
				<a class="action delete tooltipped leftwards" title="<?php echo $l->t('Delete address'); ?>"></a>
			</span>
			<fieldset class="adr hidden editor">
				<ul>
				<li>
					<select class="rtl type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
						<?php echo OCP\html_select_options($_['adr_types'], array()) ?>
					</select>
					<input type="checkbox" id="adr_pref_{idx}" class="parameter tooltipped downwards" data-parameter="TYPE" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" /><label for="adr_pref_{idx}"><?php echo $l->t('Preferred'); ?></label>
				</li>
				<li>
					<input class="value stradr tooltipped rightwards onfocus" type="text" id="adr_2" name="value[2]" value="{adr2}" 
					placeholder="<?php echo $l->t('1 Main Street'); ?>"
					title="<?php echo $l->t('Street address'); ?>" />
				</li>
				<li>
					<input class="value zip tooltipped rightwards onfocus" type="text" id="adr_5" name="value[5]" value="{adr5}" 
						placeholder="<?php echo $l->t('12345'); ?>"
						title="<?php echo $l->t('Postal code'); ?>" />
					<input class="value city tooltipped rightwards onfocus" type="text" id="adr_3" name="value[3]" value="{adr3}" 
						placeholder="<?php echo $l->t('Your city'); ?>"
						title="<?php echo $l->t('City'); ?>" />
				</li>
				<li>
					<input class="value region tooltipped rightwards onfocus" type="text" id="adr_4" name="value[4]" value="{adr4}" 
						placeholder="<?php echo $l->t('Some region'); ?>"
						title="<?php echo $l->t('State or province'); ?>" />
				</li>
				<li>
					<input class="value country tooltipped rightwards onfocus" type="text" id="adr_6" name="value[6]" value="{adr6}" 
						placeholder="<?php echo $l->t('Your country'); ?>"
						title="<?php echo $l->t('Country'); ?>" />
				</li>
			</ul>
			<input class="value pobox" type="hidden" id="adr_0" name="value[0]" value="{adr0}" />
			<input class="value extadr" type="hidden" id="adr_1" name="value[1]" value="{adr1}" />
			</fieldset>
		</li>
	</div>
	<div class="impp">
		<li data-element="impp" data-checksum="{checksum}" class="propertycontainer">
			<span class="parameters">
				<select class="type parameter" data-parameter="TYPE" name="parameters[TYPE][]">
					<?php echo OCP\html_select_options($_['impp_types'], array()) ?>
				</select>
				<input type="checkbox" class="parameter impp tooltipped downwards" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			</span>
				<div class="select_wrapper">
				<select class="ltr parameter label impp" data-parameter="X-SERVICE-TYPE" name="parameters[X-SERVICE-TYPE]">
					<?php echo OCP\html_select_options($_['im_protocols'], array()) ?>
				</select>
				</div>
			<input type="text" class="nonempty value" name="value" value="{value}"
					placeholder="<?php echo $l->t('Instant Messenger'); ?>" required />
			<span class="listactions">
				<a role="button" class="action delete tooltipped leftwards" title="<?php echo $l->t('Delete IM'); ?>"></a>
			</span>
		</li>
	</div>
</script>

<script id="addressbookTemplate" class="hidden" type="text/template">
<li data-id="{id}">
	<label class="float">{displayname}</label>
	<span class="actions">
	<a title="<?php echo $l->t('Share'); ?>" class="share action" data-possible-permissions="{permissions}" data-item="{id}" data-item-type="addressbook"></a>
	<a title="<?php echo $l->t('Export'); ?>" class="download action" href="<?php echo OCP\Util::linkTo('contacts', 'export.php'); ?>?bookid={id}"></a>
	<a  title="<?php echo $l->t('CardDAV link'); ?>" class="globe action"></a>
	<a  title="<?php echo $l->t('Delete'); ?>" class="delete action"></a>
</span></li>
</script>
