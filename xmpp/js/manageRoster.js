function getRosterList(){
	$.ajax({
		url: OC.linkTo('xmpp','ajax/getRoster.php'),
		success: function(jids){
			var jids=jQuery.parseJSON(jids)
			for(var i=0;i<jids.length;i++){
				$('#rosterList').append('<li>'+jids[i]+'</li>');
			}
		}
	});
}
function rosterAdd(){
	rosterName=$('#rosterName').val()
	roserJid=$('#rosterJid').val()
	alert('Afegir '+rosterJid+' amb nom '+rosterName);
}
$(document).ready(function(){
	getRosterList();
	$('#rosterSubmit').click(function(){rosterAdd()})
});
