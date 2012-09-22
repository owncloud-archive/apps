Mail={
	State:{
		current_folder_id:null,
		current_account_id:null,
		current_message_id:null
	},
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
				}
			});
			$('#mail-folders').html(folders);
			
			first_folder = $('#leftcontent .mail_folders li')
			
			if( first_folder.length > 0 ){
                $('#leftcontent').fadeIn(800);
				first_folder = first_folder.first();
				folder_id = first_folder.data('folder_id');
				account_id = first_folder.parent().data('account_id');
				
				
				$.ajax(OC.filePath('mail','ajax','messages.php'),{
					async:false, // no async!
					data:{'account_id': account_id, 'folder_id': folder_id},
					type:'GET',
					success:function(jsondata){
						messages = jsondata.data;
					}
                });
				$('#rightcontent').html( messages );
				
				// Save current folder
				Mail.UI.setFolderActive(account_id, folder_id);
				Mail.State.current_account_id = account_id;
				Mail.State.current_folder_id = folder_id;
			} else {
                $('#leftcontent').fadeOut(800);
            }
		},
		
		loadMessages:function( account_id, folder_id ){
			// Set folder active
			Mail.UI.setFolderInactive( Mail.State.current_account_id, Mail.State.current_folder_id );
			Mail.UI.setFolderActive( account_id, folder_id );
			
			$.getJSON(OC.filePath('mail','ajax','messages.php'),{'account_id': account_id, 'folder_id': folder_id},function(jsondata){
				if( jsondata.status == 'success' ){
					// Add messages
					$('#rightcontent').html( jsondata.data );
					
					Mail.State.current_account_id = account_id;
					Mail.State.current_folder_id = folder_id;
					Mail.State.current_message_id = null;
				}
				else{
					// Set the old folder as being active
					Mail.UI.setFolderInactive( account_id, folder_id );
					Mail.UI.setFolderActive( Mail.State.current_account_id, Mail.State.current_folder_id );
					
					OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
				}
			});
		},
		
		openMessage:function( message_id ){
			var message;
			
			$.getJSON(OC.filePath('mail','ajax','message.php'),{'account_id': Mail.State.current_account_id, 'folder_id': Mail.State.current_folder_id, 'message_id': message_id },function(jsondata){
				if( jsondata.status == 'success' ){
					// close email first
					Mail.UI.closeMessage();
					
					// Find the correct message
					message = $('#mail_messages tr[data-message_id="'+message_id+'"]');
					message.after(jsondata.data);
					
					// Set current Message as active
					Mail.State.current_message_id = message_id;
				}
				else{
					OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
				}
			});
		},
		
		closeMessage:function(){
			// Check if message is open
			var message, parent;
			if( Mail.State.current_message_id !== null ){
				$('#mail_message').remove();
			}
		},
		
		setFolderActive:function(account_id,folder_id){
			$('.mail_folders[data-account_id="'+account_id+'"]>li[data-folder_id="'+folder_id+'"]').addClass('active');
		},
		
		setFolderInactive:function(account_id,folder_id){
			$('.mail_folders[data-account_id="'+account_id+'"]>li[data-folder_id="'+folder_id+'"]').removeClass( 'active' );
		},
		
		bindEndlessScrolling:function(){
			// Add handler for endless scrolling
			//   (using jquery.endless-scroll.js)
			$('#rightcontent').endlessScroll({
				fireDelay:10,
				fireOnce:false,
				loader:'',
				callback:function(i){
					var from;
					
					// Only do the work if we show a folder
					if( Mail.State.current_account_id !== null && Mail.State.current_folder_id !== null ){
						
						// do not work if we already hit the end
						if( $('#mail_messages').data('stop_loading') != 'true' ){
							from = $('#mail_messages>tr').length;
							
							// decrease if a message is shown
							if( Mail.State.current_message_id !== null ){
								from = from - 1;
							}
							
							$.ajax(OC.filePath('mail','ajax','append_messages.php'),{
								async:false, // no async!
								data:{ 'account_id':Mail.State.current_account_id, 'folder_id':Mail.State.current_folder_id, 'from':from, 'count':20},
								type:'GET',
								success:function(jsondata){
									if( jsondata.status == 'success' ){
										$('#mail_messages').append(jsondata.data);
									}
									else{
										OC.dialogs.alert(jsondata.data.message, t('mail', 'Error'));
									}
								}
							});
			
							// If we did not get any new messages stop
							if( from == $('#mail_messages>tr').length || ( from == $('#mail_messages>tr').length + 1 && Mail.State.current_message_id !== null )){
								$('#mail_messages').data('stop_loading', 'true')
							}
						}
					}
				}
			});
		},
		
		unbindEndlessScrolling:function(){
			$('#rightcontent').unbind('scroll');
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
		var messages, account_id, folder_id, message_id;
		message_id = $(this).data('message_id');
		
		Mail.UI.openMessage( message_id );
	});
	
	Mail.UI.bindEndlessScrolling();
});
