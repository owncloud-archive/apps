/*
CardDavMATE - CardDav Web Client
Copyright (C) 2011-2012 Jan Mate <jan.mate@inf-it.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, - see <http://www.gnu.org/licenses/>.
*/

// VersionCheck (check for new version)
function netVersionCheck()
{
	$.ajax({
		type: 'GET',
		url: globalVersionCheckURL,
		cache: false,
		crossDomain: true,
		timeout: 30000,
		error: function(objAJAXRequest, strError){
			console.log("Error: [netVersionCheck: '"+globalVersionCheckURL+"'] code: '"+objAJAXRequest.status+"'");
			return false;
		},
		beforeSend: function(req) {
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: '',
		dataType: 'xml',
		complete: function(xml,textStatus)
		{
			if(textStatus!='success')
				return false;

			var count=0;
			var tmp=$(xml.responseXML).find('updates').find('carddavmate');
			var type=tmp.attr('type');
			var home=tmp.attr('homeURL');
			var version_txt=tmp.attr('version');

			if(type==undefined || type=='' || home==undefined || home=='' || version_txt==undefined || version_txt=='')
				return false;

			var version=version_txt.match(RegExp('^([0-9]+)\.([0-9]+)\.([0-9]+)(?:\.([0-9]+))?$'));
			if(version==null)
				return false;

			if(version[4]==null)
				version[4]='0';
			var version_int=version[1]*1000+version[2]*100+version[3]*10+version[4];

			var current_version=globalCardDavMATEVersion.match(RegExp('^([0-9]+)\.([0-9]+)\.([0-9]+)(?:\.([0-9]+))?'));
			if(current_version[4]==null)
				current_version[4]='0';
			var current_version_int=current_version[1]*1000+current_version[2]*100+current_version[3]*10+current_version[4];

			if(current_version_int<version_int)
			{
				var showNofication=false;

				if(globalNewVersionNotifyUsers.length==0)
					showNofication=true;
				else
				{
					for(var i=0;i<globalAccountSettings.length;i++)
						if(globalNewVersionNotifyUsers.indexOf(globalAccountSettings[i].userAuth.userName)!=-1)
						{
							showNofication=true;
							break;
						}
				}

				if(showNofication==true)
				{
					$('#System').find('div.update_h').html(localization[globalInterfaceLanguage].updateNotification.replace('%new_ver%','<span id="newversion" class="update_h"></span>').replace('%curr_ver%','<span id="version" class="update_h"></span>').replace('%url%','<span id="homeurl" class="update_h" onclick=""></span>'));

					$('#System').find('div.update_h').find('span#newversion').text(version_txt);
					$('#System').find('div.update_h').find('span#version').text(globalCardDavMATEVersion);
					$('#System').find('div.update_h').find('span#homeurl').attr('onclick','window.open(\''+home+'\')');
					$('#System').find('div.update_h').find('span#homeurl').text(home);

					setTimeout(function(){
						var orig_width=$('#System').find('div.update_d').width();
						$('#System').find('div.update_d').css('width', '0px');
						$('#System').find('div.update_d').css('display','');
						$('#System').find('div.update_d').animate({width: '+='+orig_width+'px'}, 500);
						},5000);
				}
			}
		}
	});
}

// Load the configuration from XML file
function netCheckAndCreateConfiguration(configurationURL)
{
	$.ajax({
		type: 'PROPFIND',
		url: configurationURL.href,
		cache: false,
		crossDomain: (typeof configurationURL.crossDomain=='undefined' ? true : configurationURL.crossDomain),
		xhrFields: {
			withCredentials: (typeof configurationURL.withCredentials=='undefined' ? false : configurationURL.withCredentials)
		},
		timeout: configurationURL.timeOut,
		error: function(objAJAXRequest, strError){
			console.log("Error: [netCheckAndCreateConfiguration: '"+configurationURL.href+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));
			$('#LoginLoader').fadeOut(1200);
			return false;
		},
		beforeSend: function(req) {
			if(globalLoginUsername!='' && globalLoginPassword!='')
				req.setRequestHeader('Authorization', basicAuth(globalLoginUsername,globalLoginPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			req.setRequestHeader('Depth', '0');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: '<?xml version="1.0" encoding="utf-8"?><D:propfind xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:carddav"><D:prop><D:current-user-principal/></D:prop></D:propfind>',
		dataType: 'xml',
		complete: function(xml,textStatus)
		{
			if(textStatus!='success')
				return false;

			var count=0;
			if($(xml.responseXML).children().filterNsNode('multistatus').children().filterNsNode('response').children().filterNsNode('propstat').children().filterNsNode('status').text().match(RegExp('200 OK$')))
			{
				if(typeof globalAccountSettings=='undefined')
					globalAccountSettings=[];

				globalAccountSettings[globalAccountSettings.length]=$.extend({},configurationURL);
				globalAccountSettings[globalAccountSettings.length-1].type='network';
				globalAccountSettings[globalAccountSettings.length-1].href=configurationURL.href+globalLoginUsername+'/';
				globalAccountSettings[globalAccountSettings.length-1].userAuth={userName: globalLoginUsername, userPassword: globalLoginPassword};
				count++;

				if(configurationURL.additionalResources!=undefined && configurationURL.additionalResources.length>0)
				{
					for(var i=0;i<configurationURL.additionalResources.length;i++)
					{
						globalAccountSettings[globalAccountSettings.length]=$.extend({},configurationURL);
						globalAccountSettings[globalAccountSettings.length-1].type='network';
						globalAccountSettings[globalAccountSettings.length-1].href=configurationURL.href+configurationURL.additionalResources[i]+'/';
						globalAccountSettings[globalAccountSettings.length-1].userAuth={userName: globalLoginUsername, userPassword: globalLoginPassword};
						count++;
					}
				}
			}

			if(count)
			{
				// show the logout button
				$('#Logout').css('display','');
				// start the client
				run();
			}
			else
				$('#LoginLoader').fadeOut(1200);
		}
	});
}

// Load the configuration from XML file
function netLoadConfiguration(configurationURL)
{
	$.ajax({
		type: 'GET',
		url: configurationURL.href,
		cache: false,
		crossDomain: (typeof configurationURL.crossDomain=='undefined' ? true : configurationURL.crossDomain),
		xhrFields: {
			withCredentials: (typeof configurationURL.withCredentials=='undefined' ? false : configurationURL.withCredentials)
		},
		timeout: configurationURL.timeOut,
		error: function(objAJAXRequest, strError){
			console.log("Error: [loadConfiguration: '"+configurationURL.href+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));
			$('#LoginLoader').fadeOut(1200);
			return false;
		},
		beforeSend: function(req) {
			if(globalLoginUsername!='' && globalLoginPassword!='')
				req.setRequestHeader('Authorization', basicAuth(globalLoginUsername,globalLoginPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: '',
		dataType: 'xml',
		complete: function(xml,textStatus)
		{
			if(textStatus!='success')
				return false;

			if(typeof globalAccountSettings=='undefined')
				globalAccountSettings=[];

			var count=0;
			$(xml.responseXML).children('resource').children('carddav').each(
				function(index, element)
				{
					var href=$(element).find('href').text();
					var tmp=$(element).find('hreflabel').text();
					var hreflabel=(tmp!='' ? tmp : null);
					var username=$(element).find('userauth').find('username').text();
					var password=$(element).find('userauth').find('password').text();
					var updateinterval=$(element).find('syncinterval').text();
					var timeout=$(element).find('timeout').text();
					var locktimeout=$(element).find('locktimeout').text();

					var tmp=$(element).find('withcredentials').text();
					var withcredentials=((tmp=='true' || tmp=='yes' || tmp=='1') ? true : false);
					var tmp=$(element).find('crossdomain').text();
					var crossdomain=((tmp=='false' || tmp=='no' || tmp=='0') ? false : true);

					globalAccountSettings[globalAccountSettings.length]={type: 'network', href: href, hrefLabel: hreflabel, crossDomain: crossdomain, withCredentials: withcredentials, userAuth: {userName: username, userPassword: password}, syncInterval: updateinterval, timeOut: timeout, lockTimeOut: locktimeout};

					count++;
				}
			);

			if(count)
			{
				// show the logout button
				$('#Logout').css('display','');
				// start the client
				run();
			}
			else
				$('#LoginLoader').fadeOut(1200);
		}
	});
}

function unlockCollection(inputContactObj)
{
	var tmp=inputContactObj.uid.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)([^/]+/)([^/]*)','i'));
	var collection_uid=tmp[1]+tmp[2]+'@'+tmp[3]+tmp[4]+tmp[5];

	var lockToken=globalResourceList.getCollectionByUID(collection_uid).lockToken;

	// resource not locked, we cannot unlock it
	if(lockToken=='undefined' || lockToken==null)
		return false;

	var put_href=tmp[1]+tmp[3]+tmp[4]+tmp[5];
	var put_href_part=tmp[4]+tmp[5];
	var resourceSettings=null;

	// find the original settings for the resource and user
	var tmp=inputContactObj.accountUID.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)','i'));
	var resource_href=tmp[1]+tmp[3]+tmp[4];
	var resource_user=tmp[2];

	for(var i=0;i<globalAccountSettings.length;i++)
		if(globalAccountSettings[i].href==resource_href && globalAccountSettings[i].userAuth.userName==resource_user)
			resourceSettings=globalAccountSettings[i];

	if(resourceSettings==null)
		return false;

	// the begin of each error message
	var errBegin=localization[globalInterfaceLanguage].errUnableUnlockBegin;

	$.ajax({
		type: 'UNLOCK',
		url: put_href,
		cache: false,
		crossDomain: (typeof resourceSettings.crossDomain=='undefined' ? true : resourceSettings.crossDomain),
		xhrFields: {
			withCredentials: (typeof resourceSettings.withCredentials=='undefined' ? false : resourceSettings.withCredentials)
		},
		timeout: resourceSettings.timeOut,
		error: function(objAJAXRequest, strError){
			switch(objAJAXRequest.status)
			{
				case 401:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp401),globalHideInfoMessageAfter);
					break;
				case 403:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp403),globalHideInfoMessageAfter);
					break;
				case 405:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp405),globalHideInfoMessageAfter);
					break;
				case 408:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp408),globalHideInfoMessageAfter);
					break;
				case 500:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp500),globalHideInfoMessageAfter);
					break;
				case 501:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp501),globalHideInfoMessageAfter);
					break;
				default:
					show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttpCommon.replace('%%',objAJAXRequest.status)),globalHideInfoMessageAfter);
					break;
			}
			return false;
		},
		beforeSend: function(req) {
			if(resourceSettings.userAuth.userName!='' && resourceSettings.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(resourceSettings.userAuth.userName,resourceSettings.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			// req.setRequestHeader('Depth', '0');
			if(lockToken!=null)
				req.setRequestHeader('Lock-Token', '<'+lockToken+'>');
		},
		data: '',
		complete: function(xml,textStatus)
		{
			if(textStatus=='success')
			{
				globalResourceList.setCollectionFlagByUID(collection_uid, 'lockToken', null);
				return true;
			}
		}
	});
}

