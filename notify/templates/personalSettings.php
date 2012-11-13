<form id="notify">
	<fieldset class="personalblock">
		<p><strong><?php echo $l->t('Notifications'); ?></strong></p>
		<table class="notificationClassesTable">
			<thead>
				<th><input type="checkbox" name="notify-block-all" id="notify-block-all" /><label for="notify-block-all"</th>
				<th><?php echo $l->t('App Name'); ?></th>
				<th><?php echo $l->t('Summary'); ?></th>
			</thead>
			<tbody>
			<?php foreach($_['classes'] as $cid => $class): ?>
				<tr class="notificationClass<?php echo ($class['blocked'] ? ' notify-blocked' : ''); ?>" data-notify-class-id="<?php echo $cid; ?>">
					<td><input type="checkbox" name="notify-block[<?php echo $cid; ?>]" id="notify-block[<?php echo $cid; ?>]"<?php echo ($class['blocked'] ? ' checked="checked"' : ''); ?> /></td>
					<td class="notify-appname"><?php echo $l->t($class['appName']); ?></td>
					<td class="notify-summary"><?php echo $class['summary']; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
</form>
