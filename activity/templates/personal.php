<form id="activity_notifications">
	<fieldset class="personalblock">
	<h2><?php p($l->t('Notifications')); ?></h2>
		<table class="grid" style="max-width: 550px;">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<!-- <th><?php p($l->t('Mail')); ?></th> -->
					<th><?php p($l->t('Stream')); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($_['activities'] as $activity => $data): ?>
				<tr>
					<td><?php p($data['desc']); ?></td>
					<!-- Email is not available yet
					<td>
						<input type="checkbox" name="<?php p($activity) ?>_email" value="1"
							<?php if ($data['email']): ?> checked="checked"<?php endif; ?> />
					</td>
					-->
					<td>
						<input type="checkbox" name="<?php p($activity) ?>_stream" value="1"
							<?php if ($data['stream']): ?> checked="checked"<?php endif; ?> />
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
</form>
