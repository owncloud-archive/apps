<div id="appsettings" class="popup bottomleft hidden"></div>
<div id="firstrun" <?php if($_['has_contacts']) { echo 'class="hidden"';} ?>>
	<?php echo $l->t('<h3>You have no contacts in your addressbook.</h3>'
		. '<p>You can import VCF files by dragging them to the contacts list and either '
		. 'drop them on an addressbook to import into it, or on an empty spot to create '
		. 'a new addressbook and import into that.<br />You can also import by clicking '
		. 'on the import button at the bottom of the list.</p>') ?>
	<div id="selections">
		<input type="button" value="<?php echo $l->t('Add contact') ?>" onclick="OC.Contacts.Card.editNew()" />
	</div>
</div>
