<ul id="mail_messages" data-folder_id="<?php echo $_['folder_id']; ?>" data-account_id="<?php echo $_['account_id']; ?>">
	<?php foreach( $_['messages'] as $message ): ?>
		<li data-message_id="<?php echo $message['id']; ?>">
			<div class="mail_message_summary">
				date: <?php echo OC_Util::formatDate( $message['date']->getTimestamp()); ?><br>
				from: <?php echo $message['from']; ?><br>
				subject: <?php echo $message['subject']; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
<div id="mail_messagelist_bottom"></div>
