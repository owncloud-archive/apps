<?php foreach( $_['messages'] as $message ): ?>
	<tr class="mail_message_summary" data-message_id="<?php echo $message['id']; ?>">
		<td class="mail_message_summary_from <?php if($message['flags']['unseen'] == "1") echo('unseen'); ?>"><?php echo $message['from']; ?></td>
		<td class="mail_message_summary_subject <?php if($message['flags']['unseen'] == "1") echo('unseen'); ?>"><?php echo $message['subject']; ?></td>
		<td class="mail_message_summary_date <?php if($message['flags']['unseen'] == "1") echo('unseen'); ?>"><?php echo OCP\Util::formatDate( $message['date'] ); ?></td>
	</tr>
<?php endforeach; ?>
