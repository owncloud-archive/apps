<?php foreach( $_['messages'] as $message ): ?>
	<tr class="mail_message_summary" data-message_id="<?php echo $message['id']; ?>">
		<td class="mail_message_summary_from"><?php echo $message['from']; ?></td>
		<td class="mail_message_summary_subject"><?php echo $message['subject']; ?></td>
		<td class="mail_message_summary_date"><?php echo OCP\Util::formatDate( $message['date'] ); ?></td>
	</tr>
<?php endforeach; ?>
