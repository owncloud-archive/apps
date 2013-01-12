<?php if (count($_['accounts']) > 0) { ?>
<form>
    <input type="button" id="mail_new_message" value="<?php echo $l->t('New Message'); ?>">
</form>

<?php foreach ($_['accounts'] as $account): ?>
    <h2 class="mail_account"><?php echo $account['name']; ?></h2>
    <ul class="mail_folders" data-account_id="<?php echo $account['id']; ?>">
        <!--	<li>--><?php //echo $account['error']; ?><!--</li>-->
		<?php foreach ($account['folders'] as $folder): ?>
		<?php $unseen = $folder['unseen'] ?>
		<?php $total = $folder['total'] ?>
        <li data-folder_id="<?php echo $folder['id']; ?>">
			<?php echo $folder['name']; ?>
			<?php if ($total > 0) {
			echo " ($unseen/$total)";
		}?>
        </li>
		<?php endforeach; ?>
    </ul>
	<?php endforeach; ?>

<script type="text/javascript">
    $(document).ready(function () {

        // new mail message button handling
        $('#mail_new_message').button().click(function () {
            $('#to').val('');
            $('#subject').val('');
            $('#body').val('');
            $('#mail_editor').dialog("open");
        });

        // Clicking on a folder loads the message list
        $('ul.mail_folders li').live('click', function () {
            var account_id, folder_id;
            account_id = $(this).parent().data('account_id');
            folder_id = $(this).data('folder_id');

            Mail.UI.loadMessages(account_id, folder_id);
        });

        // Clicking on a message loads the entire message
        $('#mail_messages').find('.mail_message_summary').live('click', function () {
            var message_id = $(this).data('message_id');
            Mail.UI.openMessage(message_id);
        });


    });
</script>
<?php } ?>
