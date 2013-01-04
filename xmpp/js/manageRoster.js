function getRosterList(){
	$.ajax({
		url: OC.linkTo('xmpp','ajax/getRoster.php'),
		success: function(jids){
			var jids=jQuery.parseJSON(jids)
			$('rosterList').html('');
			for(var i=0;i<jids.length;i++){
				$('#rosterList').append('<li>'+jids[i]+'</li>');
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
$(document).ready(function(){
	getRosterList();
	$('#rosterSubmitForm').click(function(){rosterAddForm()})
});
