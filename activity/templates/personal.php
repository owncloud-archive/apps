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
						<label for="<?php p($activity) ?>_stream">
							<input type="checkbox" id="<?php p($activity) ?>_email" name="<?php p($activity) ?>_email"
								value="1" <?php if ($data['email']): ?> checked="checked"<?php endif; ?> />
						</label>
					</td>
					-->
					<td class="small">
						<label for="<?php p($activity) ?>_stream">
							<input type="checkbox" id="<?php p($activity) ?>_stream" name="<?php p($activity) ?>_stream"
								value="1" <?php if ($data['stream']): ?> checked="checked"<?php endif; ?> />
						</label>
					</td>
					<td><?php p($data['desc']); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
</form>
