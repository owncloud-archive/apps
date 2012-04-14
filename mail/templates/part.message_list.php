<?php foreach( $_['messages'] as $message ): ?>
	<li data-message_id="<?php echo $message['id']; ?>">
		<div class="mail_message_summary">
			date: $message['date']->getTimestamp());<br>
			from: <?php echo $message['from']; ?><br>
			subject: <?php echo $message['subject']; ?>
		</div>
	</li>
<?php endforeach; ?>