function operationPerform(inputPerformOperation, inputContactObj, inputFilterUID)
{
	if(inputPerformOperation=='PUT')
	{
		var tmp=globalAddressbookList.getAddMeToContactGroups(inputContactObj.vcard, inputFilterUID);
		var inputContactObjArr=new Array(inputContactObj);
		inputContactObjArr=inputContactObjArr.concat(tmp);

		putVcardToCollection(inputContactObjArr, inputFilterUID, 'PUT_ALL', null);
	}
	else if(inputPerformOperation=='DELETE')
	{
		var tmp=globalAddressbookList.getRemoveMeFromContactGroups(inputContactObj.uid, null);
		var inputContactObjArr=new Array(inputContactObj);
		inputContactObjArr=tmp.concat(inputContactObjArr);

		if(inputContactObjArr.length==1)
			deleteVcardFromCollection(inputContactObjArr[0], inputFilterUID, 'DELETE_LAST');
		else
			putVcardToCollection(inputContactObjArr, inputFilterUID, 'DELETE_LAST', null);
	}
	else if(inputPerformOperation=='ADD_TO_GROUP')
	{
		var tmp=globalAddressbookList.getAddMeToContactGroups(inputContactObj.vcard, [inputContactObj.addToContactGroupUID]);
		tmp[0].uiObjects=inputContactObj.uiObjects
		var inputContactObjArr=tmp;

		putVcardToCollection(inputContactObjArr, inputFilterUID, 'ADD_TO_GROUP_LAST', null);
	}
	else if(inputPerformOperation=='DELETE_FROM_GROUP')
	{
		var inputContactObjArr=globalAddressbookList.getRemoveMeFromContactGroups(inputContactObj.uid, [inputFilterUID]);
		putVcardToCollection(inputContactObjArr, inputFilterUID, 'DELETE_FROM_GROUP_LAST', null);
	}
	else if(inputPerformOperation=='IRM_DELETE')
	{
		var tmp=globalAddressbookList.getRemoveMeFromContactGroups(inputContactObj.uid, null);
		var inputContactObjArr=new Array($.extend({withoutLockTocken: true}, inputContactObj), inputContactObj);	// first is used for PUT to destination resource (without lock token) and the second for the DELETE
		inputContactObjArr=tmp.concat(inputContactObjArr);

		putVcardToCollection(inputContactObjArr, inputFilterUID, 'IRM_DELETE_LAST', null);
	}
	else if(inputPerformOperation=='MOVE')
	{
		var tmp=globalAddressbookList.getRemoveMeFromContactGroups(inputContactObj.uid, null);
		var inputContactObjArr=new Array(inputContactObj);
		inputContactObjArr=tmp.concat(inputContactObjArr);

		if(inputContactObjArr.length==1)
			moveVcardToCollection(inputContactObjArr[0], inputFilterUID);
		else
			putVcardToCollection(inputContactObjArr, inputFilterUID, 'MOVE_LAST', null);
	}
}

