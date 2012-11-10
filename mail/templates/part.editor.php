<div id="mail_editor" title="<?php p($l->t('New Message')); ?>">
    <form>
        <table width="100%">
            <tr>
                <td>
                    <input type="text" name="to" id="to" class="text ui-widget-content ui-corner-all"
                           placeholder="<?php p($l->t('To')); ?>"/>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="text" name="subject" id="subject" class="text ui-widget-content ui-corner-all"
                           placeholder="<?php p($l->t('Subject')); ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <textarea name="body" id="body" class="text ui-widget-content ui-corner-all"></textarea>
                </td>
            </tr>
        </table>
    </form>

</div>

<script>
    $(function () {
        $("#mail_editor").dialog({
            autoOpen:false,
            height:300,
            width:350,
            modal:true,
            buttons:{
                "Send":function () {
                    $(this).dialog("close");
                }
            }});
    });
</script>
