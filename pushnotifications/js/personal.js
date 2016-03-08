/**
 * Post the push notification id to the server.
 */
function changePushId () {
        if ($('#pushnotificationid').val() !== '') {
                OC.msg.startSaving('#pushnotificationform .msg');
                // Serialize the data
                var post = $("#pushnotificationform").serialize();
                $.post( OC.filePath( 'pushnotifications', 'ajax', 'changepushid.php' ), post, function (data) {
                        OC.msg.finishedSaving('#pushnotificationform .msg', data);
                });
        }
}

$(document).ready(function () {
        $('#pushnotificationid').keyUpDelayedOrEnter(changePushId);
});