function operationPerformed(inputPerformOperation, inputContactObj, loadContactObj)
{
	if(inputPerformOperation=='ADD_TO_GROUP_LAST')
	{
		// success icon
		setTimeout(function(){
			inputContactObj.uiObjects.resource.addClass('r_success');
			inputContactObj.uiObjects.resource.removeClass('r_operate');
			setTimeout(function(){
				inputContactObj.uiObjects.contact.animate({opacity: 1}, 750);
				inputContactObj.uiObjects.contact.draggable('option', 'disabled', false);
				inputContactObj.uiObjects.resource.removeClass('r_success');
				inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
			},1200);
		},1000);
	}
	// contact group operation (only one contact group is changed at once)
	else if(inputPerformOperation=='DELETE_FROM_GROUP_LAST')
	{
		// success message
		var duration=show_editor_message('out','message_success',localization[globalInterfaceLanguage].succContactDeletedFromGroup,globalHideInfoMessageAfter);

		// after the success message show the next automatically selected contact
		setTimeout(function(){
			$('#ResourceListOverlay').fadeOut(globalEditorFadeAnimation);
			$('#ABListOverlay').fadeOut(globalEditorFadeAnimation,function(){});
			$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});
		},duration);
	}
	// contact is added but it is hidden due to search filter
	else if($('#ABList div[data-id="'+jqueryEscapeSelector(inputContactObj.uid)+'"]').hasClass('search_hide'))
	{
		// load the modified contact
		globalAddressbookList.loadContactByUID(loadContactObj.uid);

		// success message
		var duration=show_editor_message('out','message_success',localization[globalInterfaceLanguage].succContactSaved,globalHideInfoMessageAfter);

		// after the success message show the next automatically selected contact
		setTimeout(function(){
			$('#ResourceListOverlay').fadeOut(globalEditorFadeAnimation);
			$('#ABListOverlay').fadeOut(globalEditorFadeAnimation,function(){});
			$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});
		},duration+globalHideInfoMessageAfter);
	}
	else
	{
		// load the modified contact
		globalAddressbookList.loadContactByUID(loadContactObj.uid);

		// success message
		show_editor_message('in','message_success',localization[globalInterfaceLanguage].succContactSaved,globalHideInfoMessageAfter);

		// presunut do jednej funkcie s tym co je vyssie
		$('#ResourceListOverlay').fadeOut(globalEditorFadeAnimation);
		$('#ABListOverlay').fadeOut(globalEditorFadeAnimation);
		$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});
	}

	unlockCollection(inputContactObj);
}

function lockAndPerformToCollection(inputContactObj, inputFilterUID, inputPerformOperation)
{
	var tmp=inputContactObj.uid.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)([^/]+/)([^/]*)','i'));
	var collection_uid=tmp[1]+tmp[2]+'@'+tmp[3]+tmp[4]+tmp[5];

	var put_href=tmp[1]+tmp[3]+tmp[4]+tmp[5];
	var put_href_part=tmp[4]+tmp[5];
	var resourceSettings=null;

	// find the original settings for the resource and user
	var tmp=inputContactObj.accountUID.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)','i'));
	var resource_href=tmp[1]+tmp[3]+tmp[4];
	var resource_user=tmp[2];

	for(var i=0;i<globalAccountSettings.length;i++)
		if(globalAccountSettings[i].href==resource_href && globalAccountSettings[i].userAuth.userName==resource_user)
			resourceSettings=globalAccountSettings[i];

	if(resourceSettings==null)
		return false;

	// the begin of each error message
	var errBegin=localization[globalInterfaceLanguage].errUnableLockBegin;

	$.ajax({
		type: 'LOCK',
		url: put_href,
		cache: false,
		crossDomain: (typeof resourceSettings.crossDomain=='undefined' ? true : resourceSettings.crossDomain),
		xhrFields: {
			withCredentials: (typeof resourceSettings.withCredentials=='undefined' ? false : resourceSettings.withCredentials)
		},
		timeout: resourceSettings.timeOut,
		error: function(objAJAXRequest, strError)
		{
			// if the server not supports LOCK request (Mac OS X Lion) we perform
			//  the operation without LOCK (even if it is dangerous and can cause data integrity errors)
			if(objAJAXRequest.status==501)
				operationPerform(inputPerformOperation, inputContactObj, inputFilterUID);
			// if the operation type is 'MOVE' we cannot show error messages, error icon is used instead
			else if(inputPerformOperation!='MOVE')
			{
				switch(objAJAXRequest.status)
				{
					case 401:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp401),globalHideInfoMessageAfter);
						break;
					case 403:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp403),globalHideInfoMessageAfter);
						break;
					case 405:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp405),globalHideInfoMessageAfter);
						break;
					case 408:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp408),globalHideInfoMessageAfter);
						break;
					case 500:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp500),globalHideInfoMessageAfter);
						break;
					default:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttpCommon.replace('%%',objAJAXRequest.status)),globalHideInfoMessageAfter);
						break;
				}

				// error icon
				setTimeout(function(){
					inputContactObj.uiObjects.resource.addClass('r_error');
					inputContactObj.uiObjects.resource.removeClass('r_operate');
					setTimeout(function(){
						inputContactObj.uiObjects.contact.animate({opacity: 1}, 1000);
						inputContactObj.uiObjects.contact.draggable('option', 'disabled', false);
						inputContactObj.uiObjects.resource.removeClass('r_error');
						inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
					},globalHideInfoMessageAfter);
				},globalHideInfoMessageAfter/10);
			}
			$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});

			return false;
		},
		beforeSend: function(req)
		{
			if(resourceSettings.userAuth.userName!='' && resourceSettings.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(resourceSettings.userAuth.userName,resourceSettings.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			req.setRequestHeader('Depth', '0');
			// we support only one contact group at once + the contact + reserve :)
			req.setRequestHeader('Timeout', 'Second-'+Math.ceil((resourceSettings.lockTimeOut!=undefined ? resourceSettings.lockTimeOut : 10000)/1000));
		},
		contentType: 'text/xml; charset=utf-8',
		processData: false,
		data: '<?xml version="1.0" encoding="utf-8"?><D:lockinfo xmlns:D=\'DAV:\'><D:lockscope><D:exclusive/></D:lockscope><D:locktype><D:write/></D:locktype><D:owner><D:href>'+escape(collection_uid)+'</D:href></D:owner></D:lockinfo>',
		dataType: 'text',
		complete: function(xml,textStatus)
		{
			if(textStatus=='success')
			{
				var lockToken=$(xml.responseXML).children().filterNsNode('prop').children().filterNsNode('lockdiscovery').children().filterNsNode('activelock').children().filterNsNode('locktoken').children().filterNsNode('href').text();
				globalResourceList.setCollectionFlagByUID(collection_uid, 'lockToken', (lockToken=='' ? null : lockToken));

				// We have a lock!
				if(lockToken!='')
				{
					// synchronously reload the contact changes (get the latest version of contact group vcards)
					var collection=globalResourceList.getCollectionByUID(collection_uid);
					collection.filterUID=inputFilterUID;

					netLoadCollection(collection, false, false, {call: 'operationPerform', args: {performOperation: inputPerformOperation, contactObj: inputContactObj, filterUID: inputFilterUID}});
					return true;
				}
				else
				{
					// We assume that empty lockToken means 423 Resource Locked error
					show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errResourceLocked),globalHideInfoMessageAfter);

					// error icon
					setTimeout(function(){
						inputContactObj.uiObjects.resource.addClass('r_error');
						inputContactObj.uiObjects.resource.removeClass('r_operate');
						setTimeout(function(){
							inputContactObj.uiObjects.contact.animate({opacity: 1}, 1000);
							inputContactObj.uiObjects.contact.draggable('option', 'disabled', false);
							inputContactObj.uiObjects.resource.removeClass('r_error');
							inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
						},globalHideInfoMessageAfter);
					},globalHideInfoMessageAfter/10);

					$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});
				}
			}
			return false;
		}
	});
}

