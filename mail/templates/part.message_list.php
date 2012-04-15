<?php foreach( $_['messages'] as $message ): ?>
	<li data-message_id="<?php echo $message['id']; ?>">
		<div class="mail_message_summary">
			date: <?php echo OC_Util::formatDate( $message['date'] ); ?><br>
			from: <?php echo $message['from']; ?><br>
			subject: <?php echo $message['subject']; ?>
		</div>
	</li>
<?php endforeach; ?>
