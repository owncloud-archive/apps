<div id="firstrunwizard">

<a id="closeWizard" class="close">
	<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/delete.svg'); ?>">
</a>

<h1><?php echo $l->t('Welcome to ownCloud');?></h1>
<p><?php echo $l->t('Your personal web services. All your files, contacts, calendar and more, in one place.');?></p>


<h2><?php echo $l->t('Get the apps to sync your files');?></h2>
<a href="<?php echo $_['clients']['desktop']; ?>">
	<img src="<?php echo OCP\Util::imagePath('core', 'desktopapp.png'); ?>" />
</a>
<a href="<?php echo $_['clients']['android']; ?>">
	<img src="<?php echo OCP\Util::imagePath('core', 'googleplay.png'); ?>" />
</a>
<a href="<?php echo $_['clients']['ios']; ?>">
	<img src="<?php echo OCP\Util::imagePath('core', 'appstore.png'); ?>" />
</a>


<h2><?php echo $l->t('Connect your desktop apps to ownCloud');?></h2>
<a class="button" href="http://doc.owncloud.org/server/5.0/user_manual/calendars.html#synchronising-calendars-with-caldav">
	<img class="appsmall appsmall-calendar" src="<?php echo OCP\Util::imagePath('core', 'places/calendar-dark.svg'); ?>" /> <?php echo $l->t('Connect your Calendar');?>
</a>
<a class="button" href="http://doc.owncloud.org/server/5.0/user_manual/contacts.html#keeping-your-address-book-in-sync">
	<img class="appsmall appsmall-contacts" src="<?php echo OCP\Util::imagePath('core', 'places/contacts-dark.svg'); ?>" /> <?php echo $l->t('Connect your Contacts');?>
</a>
<a class="button" href="http://doc.owncloud.org/server/5.0/user_manual/connecting_webdav.html">
	<img class="appsmall" src="<?php echo OCP\Util::imagePath('core', 'places/folder.svg'); ?>" /> <?php echo $l->t('Access files via WebDAV');?>
</a>

<p class="footnote"><?php echo $l->t('There’s more information in the <a href="http://doc.owncloud.org/server/5.0/user_manual/">documentation</a> and on our <a href="http://owncloud.org">website</a>.'); ?><br>
<?php echo $l->t('If you like ownCloud, <a href="mailto:?subject=ownCloud&body=ownCloud is a great open software to sync and share your files. You can freely get it from http://owncloud.org">recommend it to your friends</a>!'); ?></p>


</div>