function putVcardToCollectionMain(inputContactObj, inputFilterUID)
{
	if(inputContactObj.etag=='')
	{
		if(inputFilterUID!='')	// new contact with vCard group (we must use locking)
			lockAndPerformToCollection(inputContactObj, inputFilterUID, 'PUT');
		else	// new contact without vCard group (no locking required)
			putVcardToCollection(inputContactObj, inputFilterUID, 'PUT_ALL', null);
	}
	else	// existing contact modification (there is no support for contact group modification -> no locking required)
		putVcardToCollection(inputContactObj, inputFilterUID, 'PUT_ALL', null);
}

function putVcardToCollection(inputContactObjArr, inputFilterUID, recursiveMode, loadContactWithUID)
{
	if(!(inputContactObjArr instanceof Array))
		inputContactObjArr=[inputContactObjArr];

	var inputContactObj=inputContactObjArr.splice(0,1);
	inputContactObj=inputContactObj[0];

	// drag & drop inter-resoruce move (we need to change the object parameters)
	if(inputContactObj.newAccountUID!=undefined && inputContactObj.newUid!=undefined)
	{
		inputContactObj.accountUID=inputContactObj.newAccountUID;
		inputContactObj.uid=inputContactObj.newUid;
		inputContactObj.etag='';
	}

	var tmp=inputContactObj.uid.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)([^/]+/)([^/]*)','i'));

	var collection_uid=tmp[1]+tmp[2]+'@'+tmp[3]+tmp[4]+tmp[5];
	var lockToken=globalResourceList.getCollectionByUID(collection_uid).lockToken;

	// if inputContactObj.etag is empty, we have a newly created contact and need to create a .vcf file name for it
	if(inputContactObj.etag!='')	// existing contact
	{
		var put_href=tmp[1]+tmp[3]+tmp[4]+tmp[5]+tmp[6];
		var put_href_part=tmp[4]+tmp[5]+tmp[6];
	}
	else	// new contact
	{
		var vcardFile=hex_sha256(inputContactObj.vcard+(new Date().getTime()))+'.vcf';
		var put_href=tmp[1]+tmp[3]+tmp[4]+tmp[5]+vcardFile;
		var put_href_part=tmp[4]+tmp[5]+vcardFile;
		inputContactObj.uid+=vcardFile;
	}

	if(loadContactWithUID==null)	// store the first contact (it will be reloaded and marked as active)
		loadContactWithUID=inputContactObj;

	var resourceSettings=null;

	// find the original settings for the resource and user
	var tmp=inputContactObj.accountUID.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)','i'));
	var resource_href=tmp[1]+tmp[3]+tmp[4];
	var resource_user=tmp[2];

	for(var i=0;i<globalAccountSettings.length;i++)
		if(globalAccountSettings[i].href==resource_href && globalAccountSettings[i].userAuth.userName==resource_user)
			resourceSettings=globalAccountSettings[i];

	if(resourceSettings==null)
		return false;

	// the begin of each error message
	var errBegin=localization[globalInterfaceLanguage].errUnableSaveBegin;

	var vcardList= new Array();
	$.ajax({
		type: 'PUT',
		url: put_href,
		cache: false,
		crossDomain: (typeof resourceSettings.crossDomain=='undefined' ? true : resourceSettings.crossDomain),
		xhrFields: {
			withCredentials: (typeof resourceSettings.withCredentials=='undefined' ? false : resourceSettings.withCredentials)
		},
		timeout: resourceSettings.timeOut,
		error: function(objAJAXRequest, strError)
		{
			if(recursiveMode=='MOVE_LAST' || recursiveMode=='IRM_DELETE_LAST' || recursiveMode=='ADD_TO_GROUP_LAST')
			{
				// error icon
				setTimeout(function(){
					var moveContactObj=inputContactObjArr[inputContactObjArr.length-1];
					moveContactObj.uiObjects.resource.addClass('r_error');
					moveContactObj.uiObjects.resource.removeClass('r_operate');
					setTimeout(function(){
						moveContactObj.uiObjects.contact.animate({opacity: 1}, 1000);
						moveContactObj.uiObjects.contact.draggable('option', 'disabled', false);
						moveContactObj.uiObjects.resource.removeClass('r_error');
						moveContactObj.uiObjects.resource.droppable('option', 'disabled', false);
					},1200);
				},1000);
			}
			else
			{
				switch(objAJAXRequest.status)
				{
					case 401:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp401),globalHideInfoMessageAfter);
						break;
					case 403:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp403),globalHideInfoMessageAfter);
						break;
					case 405:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp405),globalHideInfoMessageAfter);
						break;
					case 408:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp408),globalHideInfoMessageAfter);
						break;
					case 412:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp412),globalHideInfoMessageAfter);
						break;
					case 500:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp500),globalHideInfoMessageAfter);
						break;
					default:
						show_editor_message('in','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttpCommon.replace('%%',objAJAXRequest.status)),globalHideInfoMessageAfter);
						break;
				}
			}

			// presunut do jednej funkcie s tym co je nizsie pri success
			//$('#ResourceListOverlay').fadeOut(1200);
			//$('#ABListOverlay').fadeOut(1200);
			$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});

			unlockCollection(inputContactObj);
			return false;
		},
		beforeSend: function(req)
		{
			if(resourceSettings.userAuth.userName!='' && resourceSettings.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(resourceSettings.userAuth.userName,resourceSettings.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			if(lockToken!=null && inputContactObj.withoutLockTocken!=true)
				req.setRequestHeader('Lock-Token', '<'+lockToken+'>');
			if(inputContactObj.etag!='')
				req.setRequestHeader('If-Match', inputContactObj.etag);
			else	// adding new contact
				req.setRequestHeader('If-None-Match', '*');
		},
		contentType: 'text/vcard; charset=utf-8',
		processData: true,
		data: inputContactObj.vcard,
		dataType: 'text',
		complete: function(xml,textStatus)
		{
			if(textStatus=='success')
			{
				if(inputContactObjArr.length==1 && (recursiveMode=='DELETE_LAST' || recursiveMode=='IRM_DELETE_LAST'))
				{
					deleteVcardFromCollection(inputContactObjArr[0], inputFilterUID, recursiveMode);
					return true;
				}
				else if(inputContactObjArr.length==1 && recursiveMode=='MOVE_LAST')
				{
					moveVcardToCollection(inputContactObjArr[0], inputFilterUID);
					return true;
				}
				var newEtag=xml.getResponseHeader('Etag');

				// We get the Etag from the PUT response header instead of new collection sync (if the server supports this feature)
				if(newEtag!=undefined && newEtag!='')
				{
					globalAddressbookList.removeContact(inputContactObj.uid,false);

					var vcard=normalizeVcard(inputContactObj.vcard);
					var categories='';
					if((vcard_element=vcard.match(vCard.pre['contentline_CATEGORIES']))!=null)
					{
						// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
						parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
						categories=parsed[4];
					}

					globalAddressbookList.insertContact({timestamp: new Date().getTime(), accountUID: inputContactObj.accountUID, uid: inputContactObj.uid, etag: newEtag, vcard: vcard, categories: categories, normalized: true}, true);
					globalQs.cache();	// update the active search

					globalAddressbookList.applyABFilter(inputFilterUID, recursiveMode=='DELETE_FROM_GROUP_LAST' || $('#ABList div[data-id="'+jqueryEscapeSelector(inputContactObj.uid)+'"]').hasClass('search_hide') ? true : false);
				}
				else	// otherwise mark collection for full sync
					globalResourceList.setCollectionFlagByUID(collection_uid, 'forceSync', true);

				if(inputContactObjArr.length>0)
					putVcardToCollection(inputContactObjArr, inputFilterUID, recursiveMode, loadContactWithUID);
				else
				{
					var collection=globalResourceList.getCollectionByUID(collection_uid);

					if(collection.forceSync===true)
					{
						globalResourceList.setCollectionFlagByUID(collection_uid, 'forceSync', false);
						collection.filterUID=inputFilterUID;

						// for DELETE_FROM_GROUP_LAST we need to force reload the contact (because the editor is in "edit" state = the contact is not loaded automatically)
						netLoadCollection(collection, false, recursiveMode=='DELETE_FROM_GROUP_LAST' ? true : false, {call: 'operationPerformed', args: {mode: recursiveMode, contactObj: inputContactObj, loadContact: loadContactWithUID}});
						return true;
					}
					operationPerformed(recursiveMode, inputContactObj, loadContactWithUID);
				}
				return true;
			}
			else
				unlockCollection(inputContactObj);
		}
	});
}

