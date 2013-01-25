Mail = {
    State:{
        current_folder_id:null,
        current_account_id:null,
        current_message_id:null
    },
    UI:{
        initializeInterface:function () {
            var folders, messages, first_folder, account_id, folder_id;

            /* 1. Load folder list,
             * 2. Display it
             * 3. If an account with folders exists
             * 4.   Load message list
             * 5.   Display message list
             */
            $.ajax(OC.filePath('mail', 'ajax', 'folders.php'), {
                data:{},
                type:'GET',
                success:function (jsondata) {
                    if (jsondata.status == 'success') {
                        folders = jsondata.data;
                        $('#mail-folders').html(folders);

                        first_folder = $('#leftcontent').find('.mail_folders li');

                        if (first_folder.length > 0) {
                            $('#leftcontent').fadeIn(800);
                            first_folder = first_folder.first();
                            folder_id = first_folder.data('folder_id');
                            account_id = first_folder.parent().data('account_id');


                            $.ajax(OC.filePath('mail', 'ajax', 'messages.php'), {
                                data:{'account_id':account_id, 'folder_id':folder_id},
                                type:'GET',
                                success:function (jsondata) {
                                    messages = jsondata.data;
                                    Mail.UI.addMessages(messages);
                                }
                            });

                            // Save current folder
                            Mail.UI.setFolderActive(account_id, folder_id);
                            Mail.State.current_account_id = account_id;
                            Mail.State.current_folder_id = folder_id;
                        } else {
                            $('#leftcontent').fadeOut(800);
                        }                    } else {
                        OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
                    }
                }
            });
        },

        clearMessages:function () {
            var table = $('#mail_messages');
            var template = table.find('tr.template').clone();
            var template_loading = table.find('tr.template_loading').clone();

            table.empty();
            table.append(template);
            table.append(template_loading);
        },

        addMessages:function (data) {
            var table = $('#mail_messages');
            var template = table.find('tr.template').clone();
            var template_loading = table.find('tr.template_loading').clone();
            messages = data.messages;

            //table.date('');
            for (var i in messages) {
                var message = messages[i];
                var clone = template.clone();
                clone.removeClass('template');

                clone.data('message_id', message.id);
                clone.attr('data-message-id', message.id);
                if (message.flags['unseen']) {
                    clone.addClass('unseen');
                }
                clone.find('.mail_message_summary_from').text(message.from);
                clone.find('.mail_message_summary_subject').text(message.subject);
                clone.find('.mail_message_summary_date').text(message.date);
                clone.find('.mail_message_summary_size').text(message.size);

                table.append(clone);

                // add loading row
                var clone_loading = template_loading.clone();
                clone_loading.removeClass('template_loading');
                clone_loading.attr('data-message-id', message.id);
                table.append(clone_loading);
            }
        },

        loadMessages:function (account_id, folder_id) {
            // Set folder active
            Mail.UI.setFolderInactive(Mail.State.current_account_id, Mail.State.current_folder_id);
            Mail.UI.setFolderActive(account_id, folder_id);
            Mail.UI.clearMessages();

            $.getJSON(OC.filePath('mail', 'ajax', 'messages.php'), {'account_id':account_id, 'folder_id':folder_id}, function (jsondata) {
                if (jsondata.status == 'success') {
                    // Add messages
                    Mail.UI.addMessages(jsondata.data);

                    Mail.State.current_account_id = account_id;
                    Mail.State.current_folder_id = folder_id;
                    Mail.State.current_message_id = null;
                }
                else {
                    // Set the old folder as being active
                    Mail.UI.setFolderInactive(account_id, folder_id);
                    Mail.UI.setFolderActive(Mail.State.current_account_id, Mail.State.current_folder_id);

                    OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
                }
            });
        },

        openMessage:function (message_id) {
            var message;

            // close email first
            Mail.UI.closeMessage();
            if (Mail.State.current_message_id === message_id) {
                return;
            }

            var summary_row = $('#mail_messages tr.mail_message_summary[data-message-id="' + message_id + '"]');
            var load_row = $('#mail_messages').find('tr.mail_message_loading[data-message-id="' + message_id + '"]');
            load_row.show();

            $.getJSON(OC.filePath('mail', 'ajax', 'message.php'), {'account_id':Mail.State.current_account_id, 'folder_id':Mail.State.current_folder_id, 'message_id':message_id }, function (jsondata) {
                if (jsondata.status == 'success') {

                    summary_row.hide();

                    // hide loading
                    load_row.hide();

                    // Find the correct message
                    load_row.after(jsondata.data);

                    // Set current Message as active
                    Mail.State.current_message_id = message_id;
                }
                else {
                    OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
                }
            });
        },

        closeMessage:function () {
            // Check if message is open
            var message;
            if (Mail.State.current_message_id !== null) {
                $('#mail_message').remove();
                $('#mail_message_header').remove();

                var summary_row = $('#mail_messages tr.mail_message_summary[data-message-id="' + Mail.State.current_message_id + '"]');
                summary_row.show();
            }
        },

        setFolderActive:function (account_id, folder_id) {
            $('.mail_folders[data-account_id="' + account_id + '"]>li[data-folder_id="' + folder_id + '"]').addClass('active');
        },

        setFolderInactive:function (account_id, folder_id) {
            $('.mail_folders[data-account_id="' + account_id + '"]>li[data-folder_id="' + folder_id + '"]').removeClass('active');
        },

        bindEndlessScrolling:function () {
            // Add handler for endless scrolling
            //   (using jquery.endless-scroll.js)
            $('#rightcontent').endlessScroll({
                fireDelay:10,
                fireOnce:false,
                loader:'',
                callback:function (i) {
                    var from, new_length;

                    // Only do the work if we show a folder
                    if (Mail.State.current_account_id !== null && Mail.State.current_folder_id !== null) {

                        // do not work if we already hit the end
                        if ($('#mail_messages').data('stop_loading') != 'true') {
                            from = $('#mail_messages .mail_message_summary').length - 1;
                            // minus 1 because of the template

                            // decrease if a message is shown
                            if (Mail.State.current_message_id !== null) {
                                from = from - 1;
                            }

                            $.ajax(OC.filePath('mail', 'ajax', 'append_messages.php'), {
                                async:false, // no async!
                                data:{ 'account_id':Mail.State.current_account_id, 'folder_id':Mail.State.current_folder_id, 'from':from, 'count':20},
                                type:'GET',
                                success:function (jsondata) {
                                    if (jsondata.status == 'success') {
                                        Mail.UI.addMessages(jsondata.data);
                                    }
                                    else {
                                        OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
                                    }
                                }
                            });

                            // If we did not get any new messages stop
                            new_length = $('#mail_messages .mail_message_summary').length - 1;
                            // minus 1 because of the template
                            if (from == new_length || ( from == new_length + 1 && Mail.State.current_message_id !== null )) {
                                $('#mail_messages').data('stop_loading', 'true')
                            }
                        }
                    }
                }
            });
        },

        unbindEndlessScrolling:function () {
            $('#rightcontent').unbind('scroll');
        }
    }
}

