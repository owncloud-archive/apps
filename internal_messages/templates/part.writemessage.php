<script type="text/javascript">
    OC.InternalMessages.initDropDown();

</script>
<div id="writemessage_dialog" title="<?php echo $l -> t("Write Message");?>">
    <p style="margin-top: 1em">write a <strong>internal message</strong> to user or group ...</p>
    <table width="100%" style="border: 0; margin-top: 1em">
        <tr>
            <td>
            <input type="text" id="to_message" placeholder="Message To ..." />
            </td>
        </tr>
        <tr>
            <td>
            <ul class="sendto msglist">
            </ul>
            </td>
        </tr>
        <tr>
            <td>
                <textarea id="content_message" placeholder="Write the Content ..." cols=50 rows=5 style="width: 95%;"></textarea>
            </td>
        </tr>
        <tr>
            <td style="padding: 0.5em; text-align: right;"><a class="button" href="#" onclick="OC.InternalMessages.SendMessage()"><?php echo $l->t('Send Message')
            ?></a></td>
        </tr>
    </table>
