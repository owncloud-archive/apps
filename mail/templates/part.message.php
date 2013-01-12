<tr id="mail_message_header">
	<td>
        <img src="<?php echo OCP\Util::imagePath('mail', 'person.png'); ?>" />
	</td>
    <td>
		<?php echo $_['message']['from']; ?>
        <br/>
		<?php echo $_['message']['subject']; ?>
	    <br/>
	    <?php
	    foreach( $_['message']['attachments'] as $a) {
			p($a['filename']." (".OCP\Util::humanFileSize($a['size']).")");
		}
	    ?>
    </td>
    <td>
        <img src="<?php echo OCP\Util::imagePath('mail', 'reply.png'); ?>" />
        <img src="<?php echo OCP\Util::imagePath('mail', 'reply-all.png'); ?>" />
        <img src="<?php echo OCP\Util::imagePath('mail', 'forward.png'); ?>" />
        <br/>
	    <?php p(OCP\Util::formatDate($_['message']['date'])); ?>
	</td>
</tr>
<tr id="mail_message">
	<td colspan="3" class="mail_message_body">
		<?php echo $_['message']['body']; ?>
	</td>
</tr>
