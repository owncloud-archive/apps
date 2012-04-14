<div class="mail_message">
	<div class="mail_message_header">
		to <?php echo $_['message']['to']; ?><br>
		from <?php echo $_['message']['from']; ?><br>
		date <?php echo OC_Util::formatDate( $_['message']['date'] ); ?><br>
		subject <?php echo $_['message']['subject']; ?><br>
		size <?php OC_Helper::humanFileSize( $_['message']['subject'] ); ?>
	</div>
	<div class="mail_message_body">
		<?php echo $_['message']['body']; ?>
	</div>
</div>
