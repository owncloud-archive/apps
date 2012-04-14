Mail={
	UI:{
		initializeInterface:function(){
			var folders, messages, first_folder, account_id, folder_id;
			
			/* 1. Load folder list,
			 * 2. Display it
			 * 3. If an account with folders exists
			 * 4.   Load message list
			 * 5.   Display message list
			 */
			$.ajax(OC.filePath('mail','ajax','folders.php'),{
				async:false, // no async!
				data:{},
				type:'GET',
				success:function(jsondata){
					folders = jsondata.data;
				},
			});
			$('#leftcontent').html(folders);
			
			first_folder = $('#leftcontent .mail_folders li')
			
			if( first_folder.length > 0 ){
				first_folder = first_folder.first();
				folder_id = first_folder.data('folder_id');
				account_id = first_folder.parent().data('account_id');
				
				$.ajax(OC.filePath('mail','ajax','messages.php'),{
					async:false, // no async!
					data:{'account_id': account_id, 'folder_id': folder_id},
					type:'GET',
					success:function(jsondata){
						messages = jsondata.data;
					},
				});
				$('#rightcontent').html( messages );
			}
		},
		
		loadMessages:function( account_id, folder_id ){
			// No magic in here
			$.getJSON(OC.filePath('mail','ajax','messages.php'),{'account_id': account_id, 'folder_id': folder_id},function(jsondata){
				if( jsondata.status == 'success' ){
					$('#rightcontent').html( jsondata.data );
				}
				else{
					OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
				}
			});
		},
		
		openMessage:function( account_id, folder_id, message_id ){
			var message;
			
			$.getJSON(OC.filePath('mail','ajax','message.php'),{'account_id': account_id, 'folder_id': folder_id, 'message_id': message_id },function(jsondata){
				if( jsondata.status == 'success' ){
					// close email first
					Mail.UI.closeMessage();
					
					// Find the correct message
					message = $('#mail_messages li[data-message_id="'+message_id+'"]');
					message.find('.message_summary').hide();
					message.append(jsondata.data);
				}
				else{
					OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
				}
			});
		},
		
		closeMessage:function(){
			// Check if message is open
			var message, parent;
			message = $('#mail_message')
			parent = message.parent();
			if( message.length > 0 ){
				$('#mail_message').remove();
				parent.find('.mail_message_summary').show();
			}
		}
	}
}

$(document).ready(function(){
	Mail.UI.initializeInterface();

	// Clicking on a folder loads the message list
	$('ul.mail_folders li').live('click',function(){
		var account_id, folder_id;
		account_id = $(this).parent().data('account_id');
		folder_id = $(this).data('folder_id');
		
		Mail.UI.loadMessages( account_id, folder_id );
	});
	
	// Clicking on a message loads the entire message
	$('#mail_messages .mail_message_summary').live('click',function(){
		var messages. account_id, folder_id, message_id;
		messages = $('#mail_messages').first();
		account_id = messages.data('account_id');
		folder_id = messages.data('folder_id');
		message_id = $(this).parent('li').data('message_id');
		
		Mail.UI.openMessage( account_id, folder_id, message_id );
	});
});