function moveVcardToCollection(inputContactObj, inputFilterUID)
{
	var tmp=inputContactObj.uid.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)([^/]+/)([^/]*)','i'));
	var collection_uid=tmp[1]+tmp[2]+'@'+tmp[3]+tmp[4]+tmp[5];
	var lockToken=globalResourceList.getCollectionByUID(collection_uid).lockToken;

	var put_href=tmp[1]+tmp[3]+tmp[4]+tmp[5]+tmp[6];
	var put_href_part=tmp[4]+tmp[5]+tmp[6];

	var resourceSettings=null;

	// find the original settings for the resource and user
	var tmp=inputContactObj.accountUID.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)','i'));
	var resource_href=tmp[1]+tmp[3]+tmp[4];
	var resource_user=tmp[2];

	for(var i=0;i<globalAccountSettings.length;i++)
		if(globalAccountSettings[i].href==resource_href && globalAccountSettings[i].userAuth.userName==resource_user)
			resourceSettings=globalAccountSettings[i];

	if(resourceSettings==null)
		return false;

	var vcardList= new Array();

	$.ajax({
		type: 'MOVE',
		url: put_href,
		cache: false,
		crossDomain: (typeof resourceSettings.crossDomain=='undefined' ? true : resourceSettings.crossDomain),
		xhrFields: {
			withCredentials: (typeof resourceSettings.withCredentials=='undefined' ? false : resourceSettings.withCredentials)
		},
		timeout: resourceSettings.timeOut,
		error: function(objAJAXRequest, strError)
		{
			// error icon
			setTimeout(function(){
				inputContactObj.uiObjects.resource.addClass('r_error');
				inputContactObj.uiObjects.resource.removeClass('r_operate');
				setTimeout(function(){
					inputContactObj.uiObjects.contact.animate({opacity: 1}, 1000);
					inputContactObj.uiObjects.contact.draggable('option', 'disabled', false);
					inputContactObj.uiObjects.resource.removeClass('r_error');
					inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
				},1200);
			},1000);

			unlockCollection(inputContactObj);
		},
		beforeSend: function(req)
		{
			if(resourceSettings.userAuth.userName!='' && resourceSettings.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(resourceSettings.userAuth.userName,resourceSettings.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			if(lockToken!=null)
				req.setRequestHeader('Lock-Token', '<'+lockToken+'>');
			req.setRequestHeader('Destination', inputContactObj.moveDest);
		},
		data: '',
		complete: function(text,textStatus)
		{
			if(textStatus=='success')
			{
				// success icon
				setTimeout(function(){
					// move is successfull we can remove the contact (no sync required)
					globalAddressbookList.removeContact(inputContactObj.uid,true);
					globalAddressbookList.applyABFilter(inputFilterUID, $('#ABList div[data-id="'+jqueryEscapeSelector(inputContactObj.uid)+'"]').hasClass('search_hide') ? true : false);

					inputContactObj.uiObjects.resource.addClass('r_success');
					inputContactObj.uiObjects.resource.removeClass('r_operate');
					setTimeout(function(){
						inputContactObj.uiObjects.resource.removeClass('r_success');
						inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
					},1200);
				},1000);
			}

			unlockCollection(inputContactObj);
		}
	});
}

function deleteVcardFromCollection(inputContactObj, inputFilterUID, recursiveMode)
{
	var tmp=inputContactObj.uid.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)([^/]+/)([^/]*)','i'));

	var collection_uid=tmp[1]+tmp[2]+'@'+tmp[3]+tmp[4]+tmp[5];
	var lockToken=globalResourceList.getCollectionByUID(collection_uid).lockToken;
	var put_href=tmp[1]+tmp[3]+tmp[4]+tmp[5]+tmp[6];
	var resourceSettings=null;

	// find the original settings for the resource and user
	var tmp=inputContactObj.accountUID.match(RegExp('^(https?://)([^@/]+(?:@[^@/]+)?)@([^/]+)(.*/)','i'));
	var resource_href=tmp[1]+tmp[3]+tmp[4];
	var resource_user=tmp[2];

	for(var i=0;i<globalAccountSettings.length;i++)
		if(globalAccountSettings[i].href==resource_href && globalAccountSettings[i].userAuth.userName==resource_user)
			resourceSettings=globalAccountSettings[i];

	if(resourceSettings==null)
		return false;

	// the begin of each error message
	var errBegin=localization[globalInterfaceLanguage].errUnableDeleteBegin;

	$.ajax({
		type: 'DELETE',
		url: put_href,
		cache: false,
		crossDomain: (typeof resourceSettings.crossDomain=='undefined' ? true : resourceSettings.crossDomain),
		xhrFields: {
			withCredentials: (typeof resourceSettings.withCredentials=='undefined' ? false : resourceSettings.withCredentials)
		},
		timeout: resourceSettings.timeOut,
		error: function(objAJAXRequest, strError)
		{
			// if the DELETE is performed as a part of inter-resource move operation (drag&drop)
			if(recursiveMode=='IRM_DELETE_LAST')
			{
				// error icon
				setTimeout(function(){
					inputContactObj.uiObjects.resource.addClass('r_error');
					inputContactObj.uiObjects.resource.removeClass('r_operate');
					setTimeout(function(){
						inputContactObj.uiObjects.contact.animate({opacity: 1}, 1000);
						inputContactObj.uiObjects.contact.draggable('option', 'disabled', false);
						inputContactObj.uiObjects.resource.removeClass('r_error');
						inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
					},1200);
				},1000);
			}
			else
			{
				switch(objAJAXRequest.status)
				{
					case 401:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp401),globalHideInfoMessageAfter);
						break;
					case 403:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp403),globalHideInfoMessageAfter);
						break;
					case 405:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp405),globalHideInfoMessageAfter);
						break;
					case 408:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp408),globalHideInfoMessageAfter);
						break;
					case 410:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp410),globalHideInfoMessageAfter);
						break;
					case 500:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttp500),globalHideInfoMessageAfter);
						break;
					default:
						show_editor_message('out','message_error',errBegin.replace('%%',localization[globalInterfaceLanguage].errHttpCommon.replace('%%',objAJAXRequest.status)),globalHideInfoMessageAfter);
						break;
				}
			}

			// presunut do jednej funkcie s tym co je nizsie pri success
			$('#ResourceListOverlay').fadeOut(globalEditorFadeAnimation);
			$('#ABListOverlay').fadeOut(globalEditorFadeAnimation);
			$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});

			unlockCollection(inputContactObj);
		},
		beforeSend: function(req) {
			if(resourceSettings.userAuth.userName!='' && resourceSettings.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(resourceSettings.userAuth.userName,resourceSettings.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			if(lockToken!=null)
				req.setRequestHeader('Lock-Token', '<'+lockToken+'>');
		},
		data: '',
		complete: function(text,textStatus)
		{
			if(textStatus=='success')
			{
				if(recursiveMode=='IRM_DELETE_LAST')
				{
					// success icon
					setTimeout(function(){
						// move is successfull we can remove the contact (no sync required)
						globalAddressbookList.removeContact(inputContactObj.uid,true);

						inputContactObj.uiObjects.resource.addClass('r_success');
						inputContactObj.uiObjects.resource.removeClass('r_operate');
						setTimeout(function(){
							inputContactObj.uiObjects.resource.removeClass('r_success');
							inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
						},1200);
					},1000);
				}
				else
				{
					// success message
					var duration=show_editor_message('out','message_success',localization[globalInterfaceLanguage].succContactDeleted,globalHideInfoMessageAfter);
					globalAddressbookList.removeContact(inputContactObj.uid,true);
					globalAddressbookList.applyABFilter(inputFilterUID, $('#ABList div[data-id="'+jqueryEscapeSelector(inputContactObj.uid)+'"]').hasClass('search_hide') ? true : false);

					// after the success message show the next automatically selected contact
					setTimeout(function(){
						// presunut do jednej funkcie s tym co je vyssie
						$('#ResourceListOverlay').fadeOut(globalEditorFadeAnimation);
						$('#ABListOverlay').fadeOut(globalEditorFadeAnimation);
						$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});
					},duration);
				}
			}
			unlockCollection(inputContactObj);
		}
	});
}

