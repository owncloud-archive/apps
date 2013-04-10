<form id="notify">
	<fieldset class="personalblock">
		<p><strong><?php p($l->t('Notifications')); ?></strong></p>
		<table class="notificationClassesTable">
			<thead>
				<th><input type="checkbox" name="notify-block-all" id="notify-block-all" /><label for="notify-block-all"</th>
				<th><?php p($l->t('App Name')); ?></th>
				<th><?php p($l->t('Summary')); ?></th>
			</thead>
			<tbody>
			<?php foreach($_['classes'] as $cid => $class): ?>
				<tr class="notificationClass<?php p(($class['blocked'] ? ' notify-blocked' : '')); ?>" data-notify-class-id="<?php p($cid); ?>">
					<td><input type="checkbox" name="notify-block[<?php p($cid); ?>]" id="notify-block[<?php p($cid); ?>]"<?php p(($class['blocked'] ? ' checked="checked"' : '')); ?> /></td>
					<td class="notify-appname"><?php p($l->t($class['appName'])); ?></td>
					<td class="notify-summary"><?php p($class['summary']); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
</form>