$(document).ready(function () {
    Mail.UI.initializeInterface();

    // auto detect button handling
    $('#auto_detect_account').click(function () {
        $('#auto_detect_account').attr('disabled', "disabled");
        $('#auto_detect_account').val(t('mail', 'Checking ...'));
        var email_address, password;
        email_address = $('#email_address').val();
        password = $('#password').val();
        $.ajax(OC.filePath('mail', 'ajax', 'account/autodetect.php'), {
            data:{email_address:email_address, password:password},
            type:'POST',
            success:function (jsondata) {
                if (jsondata.status == 'success') {
                    // reload on success
                    window.location.reload();
                } else {
                    $('#auto_detect_account').attr('disabled', 'false');
                    $('#auto_detect_account').val(t('mail', 'Auto Detect'));
                    var error;

                    if (jsondata.message == 'email') {
                        error = t('mail', 'Not a email address');
                    } else {
                        error = 'Unknown error code: ' + jsondata.message;
                    }
                    OC.dialogs.alert(error, t('mail', 'Error'));
                }
            }
        });
    });

	// new mail message button handling
	$(document).on('click', '#mail_new_message', function () {
		$('#to').val('');
		$('#subject').val('');
		$('#body').val('');
		$('#mail_editor').dialog("open");
	});

	// Clicking on a folder loads the message list
	$(document).on('click', 'ul.mail_folders li', function () {
		var account_id, folder_id;
		account_id = $(this).parent().data('account_id');
		folder_id = $(this).data('folder_id');

		Mail.UI.loadMessages(account_id, folder_id);
	});

	// Clicking on a message loads the entire message
	$(document).on('click', '#mail_messages .mail_message_summary', function () {
		var message_id = $(this).data('message_id');
		Mail.UI.openMessage(message_id);
	});

//    Mail.UI.bindEndlessScrolling();
});