/*
iCloud auth (without this we have no access to iCloud photos)

function netiCloudAuth(inputResource)
{
	var re=new RegExp('^(https?://)([^/]+)','i');
	var tmp=inputResource.href.match(re);

	var uidBase=tmp[1]+inputResource.userAuth.userName+'@'+tmp[2];

	$.ajax({
		type: 'POST',
		url: 'https://setup.icloud.com/setup/ws/1/login',
		cache: false,
		crossDomain: (typeof inputResource.crossDomain=='undefined' ? true : inputResource.crossDomain),
		xhrFields: {
			withCredentials: (typeof inputResource.withCredentials=='undefined' ? false : inputResource.withCredentials)
		},
		timeout: inputResource.timeOut,
		error: function(objAJAXRequest, strError){
			console.log("Error: [netiCloudAuth: '"+uidBase+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));
			return false;
		},
		beforeSend: function(req) {
			req.setRequestHeader('Origin', 'https://www.icloud.com');
		},
		contentType: 'text/plain',
		processData: false,
		data: '{"apple_id":"'+inputResource.userAuth.userName+'","password":"'+inputResource.userAuth.userPassword+'","extended_login":false}',
		complete: function(xml,textStatus)
		{
			// iCloud cookie not set (no photo access)
			if(textStatus!='success')
				return false;
		}
	});
}
*/

/*
Permissions (from the davical wiki):
	all - aggregate of all permissions
	read - grants basic read access to the principal or collection.
	unlock - grants access to write content (i.e. update data) to the collection, or collections of the principal.
	read-acl - grants access to read ACLs on the collection, or collections of the principal.
	read-current-user-privilege-set - grants access to read the current user's privileges on the collection, or collections of the   write-acl - grants access to writing ACLs on the collection, or collections of the principal.
	write - aggregate of write-properties, write-content, bind & unbind
	write-properties - Grants access to update properties of the principal or collection. In DAViCal, when granted to a user principal, this will only grant access to update properties of the principal's collections and not the user principal itself. When granted to a group or resource principal this will grant access to update the principal properties.
	write-content - grants access to write content (i.e. update data) to the collection, or collections of the principal.
	bind - grants access to creating resources in the collection, or in collections of the principal. Created resources may be new collections, although it is an error to create collections within calendar collections.
	unbind - grants access to deleting resources (including collections) from the collection, or from collections of the principal.
*/

function netFindResource(inputResource)
{
	var re=new RegExp('^(https?://)([^/]+)','i');
	var tmp=inputResource.href.match(re);

	var baseHref=tmp[1]+tmp[2];
	var uidBase=tmp[1]+inputResource.userAuth.userName+'@'+tmp[2];

	$.ajax({
		type: 'PROPFIND',
		url: inputResource.href,
		cache: false,
		crossDomain: (typeof inputResource.crossDomain=='undefined' ? true : inputResource.crossDomain),
		xhrFields: {
			withCredentials: (typeof inputResource.withCredentials=='undefined' ? false : inputResource.withCredentials)
		},
		timeout: inputResource.timeOut,
		error: function(objAJAXRequest, strError){
			console.log("Error: [netFindResource: '"+uidBase+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));
			return false;
		},
		beforeSend: function(req) {
			if(inputResource.userAuth.userName!='' && inputResource.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(inputResource.userAuth.userName,inputResource.userAuth.userPassword));

			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			req.setRequestHeader('Depth', '0');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: '<?xml version="1.0" encoding="utf-8"?><D:propfind xmlns:D="DAV:"  xmlns:C="urn:ietf:params:xml:ns:carddav"><D:prop><D:current-user-privilege-set/><D:displayname/><D:resourcetype/><C:addressbook-home-set/></D:prop></D:propfind>',
		dataType: 'xml',
		complete: function(xml,textStatus)
		{
			if(textStatus!='success')
				return false;

// enable for iCloud cookie (to get "remote" photos from the iCloud server)
//			netiCloudAuth(inputResource);

			var response=$(xml.responseXML).children().filterNsNode('multistatus').children().filterNsNode('response');

			var addressbook_home=response.children().filterNsNode('propstat').children().filterNsNode('prop').children().filterNsNode('addressbook-home-set').children().filterNsNode('href').text();

			if(addressbook_home=='')	// addressbook-home-set has no 'href' value -> SabreDav
				addressbook_home=response.children().filterNsNode('href').text().replace('/principals/users/caldav.php','/caldav.php');

			if(addressbook_home.match(RegExp('^https?://','i'))!=null)	// absolute URL returned
				inputResource.abhref=addressbook_home;
			else	// relative URL returned
				inputResource.abhref=baseHref+addressbook_home;

			netLoadABResource(inputResource);
		}
	});
}

