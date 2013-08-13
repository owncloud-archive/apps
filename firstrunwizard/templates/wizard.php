<div id="firstrunwizard">

<?php $defaults = new OCP\Defaults(); ?>

<a id="closeWizard" class="close">
	<img class="svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>">
</a>

<h1><?php p($l->t('Welcome to ownCloud'));?></h1>
<?php if (OC_Util::getEditionString() === ''): ?>
<p><?php p($l->t('Your personal web services. All your files, contacts, calendar and more, in one place.'));?></p>
<?php else: ?>
<p><?php p($defaults->getSlogan()); ?></p>
<?php endif; ?>


<h2><?php p($l->t('Get the apps to sync your files'));?></h2>
<a href="<?php p($_['clients']['desktop']); ?>">
	<img src="<?php print_unescaped(OCP\Util::imagePath('core', 'desktopapp.png')); ?>" />
</a>
<a href="<?php p($_['clients']['android']); ?>">
	<img src="<?php print_unescaped(OCP\Util::imagePath('core', 'googleplay.png')); ?>" />
</a>
<a href="<?php p($_['clients']['ios']); ?>">
	<img src="<?php print_unescaped(OCP\Util::imagePath('core', 'appstore.png')); ?>" />
</a>

<h2><?php p($l->t('Connect your desktop apps to ownCloud'));?></h2>
<?php if (OC_Util::getEditionString() === ''): ?>
<a class="button" href="http://doc.owncloud.org/server/5.0/user_manual/calendars.html#synchronising-calendars-with-caldav">
	<img class="appsmall appsmall-calendar svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'places/calendar-dark.svg')); ?>" /> <?php p($l->t('Connect your Calendar'));?>
</a>
<a class="button" href="http://doc.owncloud.org/server/5.0/user_manual/contacts.html#keeping-your-address-book-in-sync">
	<img class="appsmall appsmall-contacts svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'places/contacts-dark.svg')); ?>" /> <?php p($l->t('Connect your Contacts'));?>
</a>
<?php endif; ?>
<a class="button" href="<?php p($defaults->getDocBaseUrl()); ?>/server/5.0/user_manual/files/files.html">
	<img class="appsmall svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'places/folder.svg')); ?>" /> <?php p($l->t('Access files via WebDAV'));?>
</a>

<p class="footnote">
<?php if (OC_Util::getEditionString() === ''): ?>
<?php print_unescaped($l->t('There’s more information in the <a href="http://doc.owncloud.org/server/5.0/user_manual/">documentation</a> and on our <a href="http://owncloud.org">website</a>.')); ?><br>
<?php print_unescaped($l->t('If you like ownCloud, <a href="mailto:?subject=ownCloud&body=ownCloud is a great open software to sync and share your files. You can freely get it from http://owncloud.org">recommend it to your friends</a>!')); ?></p>
<?php else: ?>
© 2013 <a href="https://owncloud.com" target="_blank">ownCloud Inc.</a>
<?php endif; ?>
</p>

</div>
