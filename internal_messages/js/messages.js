OC.InternalMessages = {

    folder : 'inbox',
    search : ['p'],

    mesgto : [],

    loading : function(isLoading) {
        if(isLoading) {
            $('#loading').show();
        } else {
            $('#loading').hide();
        }
    },
    
    SearchMessage : function(event) {

        var pattern = $('#search_messages').val();
        $.post(OC.filePath('internal_messages', 'ajax', 'search_message.php'), {
            pattern : pattern,
            folder : OC.InternalMessages.folder
        }, function(jsondata) {
            if(jsondata.status == 'success') {
                document.getElementById('messages_wall').innerHTML = jsondata.data;
                var search = document.getElementsByName("message_content");
                $.each(search, function(i) {
                    var str = search[i];
                    var orgText = $(str).text();
                    orgText = orgText.replace(pattern, function($1) {
                        return "<span style='background-color: yellow;'>" + $1 + "</span>"
                    });
                    $(str).html(orgText);
                });
            } else {
                OC.dialogs.alert(jsondata.data.message, 'Error - Internal Message');
            }
        }, 'json');
    },
    
    SendMessage : function() {

        var msgcontent = $("#content_message").val().trim();
        
        if( msgcontent && ( OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_USER].length || OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_GROUP].length ) ) {
	  
	  
            $.post(OC.filePath('internal_messages', 'ajax', 'send_message.php'), {
                msgto : OC.InternalMessages.mesgto,
                msgcontent : msgcontent
            }, function(jsondata) {
                if(jsondata.status == 'success') {
                    
                    OC.dialogs.alert(jsondata.data.message, 'Success - Internal Message');
                    
                    $.post(OC.filePath('internal_messages', 'ajax', 'view_messaged_users.php'), {
		    }, function(jsondata) {
			
			if(jsondata.data){
			  document.getElementById('messages_wall').innerHTML = jsondata.data;
			}					
				
		       },'json');

                    $('#writemessage_dialog').dialog('destroy').remove();
                } else {
                    OC.dialogs.alert(jsondata.data.message, 'Error - Internal Message');
                }
            }, 'json');
        } else {
            OC.dialogs.alert('All fields must be filled ...', 'Error - Internal Message');
        }
    },
    
    DelMessage : function(id) {

        OC.InternalMessages.loading(true);
        $('.tipsy').remove();

        $.post(OC.filePath('internal_messages', 'ajax', 'del_message.php'), {
            id : id,
            folder : OC.InternalMessages.folder
        }, function(jsondata) {}, 'json');

        OC.InternalMessages.loading(false);
    },
    
    ReplyMessage : function(owner) {

        $('.tipsy').remove();
        
        $('#dialog_holder').load(OC.filePath('internal_messages', 'ajax', 'write_message.php'), function(response) {
            if(response.status != 'error') {
                
                $('#writemessage_dialog').dialog({
                    minWidth : 500,
                    modal : true,
                    close : function(event, ui) {
                        $(this).dialog('destroy').remove();
                    }
                }).css('overflow', 'visible');

                var msgType = OC.Share.SHARE_TYPE_USER;
                var msgTo = owner;
                var newitem = '<li ' + 'data-message-to="' + msgTo 
                            + '" ' + 'data-message-type="' + msgType + '">' + msgTo 
                            + ' (' + (msgType == OC.Share.SHARE_TYPE_USER ? t('core', 'user') : t('core', 'group')) + ')' 
                            +'<span class="msgactions">'+ '<img class="svg action delete" title="Quit"src="' 
                            + OC.imagePath('core', 'actions/delete.svg') + '"></span></li>';
                $('.sendto.msglist').append(newitem);
                OC.InternalMessages.mesgto[msgType].push(msgTo);
                $('#content_message').focus();

            }
        });

    },
    

    ReplyConversation : function(msgto) {

        var messageTo = new Array();
	messageTo[0] = msgto;
	var msg_to =  new Array();
	msg_to[0] = messageTo;
        var msgcontent = $("#conversation_content").val().trim();
	var group_conv_id = $("#conversation_content").attr('group_conv_id');
	if(group_conv_id == 'undefined'){
		group_conv_id = 0;
	}

        if( msgcontent ){ //&& ( OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_USER].length || OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_GROUP].length ) ) {
            $.post(OC.filePath('internal_messages', 'ajax', 'send_message.php'), {
                msgto : msg_to,
                msgcontent : msgcontent,
		groupConvId : group_conv_id

            }, function(jsondata) {
                if(jsondata.status != 'success') {
                    OC.dialogs.alert(jsondata.data.message, 'Error - Internal Message');
                } else {
		    OC.InternalMessages.ViewConversation(msgto);	
		}
            }, 'json');
        } else {
            OC.dialogs.alert('All fields must be filled ...', 'Error - Internal Message');
        }

    },

  ReplyGroup : function(conv_id) { 

	var content = $("#conversation_content").val().trim();
	
	if( content ){ 
	    $.post(OC.filePath('internal_messages', 'ajax', 'send_message.php'), {
		groupConvId : conv_id,
		msgcontent : content

	    }, function(jsondata) {
		if(jsondata.status != 'success') {
		    OC.dialogs.alert(jsondata.data.message, 'Error - Internal Message');
		} else {
		    OC.InternalMessages.ViewGroupConversation(conv_id);	
		}
	    }, 'json');

	}

    },
    

   ViewConversation : function(ref_owner){
        OC.InternalMessages.loading(true);
        $('.tipsy').remove();
	
	 $.post(OC.filePath('internal_messages', 'ajax', 'view_conversation.php'), {
            ref_owner : ref_owner
        }, function(jsondata) {
            // page reload code here
		if(!jsondata){
			OC.dialogs.alert('The messages could not be loaded', 'Failure - Internal Message');		
		}else{
		  document.getElementById('messages_wall').innerHTML = jsondata.data;
		 $("#messages_wall").animate({ scrollTop: $('#conversation_content').position().top });
		}
               
        }, 'json');

        OC.InternalMessages.loading(false);

   },

   ViewGroupConversation : function(id){
        OC.InternalMessages.loading(true);
        $('.tipsy').remove();
	
	 $.post(OC.filePath('internal_messages', 'ajax', 'view_group_conversation.php'), {
            conv_id : id
        }, function(jsondata) {

		
		if(jsondata.status != 'success'){
			OC.dialogs.alert('The messages could not be loaded', 'Failure - Internal Message');		
		}else{
		  document.getElementById('messages_wall').innerHTML = jsondata.data;
		 $("#messages_wall").animate({ scrollTop: $('#conversation_content').position().top });
		}
               
        }, 'json');

        OC.InternalMessages.loading(false);

   },



   DelMessageInConversation : function(msg_owner,partner,id) {

        OC.InternalMessages.loading(true);
        $('.tipsy').remove();

        $.post(OC.filePath('internal_messages', 'ajax', 'del_message_conv.php'), {
            id : id,
	    msg_owner : msg_owner
        }, function(jsondata) {
            // page reload code here
		if(jsondata.status == 'success'){
			OC.dialogs.alert('The message has been successfully deleted.', 'Success - Internal Message');		
			if(msg_owner == jsondata.data.current_user){
				OC.InternalMessages.ViewConversation(partner);	
			}else{
				OC.InternalMessages.ViewConversation(msg_owner);	
			}		
		}
                
        }, 'json');

        OC.InternalMessages.loading(false);
    },

    initDropDown : function() {

        OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_USER] = [];
        OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_GROUP] = [];

        $('#to_message').autocomplete({
            minLength : 2,
            source : function(search, response) {
                $.get(OC.filePath('core', 'ajax', 'share.php'), {
                    fetch : 'getShareWith',
                    search : search.term,
                    itemShares : [OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_USER], OC.InternalMessages.mesgto[OC.Share.SHARE_TYPE_GROUP]]
                }, function(result) {
                    if(result.status == 'success' && result.data.length > 0) {
                        response(result.data);
                    }
                });
            },
            focus : function(event, focused) {
                event.preventDefault();
            },
            select : function(event, selected) {
                var msgType = selected.item.value.shareType;
                var msgTo = selected.item.value.shareWith;
                var newitem = '<li ' + 'data-message-to="' + msgTo 
                            + '" ' + 'data-message-type="' + msgType + '">' + msgTo 
                            + ' (' + (msgType == OC.Share.SHARE_TYPE_USER ? t('core', 'user') : t('core', 'group')) + ')' 
                            +'<span class="msgactions">'+ '<img class="svg action delete" title="Quit"src="' 
                            + OC.imagePath('core', 'actions/delete.svg') + '"></span></li>';
                $('.sendto.msglist').append(newitem);
                $('#sharewith').val('');
                OC.InternalMessages.mesgto[msgType].push(msgTo);
		$(this).val("");
                return false;
            },
        });
    },


	prependUpdatedRows : function() {
	
		$("table#messaged_users tr.updates").each(function(){
			$(this).remove();
			$('#messaged_users').prepend($(this));		
		});
	}
  }

    $(document).ready(function() {
    $('#back_btn').hide();
    OC.InternalMessages.prependUpdatedRows();
    $('.msgactions > .delete').live('click', function() {   
        var container = $(this).parents('li').first();
        var msgType = container.data('message-type');
        var msgTo = container.data('message-to');
        container.remove();
        var index = OC.InternalMessages.mesgto[msgType].indexOf(msgTo);
        OC.InternalMessages.mesgto[msgType].splice(index, 1);
    });

    $(window).resize(function() {
        fillWindow($('#messages_wall'));
    });

    $(window).trigger('resize');

    $('#search_messages').live('keyup', OC.InternalMessages.SearchMessage);
    
    $('#search_messages').live('keydown', function(event) {
        if(event.keyCode == 13 || event.keyCode == 27) {
            return false;
        }
    });

    $('#create_message').click(function() {

        $('#dialog_holder').load(OC.filePath('internal_messages', 'ajax', 'write_message.php'), function(response) {
            if(response.status != 'error') {
                $('#writemessage_dialog').dialog({
                    minWidth : 500,
                    modal : true,
                    close : function(event, ui) {
                        $(this).dialog('destroy').remove();
                    }
                }).css('overflow', 'visible');
            }
        });
	
    });




    $('#back_btn').click(function() {

        OC.InternalMessages.loading(true);

 	$.post(OC.filePath('internal_messages', 'ajax', 'view_messaged_users.php'), {
        }, function(jsondata) {
            document.getElementById('messages_wall').innerHTML = jsondata.data;
        }, 'json');

        OC.InternalMessages.loading(false);

    });


    $('.message_delete').live('click', function() {
        OC.InternalMessages.DelMessage($(this).attr('msg_id'));
    });

    $('.message_reply').live('click', function() {
        OC.InternalMessages.ReplyMessage($(this).attr('msg_owner'));
    });

    $('.conversation_reply').live('click',function(){
	 OC.InternalMessages.ReplyConversation($(this).attr('msg_owner'));
         
    });
	
    $('.conversation_group_reply').live('click',function(){
	 OC.InternalMessages.ReplyGroup($(this).attr('conv_id'));
         
    });

    $('.message_delete_conv').live('click', function() {
        OC.InternalMessages.DelMessageInConversation($(this).attr('msg_owner'),$(this).attr('partner'),$(this).attr('msg_id'));
    });

    

    $('.unread').live('hover' , function(){
	$(this).removeClass('unread');
	$(this).addClass('read');
	var message_id = $(this).find('p[message_id]').attr('message_id') ;
	
	$.post(OC.filePath('internal_messages', 'ajax', 'mark_as_read.php'), {
            id : message_id
        }, function(jsondata) {
		$unread = jsondata.data;
		if($unread > 0){
			$('#unread_count').text('('+$unread+')');
			$('#unread_count').show();
		}else{
			$('#unread_count').hide();		
		}
        }, 'json');
    });

    $('.users').live('click',function(){
	$('#back_btn').show();
	$('#create_message').hide();
	var element = $(this).find('#msg_content').find('.preffered_user');

	if(element.attr('group_conv_id') > 0){
		OC.InternalMessages.ViewGroupConversation(element.attr('group_conv_id'));	
	}else{
       		OC.InternalMessages.ViewConversation(element.attr('ref_owner'));
   	} 
       
    });


    $('#back_btn').live('click',function(){
	$('#back_btn').hide();
	$('#create_message').show();
    });

    $('a.message_action').tipsy({
        gravity : 's',
        fade : true,
        live : true
    });

})