function netLoadABResource(inputResource)
{
	var re=new RegExp('^(https?://)([^/]+)','i');

	var tmp=inputResource.abhref.match(re);
	var baseHref=tmp[1]+tmp[2];
	var uidBase=tmp[1]+inputResource.userAuth.userName+'@'+tmp[2];

	var tmp=inputResource.href.match(RegExp('^(https?://)(.*)','i'));
	var origUID=tmp[1]+inputResource.userAuth.userName+'@'+tmp[2];

	$.ajax({
		type: 'PROPFIND',
		url: inputResource.abhref,
		cache: false,
		crossDomain: (typeof inputResource.crossDomain=='undefined' ? true : inputResource.crossDomain),
		xhrFields: {
			withCredentials: (typeof inputResource.withCredentials=='undefined' ? false : inputResource.withCredentials)
		},
		timeout: inputResource.timeOut,
		error: function(objAJAXRequest, strError){
			console.log("Error: [netLoadABResource: '"+uidBase+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));
			return false;
		},
		beforeSend: function(req) {
			if(inputResource.userAuth.userName!='' && inputResource.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(inputResource.userAuth.userName,inputResource.userAuth.userPassword));

			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			req.setRequestHeader('Depth', '1');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: '<?xml version="1.0" encoding="utf-8"?><D:propfind xmlns:D="DAV:"  xmlns:C="urn:ietf:params:xml:ns:carddav"><D:prop><D:current-user-privilege-set/><D:displayname/><D:resourcetype/><C:addressbook-home-set/></D:prop></D:propfind>',
		dataType: 'xml',
		complete: function(xml,textStatus)
		{
			if(textStatus!='success')
				return false;

			var resultTimestamp = new Date().getTime();

			$(xml.responseXML).children().filterNsNode('multistatus').children().filterNsNode('response').each(
				function(index, element)
				{
					$(element).children().filterNsNode('propstat').each(
						function(pindex, pelement)
						{
							var resources=$(pelement).children().filterNsNode('prop');
							// if resource is addressbook and collection (RFC requirement for CardDav)
							if(resources.children().filterNsNode('resourcetype').children().filterNsNode('addressbook').length==1 && resources.children().filterNsNode('resourcetype').children().filterNsNode('collection').length==1)
							{
								var permissions=new Array();
								resources.children().filterNsNode('current-user-privilege-set').children().filterNsNode('privilege').each(
									function(index, element)
									{
										$(element).children().each(
											function(index, element)
											{
												permissions[permissions.length]=$(element).prop('tagName').replace(/^[^:]+:/,'');
											}
										);
									}
								);
								var read_only=false;

								if(permissions.length>0 && permissions.indexOf('all')==-1 && permissions.indexOf('write')==-1 &&  permissions.indexOf('write-content')==-1)
									read_only=true;

								var href=$(element).children().filterNsNode('href').text();
								var displayvalue=resources.children().filterNsNode('displayname').text();

								var tmp_dv=href.match(RegExp('.*/([^/]+)/$','i'));
								if(displayvalue=='')	// MacOSX Lion Server
									displayvalue=tmp_dv[1];

								// insert the resource
								globalResourceList.insertResource({timestamp: resultTimestamp, uid: uidBase+href, timeOut: inputResource.timeOut, displayvalue: displayvalue, userAuth: inputResource.userAuth, url: baseHref, accountUID: origUID, href: href, hrefLabel: inputResource.hrefLabel, permissions: {full: permissions, read_only: read_only}, crossDomain: inputResource.crossDomain, withCredentials: inputResource.withCredentials});
							}
						}
					);
				}
			);

			// remove deleted resources
			globalResourceList.removeOldResources(inputResource.href, resultTimestamp);

			// by default load the first resource
			globalResourceList.loadFirstAddressbook();
		}
	});
}

