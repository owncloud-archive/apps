var connection = null;
var sessionAttached=false;
var savedomchat=true;
var xcd='xmpp-chat-dom';

function onConnect(status){
	console.log('Connection status '+ status)
	if (status == Strophe.Status.ATTACHED) {
		updatePresence('online');
		sessionAttached=true;
		createDomChat()
		console.log('Connection attached');
		if(connection.paused){connection.resume()}
	}else if (status == Strophe.Status.DISCONNECTED ){
		var connection=null;
		attachNew();
	}
}
function getRoster(id){
	if(id==null){
		iq=$iq({type: 'get'}).c('query', {xmlns: Strophe.NS.ROSTER});
	}else{
		iq=$iq({type: 'get', id: id}).c('query', {xmlns: Strophe.NS.ROSTER});
	}
	connection.sendIQ(iq);
}
function updatePresence(type){
	connection.send($pres().c('show').t(type))
}

function onPresence(pres){
	jid = Strophe.getBareJidFromJid(pres.getAttribute('from'));
	type = pres.getAttribute('type');
	if(type!=null){
		if(type=='subscribe'){
			// Afegir roster
			if(confirm('Vols afegir a '+jid+' a la teva llista de contactes?')){
				connection.send($pres({ to: jid, type: "subscribed" }));
				connection.send($pres({ to: jid, type: "subscribe" }));
			}
		}else if(type=='unsubscribe'){
			if(confirm('Vols eliminar '+jid+' de la teva llista de contactes?')){
				connection.send($pres({ to: jid, type: "unsubscribed" }));
				connection.send($pres({ to: jid, type: "unsubscribe" }));
			}
		}else if(type=='subscribed'){
			connection.send($pres({ to: jid, type: "subscribed" }));
		}
	}
	//gestionar tipus de presencia
	show = pres.getElementsByTagName('show')
	show = Strophe.getText(show[0])
	if(type=='unavailable'){
		img_stat='img/dot_black.png';
	}else if(show=='away'){
		img_stat='img/dot_red.gif';
	}else{
		img_stat='img/dot_green.gif';
	}
	divjid= jidtodiv(jid);
	$('#img_'+divjid).attr('src',OC.linkTo('xmpp',img_stat))
	return true;
}

