function getRosterList(){
	$.ajax({
		url: OC.linkTo('xmpp','ajax/getRoster.php'),
		success: function(jids){
			var jids=jQuery.parseJSON(jids)
			$('#rosterList').html('');
			for(var i=0;i<jids.length;i++){
				divjid=jidtodiv(jids[i]);
				$('#rosterList')
				.append('<li><a href="#" onclick="createChatBox(\''+divjid+'\')">'+jids[i]+'</a><a href="#" onclick="deleteRoster(\''+divjid+'\')"><img src="'+OC.linkTo('xmpp','img/delete.png')+'"></a></li>');
			}
		}
	});
}
function rosterAddForm(){
	rosterName=$('#rosterNameInput').val()
	rosterJid=$('#rosterJidInput').val()
	$.ajax({
		type: 'POST',
		url: OC.linkTo('xmpp','ajax/addRoster.php'),
		data: "name="+rosterName+"&"+"jid="+rosterJid,
		success: getRosterList()
	});
}
function deleteRoster(jid){
	j=divtojid(jid);
	$.ajax({
		type: 'POST',
		url: OC.linkTo('xmpp','ajax/deleteRoster.php'),
		data: "jid="+j,
		success: getRosterList()
	});
}
$(document).ready(function(){
	getRosterList();
	$('#rosterSubmitForm').click(function(){rosterAddForm()})
});
