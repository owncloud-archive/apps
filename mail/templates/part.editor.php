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
    });

    $( "#to" ).autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: "http://ws.geonames.org/searchJSON",
                dataType: "jsonp",
                data: {
                    featureClass: "P",
                    style: "full",
                    maxRows: 12,
                    name_startsWith: request.term
                },
                success: function( data ) {
                    response( $.map( data.geonames, function( item ) {
                        return {
                            label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
                            value: item.name
                        }
                    }));
                }
            });
        },
        minLength: 2,
        select: function( event, ui ) {
//            log( ui.item ?
//                    "Selected: " + ui.item.label :
//                    "Nothing selected, input was " + this.value);
        },
        open: function() {
            $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
        },
        close: function() {
            $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
        }
    });

</script>
