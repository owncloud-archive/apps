<form id="contacts-settings">
	<fieldset class="personalblock">
		<?php p($l->t('CardDAV syncing addresses')); ?> (<a href="http://owncloud.org/synchronisation/" target="_blank"><?php p($l->t('more info')); ?></a>)
		<dl>
		<dt><?php p($l->t('Primary address (Kontact et al)')); ?></dt>
		<dd><code><?php print_unescaped(OCP\Util::linkToRemote('carddav')); ?></code></dd>
		<dt><?php p($l->t('iOS/OS X')); ?></dt>
		<dd><code><?php print_unescaped(OCP\Util::linkToRemote('carddav')); ?>principals/<?php p(OCP\USER::getUser()); ?></code>/</dd>
		</dl>
		<div class="addressbooks-settings hidden">
			<?php p($l->t('Addressbooks')); ?>
			<table>
			<?php foreach($_['addressbooks'] as $addressbook) { ?>
			<tr class="addressbook" data-id="<?php p($addressbook['id']) ?>"
				data-uri="<?php p($addressbook['uri']) ?>"
				data-owner="<?php p($addressbook['userid']) ?>"
				>
				<td class="active">
					<?php if($addressbook['permissions'] & OCP\PERMISSION_UPDATE) { ?>
					<input type="checkbox" <?php print_unescaped((($addressbook['active']) == '1' ? ' checked="checked"' : '')); ?> />
					<?php } ?>
				</td>
				<td class="name"><?php p($addressbook['displayname']) ?></td>
				<td class="description"><?php p($addressbook['description']) ?></td>
				<td class="action">
					<a class="svg action globe" title="<?php p($l->t('Show CardDav link')); ?>"></a>
				</td>
				<td class="action">
					<a class="svg action cloud" title="<?php p($l->t('Show read-only VCF link')); ?>"></a>
				</td>
				<td class="action">
					<?php if($addressbook['permissions'] & OCP\PERMISSION_SHARE) { ?>
					<a class="svg action share" data-item-type="addressbook"
						data-item="<?php p($addressbook['id']) ?>"
						data-possible-permissions="<?php p($addressbook['permissions']) ?>"
						title="<?php p($l->t("Share")); ?>"></a>
					<?php } ?>
				</td>
				<td class="action">
					<a class="svg action download" title="<?php p($l->t('Download')); ?>"
						href="<?php print_unescaped(OCP\Util::linkToAbsolute('contacts', 'export.php')); ?>?bookid=<?php p($addressbook['id']) ?>"></a>
				</td>
				<td class="action">
					<?php if($addressbook['permissions'] & OCP\PERMISSION_UPDATE) { ?>
					<a class="svg action edit" title="<?php p($l->t("Edit")); ?>"></a>
					<?php } ?>
				</td>
				<td class="action">
					<?php if($addressbook['permissions'] & OCP\PERMISSION_DELETE) { ?>
					<a class="svg action delete" title="<?php p($l->t("Delete")); ?>"></a>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			</table>
			<div class="actions" style="width: 100%;">
				<input class="active hidden" type="checkbox" />
				<button class="new"><?php p($l->t('New Address Book')) ?></button>
				<input class="name hidden" type="text" autofocus="autofocus" placeholder="<?php p($l->t('Name')); ?>" />
				<input class="description hidden" type="text" placeholder="<?php p($l->t('Description')); ?>" />
				<button class="save hidden"><?php p($l->t('Save')) ?></button>
				<button class="cancel hidden"><?php p($l->t('Cancel')) ?></button>
			</div>
		</div>
		<div style="width: 100%; clear: both;">
			<button class="moreless"><?php p($l->t('More...')) ?></button>
		</div>
	</fieldset>
</form>