function netLoadCollection(inputCollection, forceLoad, forceLoadNextContact, innerOperationData)
{
	if(inputCollection.forceSyncPROPFIND==true)
		var requestText='<?xml version="1.0" encoding="utf-8"?><D:propfind xmlns:D="DAV:"  xmlns:C="urn:ietf:params:xml:ns:carddav"><D:prop><D:getetag/><D:getcontenttype/></D:prop></D:propfind>';
	else	// if inputCollection.forceSyncPROPFIND is undefined or false
		var requestText='<?xml version="1.0" encoding="utf-8"?><D:sync-collection xmlns:D="DAV:">'+(forceLoad==true || inputCollection.syncToken==undefined ? '<D:sync-token/>' : '<D:sync-token>'+inputCollection.syncToken+'</D:sync-token>')+ '<D:prop><D:getetag/><D:getcontenttype/></D:prop></D:sync-collection>';

	$.ajax({
		type: (inputCollection.forceSyncPROPFIND==true ? 'PROPFIND' : 'REPORT'),
		url: inputCollection.url+inputCollection.href,
		cache: false,
		crossDomain: (typeof inputCollection.crossDomain=='undefined' ? true : inputCollection.crossDomain),
		xhrFields: {
			withCredentials: (typeof inputCollection.withCredentials=='undefined' ? false : inputCollection.withCredentials)
		},
		timeout: inputCollection.timeOut,
		error: function(objAJAXRequest, strError){
			if((objAJAXRequest.status==400 /* bad request */ || objAJAXRequest.status==501 /* unimplemented */) && inputCollection.forceSyncPROPFIND!=true /* prevent recursion */)
			{
				netLoadCollection(globalResourceList.setCollectionFlagByUID(inputCollection.uid, 'forceSyncPROPFIND', true), forceLoad, forceLoadNextContact, innerOperationData);
				return true;
			}
			else
			{
				console.log("Error: [netLoadCollection: '"+inputCollection.url+inputCollection.href+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));
				return false;
			}
		},
		beforeSend: function(req) {
			if(inputCollection.userAuth.userName!='' && inputCollection.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(inputCollection.userAuth.userName,inputCollection.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
			req.setRequestHeader('Depth', '1');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: requestText,
		dataType: 'xml',
		complete: function(xml,textStatus)
		{
			if(textStatus!='success')
				return false;

			var vcardList=new Array();
			$(xml.responseXML).children().filterNsNode('multistatus').children().filterNsNode('response').each(
				function(index, element)
				{
					var result=$(element).find('*').filterNsNode('status').text();	// note for 404 there is no propstat!
					if(result.match(RegExp('200 OK$')))	// HTTP OK
						vcardList[vcardList.length]={etag: $(element).children().filterNsNode('propstat').children().filterNsNode('prop').children().filterNsNode('getetag').text(), href: $(element).children().filterNsNode('href').text()};
					else if(result.match(RegExp('404 Not Found$')))	// HTTP Not Found
						vcardList[vcardList.length]={deleted: true, etag: $(element).children().filterNsNode('propstat').children().filterNsNode('prop').children().filterNsNode('getetag').text(), href: $(element).children().filterNsNode('href').text()};
				}
			);

			// store the syncToken
			if(inputCollection.forceSyncPROPFIND!=true)
				inputCollection.syncToken=$(xml.responseXML).children().filterNsNode('multistatus').children().filterNsNode('sync-token').text();

			// we must call the netLoadAddressbook even if we get empty vcardList
			netLoadAddressbook(inputCollection, vcardList, (inputCollection.forceSyncPROPFIND!=true ? true : false), forceLoadNextContact, innerOperationData);
		}
	});
}

function netLoadAddressbook(inputCollection, vcardList, syncReportSupport, forceLoadNext, innerOperationData)
{
	var vcardChangedList = new Array();
	var resultTimestamp = new Date().getTime();

	if(syncReportSupport==true)
	{
		for(var i=0;i<vcardList.length;i++)
			if(vcardList[i].deleted==true)
				globalAddressbookList.removeContact(inputCollection.uid+vcardList[i].href.replace(RegExp('.*/',''),''),true);
			else
				vcardChangedList[vcardChangedList.length]=vcardList[i].href;
	}
	else	// no sync-collection REPORT supported (we need to delete contacts by timestamp comparison)
	{
		for(var i=0;i<vcardList.length;i++)
		{
			var uid=inputCollection.uid+vcardList[i].href.replace(RegExp('.*/',''),'');
			if(!globalAddressbookList.checkAndTouchIfExists(uid,vcardList[i].etag,resultTimestamp))
				vcardChangedList[vcardChangedList.length]=vcardList[i].href;
		}
		globalAddressbookList.removeOldContacts(inputCollection.uid, resultTimestamp);
	}

	// not loaded vCards from the last multiget (if any)
	if(inputCollection.pastUnloaded!=undefined && inputCollection.pastUnloaded.length>0)
		vcardChangedList=vcardChangedList.concat(inputCollection.pastUnloaded).sort().unique();

	// if nothing is changed on the server return
	if(vcardChangedList.length==0)
	{
		$('#AddContact').prop('disabled',false);
		$('#ABListLoader').css('display','none');

		if(innerOperationData!=null)
		{
			if(innerOperationData.call=='operationPerform')
				operationPerform(innerOperationData.args.performOperation, innerOperationData.args.contactObj, innerOperationData.args.filterUID);
			else if(innerOperationData.call=='operationPerformed')
				operationPerformed(innerOperationData.args.mode, innerOperationData.args.contactObj, innerOperationData.args.loadContact);
		}
		return true;
	}

	multigetData='<?xml version="1.0" encoding="utf-8"?><E:addressbook-multiget xmlns:E="urn:ietf:params:xml:ns:carddav"><A:prop xmlns:A="DAV:"><A:getetag/><E:address-data/></A:prop><A:href xmlns:A="DAV:">'+vcardChangedList.join('</A:href><A:href xmlns:A="DAV:">')+'</A:href></E:addressbook-multiget>';

	$.ajax({
		type: 'REPORT',
		url: inputCollection.url+inputCollection.href,
		cache: false,
		crossDomain: (typeof inputCollection.crossDomain=='undefined' ? true : inputCollection.crossDomain),
		xhrFields: {
			withCredentials: (typeof inputCollection.withCredentials=='undefined' ? false : inputCollection.withCredentials)
		},
		timeout: inputCollection.timeOut,
		error: function(objAJAXRequest, strError){
			// unable to load vCards, try to load them next time
			inputCollection.pastUnloaded=vcardChangedList;

			console.log("Error: [netLoadAddressbook: '"+inputCollection.url+inputCollection.href+"'] code: '"+objAJAXRequest.status+"'"+(objAJAXRequest.status==0 ? ' - see http://www.inf-it.com/carddavmate/misc/readme_network.txt' : ''));

			if(innerOperationData!=null && innerOperationData.call=='operationPerform')
			{
				show_editor_message('out','message_error',localization[globalInterfaceLanguage].errUnableSync,globalHideInfoMessageAfter);

				// error icon
				setTimeout(function(){
					inputContactObj.uiObjects.resource.addClass('r_error');
					inputContactObj.uiObjects.resource.removeClass('r_operate');
					setTimeout(function(){
						inputContactObj.uiObjects.contact.animate({opacity: 1}, 1000);
						inputContactObj.uiObjects.contact.draggable('option', 'disabled', false);
						inputContactObj.uiObjects.resource.removeClass('r_error');
						inputContactObj.uiObjects.resource.droppable('option', 'disabled', false);
					},globalHideInfoMessageAfter);
				},globalHideInfoMessageAfter/10);

				$('#ABContactOverlay').fadeOut(globalEditorFadeAnimation,function(){$('#AddContact').prop('disabled',false);});
			}
			return false;
		},
		beforeSend: function(req) {
			if(inputCollection.userAuth.userName!='' && inputCollection.userAuth.userPassword!='')
				req.setRequestHeader('Authorization', basicAuth(inputCollection.userAuth.userName,inputCollection.userAuth.userPassword));
			req.setRequestHeader('X-client', 'CardDavMATE '+globalCardDavMATEVersion+' (INF-IT CardDav Web Client)');
		},
		contentType: 'text/xml; charset=utf-8',
		processData: true,
		data: multigetData,
		dataType: 'xml',
		complete: function(xml, textStatus)
		{
			if(textStatus!='success')
				return false;

			inputCollection.pastUnloaded=[];	// all vCards loaded

			$(xml.responseXML).children().filterNsNode('multistatus').children().filterNsNode('response').each(
				function(index, element)
				{
					if($(element).children().filterNsNode('propstat').children().filterNsNode('status').text().match(RegExp('200 OK$')))	// HTTP OK
					{
						var etag=$(element).children().filterNsNode('propstat').children().filterNsNode('prop').children().filterNsNode('getetag').text();
						var uid=inputCollection.uid+$(element).children().filterNsNode('href').text().replace(RegExp('.*/',''),'');

						var vcard_raw=$(element).children().filterNsNode('propstat').children().filterNsNode('prop').children().filterNsNode('address-data').text();

						if(vcard_raw!='')
							var result=basicRFCFixesAndCleanup(vcard_raw);
						else
							return true;	// continue for jQuery

						// check the vCard validity here
						// ...
						// ...
						if($('#AddContact').attr('data-url')==inputCollection.uid)
						{
							globalAddressbookList.insertContact({timestamp: resultTimestamp, accountUID: inputCollection.accountUID, uid: uid, etag: etag, vcard: result.vcard, categories: result.categories, normalized: false}, (innerOperationData!=null && innerOperationData.call=='operationPerformed' && innerOperationData.args.mode=='DELETE_FROM_GROUP_LAST'));	// if inner operation is DELETE_FROM_GROUP_LAST we force reload the contact
						}
						else	// "concurrent" write in progress
						{
							globalAddressbookList.removeCollectionContacts(inputCollection.uid);
							return false;
						}
					}
				}
			);

			// update the active search
			globalQs.cache();

			// if no "concurrent" write in progress we need to update the group filter
			if($('#AddContact').attr('data-url')==inputCollection.uid)
				globalAddressbookList.applyABFilter(inputCollection.filterUID, forceLoadNext);

			$('#AddContact').prop('disabled',false);
			$('#ABListLoader').css('display','none');

			if(innerOperationData!=null)
			{
				if(innerOperationData.call=='operationPerform')
					operationPerform(innerOperationData.args.performOperation, innerOperationData.args.contactObj, innerOperationData.args.filterUID);
				else if(innerOperationData.call=='operationPerformed')
					operationPerformed(innerOperationData.args.mode, innerOperationData.args.contactObj, innerOperationData.args.loadContact);
			}

			return true;
		}
	});
}
