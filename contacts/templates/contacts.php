<div id='notification'></div>
<div id="appsettings" class="popup topright hidden"></div>
<script type='text/javascript'>
	var totalurl = '<?php echo OCP\Util::linkToRemote('carddav'); ?>addressbooks';
	var categories = <?php echo json_encode($_['categories']); ?>;
	var id = '<?php echo $_['id']; ?>';
	var lang = '<?php echo OCP\Config::getUserValue(OCP\USER::getUser(), 'core', 'lang', 'en'); ?>';
</script>
<div id="leftcontent" class="loading">
	<div class="hidden" id="statusbar"></div>
	<nav id="grouplist">
	</nav>
</div>
<div id="contactsheader">
	<input type="checkbox" id="toggle_all" title="<?php echo $l->t('(De-)select all'); ?>" />
	<div class="actions">
		<button class="back control" title="<?php echo $l->t('Back'); ?>"><?php echo $l->t('Back'); ?></button>
		<button class="add control" title="<?php echo $l->t('Add Contact'); ?>"></button>
		<button class="download control" title="<?php echo $l->t('Download Contact'); ?>"></button>
		<button class="delete control" title="<?php echo $l->t('Delete Contact'); ?>"></button>
		<select class="groups control" name="groups">
			<option value="-1" disabled="disabled" selected="selected"><?php echo $l->t('Groups'); ?></option>
		</select>
	</div>
	<button class="settings control"></button>
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
<form action="<?php echo OCP\Util::linkTo('contacts', 'index.php'); ?>" method="post" enctype="multipart/form-data">
	<section id="contact" data-id="{id}">
	<ul>
		<li>
			<img class="contactphoto" src="<?php echo OCP\Util::linkTo('contacts', 'photo.php'); ?>?id={id}" />
			<div>
			<input class="fullname value propertycontainer" data-element="fn" type="text" name="value" value="{name}" />
			<dl class="form">
				<dt data-element="nickname">
					<?php echo $l->t('Nickname'); ?>
				</dt>
				<dd data-element="nickname" class="propertycontainer">
					<input class="value" type="text" name="value" value="{nickname}" />
				</dd>
				<dt data-element="title">
					<?php echo $l->t('Title'); ?>
				</dt>
				<dd data-element="title" class="propertycontainer">
					<input class="value" type="text" name="value" value="{title}" />
				</dd>
				<dt data-element="org">
					<?php echo $l->t('Organization'); ?>
				</dt>
				<dd data-element="org" class="propertycontainer">
					<input class="value" type="text" name="value" value="{org}" />
				</dd>
				<dt data-element="bday">
					<?php echo $l->t('Birthday'); ?>
				</dt>
				<dd data-element="bday" class="propertycontainer">
					<input class="value" type="text" name="value" value="{bday}" />
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
			<input type="checkbox" class="value impp tip" name="parameters[TYPE][]" value="PREF" title="<?php echo $l->t('Preferred'); ?>" />
			<div class="select_wrapper">
			<select class="rtl value label impp" name="parameters[X-SERVICE-TYPE]">
				<?php echo OCP\html_select_options($_['im_protocols'], array()) ?>
			</select>
			</div>
			<input type="text" required="required" class="nonempty value" name="value" value="{value}"
					placeholder="<?php echo $l->t('Instant Messenger'); ?>" />
			<a role="button" class="action delete" title="<?php echo $l->t('Delete IM'); ?>"></a>
		</li>
	</div>
</script>
