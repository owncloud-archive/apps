<?php
$accounts = OCA\Mail\App::getFolders(OCP\User::getUser());
if (count($accounts) == 0) {
	echo $this->inc("part.no-accounts");
} else {
	?>

<div id="leftcontent" class="leftcontent">
    <div id="mail-folders"></div>
    <div id="bottomcontrols">
        <button class="control settings" title="<?php echo $l->t('Settings'); ?>"></button>
    </div>
</div>
<div id="rightcontent" class="rightcontent">
    <table id="mail_messages">
        <tr class="template mail_message_summary" data-message-id="0">
            <td class="mail_message_summary_from"></td>
            <td class="mail_message_summary_subject"></td>
            <td class="mail_message_summary_date"></td>
        </tr>
    </table>
</div>

<?php echo $this->inc("part.editor"); ?>

<?php } ?>
