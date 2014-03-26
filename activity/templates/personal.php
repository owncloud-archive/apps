<form id="activity_notifications">
	<fieldset class="personalblock">
	<h2><?php p($l->t('Notifications')); ?></h2>
		<table class="grid activitysettings">
			<thead>
				<tr>
					<!-- <th><?php p($l->t('Mail')); ?></th> -->
					<th class="small"><?php p($l->t('Stream')); ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($_['activities'] as $activity => $data): ?>
				<tr>
					<!-- Email is not available yet
					<td class="small">
						<input type="checkbox" name="<?php p($activity) ?>_email" value="1"
							<?php if ($data['email']): ?> checked="checked"<?php endif; ?> />
					</td>
					-->
					<td class="small">
						<input type="checkbox" name="<?php p($activity) ?>_stream" value="1"
							<?php if ($data['stream']): ?> checked="checked"<?php endif; ?> />
					</td>
					<td><?php p($data['desc']); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
</form>