function onRoster(msg) {
	console.log(msg)
	id = msg.getAttribute('id');
	if(id=='manageRoster'){
		manageRoster(msg);
		return true;
	}
	type = msg.getAttribute('type');
	if(type=='get' || type=='result'){
		$('#roster_content').html('');
        	xmlroster = msg.firstChild;
	        items = xmlroster.getElementsByTagName('item');
		for (i = 0; i < items.length; i++) {
			item = items[i];
			jid = item.getAttribute('jid'); //REQUIRED
			name = item.getAttribute('name'); //MAY
			subscription = item.getAttribute('subscription');
			rosterAdd(jid,name,content)
		}
	}else if(type=='set'){
		connection.sendIQ($iq({'from':connection.jid,'to':Strophe.getDomainFromJid(connection.jid),'type':'result','id':msg.getAttribute('id')}));
		xmlroster=msg.firstchild;
		items=xmlroster.getElementsByTagName('item');
		item=items[0];
		jid = item.getAttribute('jid');
		name = item.getAttribute('name');
		rosterAdd(jid,name,content)
	}
	return true;
}
function onMessage(msg){
	//console.log(msg)
	var to = msg.getAttribute('to');
	var from = Strophe.getBareJidFromJid(msg.getAttribute('from'));
	chatboxtitle=from.replace('.','DOT').replace('@','AT')
	var type = msg.getAttribute('type');
	var elems = msg.getElementsByTagName('body');

	if(elems[0]!=null){
		if($("#chatbox\\_"+chatboxtitle).length!=1){
			createChatBox(chatboxtitle)
		}
		$("#chatbox\\_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+from+':&nbsp;&nbsp;<br/></span><span class="chatboxmessagecontent">'+Strophe.getText(elems[0])+'</span></div>');
		$("#chatbox\\_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
	}
	return true;
}

function jidtodiv(jid){
	return jid.replace('@','AT').replace('.','DOT');
}
function divtojid(jid){
	return jid.replace('AT','@').replace('DOT','.');
}

function requestPresence(jid){
	connection.send($pres({'type':'probe','from':connection.jid,'to':jid}));
}

function rosterAdd(jid,name){
	requestPresence(jid)
	divjid=jidtodiv(jid)
	if(name=='null')name=jid;
	$('#roster_content').append('<p id="'+divjid+'" onclick="createChatBox(\''+divjid+'\')"><img id="img_'+divjid+'" src="'+OC.linkTo('xmpp','img/dot_black.png')+'" />'+name+'</p>')
}
function onSuggest(msg){
	//console.log(msg)
	return true;
}

function saveRID(){
	if(sessionAttached==true){
		$.ajax({
			type: 'POST',
			async: false,
			url:OC.linkTo('xmpp','ajax/setRid.php'),
			data:"rid="+connection.rid
		});
	}
	return true;
}

function attachNew(){
	$.ajax({
		type: 'GET',
		url: OC.linkTo('xmpp','ajax/newSession.php'),
		success: function(){
			initConnection();
		}
	});
}

function attachSession(){
	$.ajax({
                type: 'GET',
                url:OC.linkTo('xmpp','ajax/getSession.php'),
                success: function(xmppsess){
                        var j=jQuery.parseJSON(xmppsess)
                        connection.rid=j.rid;
                        connection.attach(j.jid,j.sid,j.rid,onConnect);
                }
        });
}

function initBosh(){
	$.ajax({
		type: 'GET',
		url:OC.linkTo('xmpp','ajax/getBOSHURL.php'),
		success: function(boshurl){
			var bosh=jQuery.parseJSON(boshurl);
			initConnection(bosh);
		}
	});
}

function initConnection(bosh){
	connection = new Strophe.Connection(bosh)
	connection.rawInput = function (data) { console.log('RECV: ' + data); };
	connection.rawOutput = function (data) { console.log('SENT: ' + data); saveRID();};
	Strophe.addNamespace('ROSTERX', 'http://jabber.org/protocol/rosterx');
	connection.addHandler(onRoster,Strophe.NS.ROSTER,"iq");
	connection.addHandler(onSuggest,Strophe.NS.ROSTERX,'message');
	connection.addHandler(onMessage, null, 'message');
	connection.addHandler(onPresence,null,'presence');
	attachSession();
}

function createDomChat(){
	console.log('Creating dom chat');
        $('<div/>').attr('id','xmpp-chat-dom').appendTo($('body'))
        if(xmppdom=getVar(xcd)){
		$('#xmpp-chat-dom').html(xmppdom);
		rmVar(xcd);
	}else{
		$(" <div />" ).attr("id","chatbox_roster")
		.addClass("chatbox")
		.html('<div class="chatboxhead"><div class="chatboxtitle">Contacts</div><div class="chatboxoptions"><a href="javascript:void(0)" onclick="javascript:toggleChatBoxGrowth(\'roster\')">-</a> </div><br clear="all"/></div><div id="roster_content" class="chatboxcontent"></div>')
		.appendTo($( "#xmpp-chat-dom" ));
		$("#chatbox_roster").css('display','block');
		$("#chatbox_roster").css('right','10px');
	}

	getRoster();
	return true;
}
function unloadChat(){
	connection.pause();
	saveRID();
	if(savedomchat==true){
		xmppdom=$('#xmpp-chat-dom').html();
		if(xmppdom!=null){
			setVar(xcd,xmppdom)
		}
	};
}

function rmVar(name){
	return sessionStorage.removeItem(name);
}

function setVar(name,value){
	return sessionStorage.setItem(name,value);
}

function getVar(name){
	return sessionStorage.getItem(name);
}

$(document).ready(function () {
	initBosh();
	$(window).bind('beforeunload',function(){
		unloadChat();
	});
	$('#logout').click(function(){
		savedomchat=false;
		rmVar(xcd);
	});
});



// TESTING
var chatboxFocus = new Array();

function getChatBoxes(){
	var chat= new Array();
	var chatboxs = $('.chatbox');
	for(var x=0;x<chatboxs.length;x++){
		if(chatboxs[x].id!='chatbox_roster'){
			chat.push(chatboxs[x].id)
		}
	}
	return chat;
}

function createChatBox(chatboxtitle,minimizeChatBox) {
        if ($("#chatbox_"+chatboxtitle).length > 0) {
                if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
                        $("#chatbox_"+chatboxtitle).css('display','block');
                        restructureChatBoxes();
                }
                $("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
                return;
        }

        $(" <div />" ).attr("id","chatbox_"+chatboxtitle)
        .addClass("chatbox")
        .html('<div class="chatboxhead"><div class="chatboxtitle">'+chatboxtitle.replace('DOT','.').replace('AT','@')+'</div><div class="chatboxoptions"><a href="javascript:void(0)" onclick="javascript:toggleChatBoxGrowth(\''+chatboxtitle+'\')">-</a> <a href="javascript:void(0)" onclick="javascript:closeChatBox(\''+chatboxtitle+'\')">X</a></div><br clear="all"/></div><div class="chatboxcontent"></div><div class="chatboxinput"><textarea class="chatboxtextarea" onkeydown="javascript:return checkChatBoxInputKey(event,this,\''+chatboxtitle+'\');"></textarea></div>')
        .appendTo($( "#xmpp-chat-dom" ));

        $("#chatbox_"+chatboxtitle).css('bottom', '0px');

        chatBoxeslength = 0;
	chatBoxes=getChatBoxes();
        for (x in chatBoxes) {
                if ($("#"+chatBoxes[x]).css('display') != 'none') {
                        chatBoxeslength++;
                }
        }

        if (chatBoxeslength == 0) {
                $("#chatbox_"+chatboxtitle).css('right', '245px');
        } else {
                width = (chatBoxeslength)*(225+7)+245;
                $("#chatbox_"+chatboxtitle).css('right', width+'px');
        }

        if (minimizeChatBox == 1) {
                minimizedChatBoxes = new Array();

                if ($.cookie('chatbox_minimized')) {
                        minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
                }
                minimize = 0;
                for (j=0;j<minimizedChatBoxes.length;j++) {
                        if (minimizedChatBoxes[j] == chatboxtitle) {
                                minimize = 1;
                        }
                }

                if (minimize == 1) {
                        $('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
                        $('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
                }
        }

        chatboxFocus[chatboxtitle] = false;

        $("#chatbox_"+chatboxtitle+" .chatboxtextarea").blur(function(){
                chatboxFocus[chatboxtitle] = false;
                $("#chatbox_"+chatboxtitle+" .chatboxtextarea").removeClass('chatboxtextareaselected');
        }).focus(function(){
                chatboxFocus[chatboxtitle] = true;
                $('#chatbox_'+chatboxtitle+' .chatboxhead').removeClass('chatboxblink');
                $("#chatbox_"+chatboxtitle+" .chatboxtextarea").addClass('chatboxtextareaselected');
        });

        $("#chatbox_"+chatboxtitle).click(function() {
                if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') != 'none') {
                        $("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
                }
        });

        $("#chatbox_"+chatboxtitle).show();
}

function toggleChatBoxGrowth(chatboxtitle) {
        if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') == 'none') {

                var minimizedChatBoxes = new Array();

                if ($.cookie('chatbox_minimized')) {
                        minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
                }

                var newCookie = '';

                for (i=0;i<minimizedChatBoxes.length;i++) {
                        if (minimizedChatBoxes[i] != chatboxtitle) {
                                newCookie += chatboxtitle+'|';
                        }
                }

                newCookie = newCookie.slice(0, -1)


                $.cookie('chatbox_minimized', newCookie);
                $('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','block');
                $('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','block');
                $("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
        } else {

                var newCookie = chatboxtitle;

                if ($.cookie('chatbox_minimized')) {
                        newCookie += '|'+$.cookie('chatbox_minimized');
                }


                $.cookie('chatbox_minimized',newCookie);
                $('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
                $('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
        }

}

function restructureChatBoxes() {
        align = 0;
	chatBoxes=getChatBoxes();
        for (x in chatBoxes) {
                chatboxtitle = chatBoxes[x];

                if ($("#"+chatboxtitle).css('display') != 'none') {
                        if (align == 0) {
                                $("#"+chatboxtitle).css('right', '245px');
                        } else {
                                width = (align)*(225+7)+245;
                                $("#"+chatboxtitle).css('right', width+'px');
                        }
                        align++;
                }
        }
}

function closeChatBox(chatboxtitle) {
        $('#chatbox_'+chatboxtitle).css('display','none');
        restructureChatBoxes();
}

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};



function checkChatBoxInputKey(event,chatboxtextarea,chatboxtitle) {

	if(event.keyCode == 27){
		closeChatBox(chatboxtitle);
		return false;
	}
        if(event.keyCode == 13 && event.shiftKey == 0)  {
                message = $(chatboxtextarea).val();
                message = message.replace(/^\s+|\s+$/g,"");

                $(chatboxtextarea).val('');
                $(chatboxtextarea).focus();
                $(chatboxtextarea).css('height','44px');
                if (message != '') {
                        message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
                        $("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">me:&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
                        $("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			msg=$msg({to: chatboxtitle.replace('AT','@').replace('DOT','.'), from: connection.jid, type: 'chat'}).c("body").t(message);
			connection.send(msg)
                }
                return false;
        }

        var adjustedHeight = chatboxtextarea.clientHeight;
        var maxHeight = 94;

        if (maxHeight > adjustedHeight) {
                adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
                if (maxHeight)
                        adjustedHeight = Math.min(maxHeight, adjustedHeight);
                if (adjustedHeight > chatboxtextarea.clientHeight)
                        $(chatboxtextarea).css('height',adjustedHeight+8 +'px');
        } else {
                $(chatboxtextarea).css('overflow','auto');
        }

}
