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
            height:420,
            width:640,
            modal:true,
            buttons:{
                "Send":function () {
                    $(this).dialog("close");
                }
            }});

        function split(val) {
            return val.split(/,\s*/);
        }

        function extractLast(term) {
            return split(term).pop();
        }

        $("#to")
            // don't navigate away from the field on tab when selecting an item
                .bind("keydown", function (event) {
                    if (event.keyCode === $.ui.keyCode.TAB &&
                            $(this).data("autocomplete").menu.active) {
                        event.preventDefault();
                    }
                })
                .autocomplete({
                    source:function (request, response) {
                        $.getJSON(
                                OC.filePath('mail', 'ajax', 'receivers.php'),
                                {
                                    term:extractLast(request.term)
                                }, response);
                    },
                    search:function () {
                        // custom minLength
                        var term = extractLast(this.value);
                        if (term.length < 2) {
                            return false;
                        }
                    },
                    focus:function () {
                        // prevent value inserted on focus
                        return false;
                    },
                    select:function (event, ui) {
                        var terms = split(this.value);
                        // remove the current input
                        terms.pop();
                        // add the selected item
                        terms.push(ui.item.value);
                        // add placeholder to get the comma-and-space at the end
                        terms.push("");
                        this.value = terms.join(", ");
                        return false;
                    }
                });
    });

</script>
