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
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function loadAddressbook(inputCollection, forceLoad)
{
	if(forceLoad!=true && globalWindowFocus==false)
		return false;

	netLoadCollection(inputCollection, forceLoad, false, null);
}

// AddressbookList Class
function AddressbookList()
{
	this.contacts=new Array();
	this.contact_groups=new Array();
	this.contact_categories=new Object();
	this.contact_companies=new Object();
	this.contactLoaded=null;
	this.contactGroupLoaded=null;

	this.reset=function()
	{
		this.contacts.splice(0,this.contacts.length);
		this.contact_groups.splice(0,this.contact_groups.length);	// these are not removed from the interface (it's OK)
		this.contact_categories=new Object();
		this.contact_companies=new Object();
		this.contactLoaded=null;
		this.contactGroupLoaded=null;
	}

	this.getNewUID=function()
	{
		var newUID=null;
		var found=true;

		while(found==true)
		{
			newUID=generateUID();
			found=false;
			for(i=0;i<this.contacts.length;i++)
				if(this.contacts[i].uid!=undefined && this.contacts[i].uid==newUID)	// undefined = contactlist "alpha header" values
					found=true;
		}
		return newUID;
	}

	this.getLoadedContactUID=function()
	{
		if(this.contactLoaded!=null)
			return this.contactLoaded.uid;
		else
			return '';
	}

	this.getSortKey=function(vcard_clean,inputSettings)
	{
		var tmp=inputSettings.replaceAll(' ','');
		tmp=tmp.replace(RegExp('surname|lastname|last|family','i'),'0');
		tmp=tmp.replace(RegExp('firstname|first|given','i'),'1');
		tmp=tmp.replace(RegExp('middlename|middle','i'),'2');
		tmp=tmp.replace(RegExp('prefix','i'),'3');
		tmp=tmp.replace(RegExp('suffix','i'),'4');
		tmp=tmp.split(',');

		var vcard_element=('\r\n'+vcard_clean).match(vCard.pre['contentline_N']);
		if(vcard_element!=null && vcard_element.length==1)	// if the N attribute is not present exactly once, vCard is considered invalid
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			var parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [0]->Family, [1]->Given, [2]->Middle, [3]->Prefix, [4]->Suffix
			var parsed_value=vcardSplitValue(parsed[4],';');

			var sort_value='';
			for(var i=0;i<tmp.length;i++)
			{
				if(parsed_value[tmp[i]]!=undefined)
					sort_value+=parsed_value[tmp[i]];
				if(sort_value!='' && tmp[i+1]!=undefined && parsed_value[tmp[i+1]]!=undefined && parsed_value[tmp[i+1]]!='')
					sort_value+=' ';
			}

			if(sort_value=='')	// if no N value present, we use the FN instead
			{
				var vcard_element2=('\r\n'+vcard_clean).match(vCard.pre['contentline_FN']);
				if(vcard_element2!=null && vcard_element2.length==1)	// if the FN attribute is not present exactly once, vCard is considered invalid
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					var parsed=vcard_element2[0].match(vCard.pre['contentline_parse']);
					var sort_value=parsed[4];
				}
			}
			return sort_value;
		}
		else
			return false;
	}

	this.isContactGroup=function(vcard_clean)
	{
		if(('\r\n'+vcard_clean).match(vCard.pre['X-ADDRESSBOOKSERVER-KIND'])!=null)
			return true;
		else
			return false;
	}

	this.getRemoveMeFromContactGroups=function(inputUid, inputContactGroupsUidArr)
	{
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].uid==inputUid)
			{
				var changedContactGroups=new Array();

				if((vcard_element=this.contacts[i].vcard.match(vCard.pre['contentline_UID']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

					for(var j=0;j<this.contact_groups.length;j++)
					{
						if(inputContactGroupsUidArr!=null)
						{
							var skipThis=true;
							for(var k=0;k<inputContactGroupsUidArr.length;k++)
								if(inputContactGroupsUidArr[k]==this.contact_groups[j].uid)
								{
									skipThis=false;
									break;
								}

							if(skipThis==true)
								continue;
						}

						var vcard=this.contact_groups[j].vcard;

						var changedVcard=null;
						if(vcard!=(changedVcard=vcard.replaceAll('\r\nX-ADDRESSBOOKSERVER-MEMBER:urn:uuid:'+parsed[4]+'\r\n','\r\n')))
						{
							// update the revision in the group vcard
							var d = new Date();
							utc=d.getUTCFullYear()+(d.getUTCMonth()+1<10 ? '0':'')+(d.getUTCMonth()+1)+(d.getUTCDate()<10 ? '0':'')+d.getUTCDate()+'T'+(d.getUTCHours()<10 ? '0':'')+d.getUTCHours()+(d.getUTCMinutes()<10 ? '0':'')+d.getUTCMinutes()+(d.getUTCSeconds()<10 ? '0':'')+d.getUTCSeconds()+'Z';
							changedVcard=changedVcard.replace(RegExp('\r\nREV:.*\r\n','mi'),'\r\nREV:'+utc+'\r\n');

							// "copy" of the original object
							changedContactGroups[changedContactGroups.length]=$.extend({},this.contact_groups[j]);
							// new modified vcard group
							changedContactGroups[changedContactGroups.length-1].vcard=changedVcard;
						}
					}
				}
				return changedContactGroups;
			}
		return null;
	}

	this.getAddMeToContactGroups=function(inputVcard, inputContactGroupsUidArr)
	{
		if(!(inputContactGroupsUidArr instanceof Array))
			inputContactGroupsUidArr=[inputContactGroupsUidArr];

		vcard_element=inputVcard.match(vCard.pre['contentline_UID']);

		// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
		parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

		var changedContactGroups=new Array();
		for(var j=0;j<this.contact_groups.length;j++)
			for(var k=0;k<inputContactGroupsUidArr.length;k++)
				if(this.contact_groups[j].uid==inputContactGroupsUidArr[k])
				{
					// if the uuid is already a member we remove it from contact-group to avoid duplicate membership
					var vcard=this.contact_groups[j].vcard.replaceAll('\r\nX-ADDRESSBOOKSERVER-MEMBER:urn:uuid:'+parsed[4]+'\r\n','\r\n');
					var tmp=vcard.split('\r\n');
					tmp.splice(tmp.length-2,0,'X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:'+parsed[4]);
					var changedVcard=tmp.join('\r\n');

					var d = new Date();
					utc=d.getUTCFullYear()+(d.getUTCMonth()+1<10 ? '0':'')+(d.getUTCMonth()+1)+(d.getUTCDate()<10 ? '0':'')+d.getUTCDate()+'T'+(d.getUTCHours()<10 ? '0':'')+d.getUTCHours()+(d.getUTCMinutes()<10 ? '0':'')+d.getUTCMinutes()+(d.getUTCSeconds()<10 ? '0':'')+d.getUTCSeconds()+'Z';
					changedVcard=changedVcard.replace(RegExp('\r\nREV:.*\r\n','mi'),'\r\nREV:'+utc+'\r\n');

					// "copy" of the original object
					changedContactGroups[changedContactGroups.length]=$.extend({},this.contact_groups[j]);
					// new modified vcard group
					changedContactGroups[changedContactGroups.length-1].vcard=changedVcard;
				}
		return changedContactGroups;
	}

	// Contact group list is not sorted, instead "insert sort" is performed
	this.insertContactGroup=function(inputContact)
	{
		if((inputContact.sortkey=this.getSortKey(inputContact.vcard,globalCollectionSort))===false || (inputContact.displayvalue=this.getSortKey(inputContact.vcard,globalCollectionDisplay))===false)
			return false;	//invalid vcard

		makeActive=null;

		// do not insert entry with duplicate UID
		for(var i=0;i<this.contact_groups.length;i++)
			if(this.contact_groups[i].uid==inputContact.uid)
			{
				if(this.contact_groups[i].displayvalue==inputContact.displayvalue)
				{
					this.contact_groups[i]=inputContact;
					return 0;
				}
				else
				{
					if(this.contactGroupLoaded!=null && this.contactGroupLoaded.uid==inputContact.uid)
						makeActive=inputContact.uid;

					// the contact group name is changed and must be moved to correct place (we first remove it and then reinsert)
					this.removeContactGroup(inputContact.uid,false);
					break;
				}
			}

		// find the index where to insert the new contact group
		var insertIndex=this.contact_groups.length;
		for(var i=0;i<this.contact_groups.length;i++)
			if(this.contact_groups[i].sortkey.customCompare(inputContact.sortkey,globalSortAlphabet,1,false)==1)
			{
				insertIndex=i;
				break;
			}

		// insert the contact group
		this.contact_groups.splice(insertIndex,0,inputContact);

		// insert the contact group to interface
		var newElement=$('#ResourceListTemplate').find('.resource_item').find('.contact_group').find('.group').clone().wrap('<div>');
		// the onclick event is disabled until the last drag&drop operation is completed (the class*="r_" is a little bit weak but works)
		newElement=newElement.attr('onclick','if($(this).parents(\':eq(2)\').find(\'[class*="r_"]\').length>0) return false; else globalResourceList.loadAddressbookByUID(this.getAttribute(\'data-id\'));');
		newElement=newElement.attr('data-id',inputContact.uid);
		newElement.text(vcardUnescapeValue(inputContact.displayvalue));
		newElement.css('display','');
		newElement=newElement.parent().html();

		// insert the contact group only if it not exists in interface (these are not removed from the interface when the users switch to different collection)
		if($('#ResourceList').find('[data-id="'+jqueryEscapeSelector(inputContact.uid.replace(RegExp('/[^/]*$',''),'/'))+'"]').parent().find('.contact_group').find('[data-id="'+jqueryEscapeSelector(inputContact.uid)+'"]').length==0)
			$('#ResourceList').find('[data-id="'+jqueryEscapeSelector(inputContact.uid.replace(RegExp('/[^/]*$',''),'/'))+'"]').parent().find('.contact_group').children().eq(insertIndex).after(newElement);

		// make the area droppable if the collection is not read-only
		if(globalResourceList.getCollectionPrivByUID(inputContact.uid.replace(RegExp('[^/]*$',''),''))==false)
			$('#ResourceList').find('[data-id="'+jqueryEscapeSelector(inputContact.uid.replace(RegExp('[^/]*$',''),''))+'"]').parent().find('.contact_group').children().eq(insertIndex+1).droppable({
				accept: '.ablist_item',
				tolerance: 'pointer',
				hoverClass: 'group_dropped_to',
				drop: function(event, ui){
					// animate the clone of the dropped (draggable) element
					var tmp=ui.helper.clone();
					tmp.appendTo('body')
					.animate({opacity: 0, color: 'transparent', height: 0, width: 0, fontSize: 0, lineHeight: 0, paddingLeft: 0, paddingRight: 0},750,function(){tmp.remove()});

					// disallow to drag the original dropped element until the processing is finished
					ui.draggable.draggable('option', 'disabled', true);

					// animate the original dropped element
					ui.draggable.animate({opacity: 0.3}, 750);

					// disallow to drop any new element until the processing is finished
					$(this).droppable('option', 'disabled', true);

					// show the loader icon
					$(this).addClass('r_operate');

					var tmp2=globalAddressbookList.getContactByUID(ui.draggable.attr('data-id'));
					tmp2.addToContactGroupUID=$(this).attr('data-id');
					tmp2.uiObjects={contact: ui.draggable, resource: $(this)};

					lockAndPerformToCollection(tmp2, $('#AddContact').attr('data-filter-url'), 'ADD_TO_GROUP');
				}
			});


		// load the contact group if it was selected
		if(makeActive!=null)
		{
			$('#ResourceList').find('.resource_item').find('.resource_selected').removeClass('resource_selected');
			$('#ResourceList').find('[data-id='+jqueryEscapeSelector(makeActive.replace(RegExp('[^/]*$',''),''))+']').addClass('resource_selected');
			$('#ResourceList').find('[data-id='+jqueryEscapeSelector(makeActive)+']').addClass('resource_selected');

			this.applyABFilter(makeActive, false);
		}
	}

	// hide/show contacts in the interface according to contactGroupUid or search filter in the interface (contactGroupUid==false)
	this.applyABFilter=function(contactGroupUid, inputForceLoadNext)
	{
		var vcard=null;
		if(contactGroupUid===false)
		{
			if(this.contactGroupLoaded!=null)
				vcard=this.contactGroupLoaded.vcard;
		}
		else
		{
			this.contactGroupLoaded=null;
			// remember the loaded contact group
			if(contactGroupUid!='')
				for(var i=0;i<this.contact_groups.length;i++)
					if(this.contact_groups[i].uid==contactGroupUid)
					{
						this.contactGroupLoaded=this.contact_groups[i];
						vcard=this.contact_groups[i].vcard;
						break;
					}
		}

		// no contactGroup filter specified
		if(this.contactGroupLoaded==null)
		{
			// set all (except the hidden) contacts as active
			for(var i=0;i<this.contacts.length;i++)
				if(this.contacts[i].headerOnly==undefined)
				{
					if($('#ABList div[data-id="'+jqueryEscapeSelector(this.contacts[i].uid)+'"]').hasClass('search_hide')==false)
						this.contacts[i].show=true;
					else
						this.contacts[i].show=false;
				}
		}
		else
		{
			var previousActiveIndex=null;	// used to find the nearest contact and set it as selected

			// set all contacts as inactive
			for(var i=0;i<this.contacts.length;i++)
				if(this.contacts[i].headerOnly==undefined)
				{
					if(this.contacts[i].show==true && $('#ABList div[data-id="'+jqueryEscapeSelector(this.contacts[i].uid)+'"]').hasClass('ablist_item_selected'))
						previousActiveIndex=i;

					this.contacts[i].show=false;
				}

			var vcardUIDList=new Array();
			// get the members of the array group
			while((vcard_element=vcard.match(vCard.pre['X-ADDRESSBOOKSERVER-MEMBER']))!=null)
			{
				// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
				parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
				vcardUIDList[vcardUIDList.length]=parsed[4].replace('urn:uuid:','');
				// remove the processed parameter
				vcard=vcard.replace(vcard_element[0],'\r\n');
			}

			// update the contacts' "show" attribute
			for(var i=0;i<vcardUIDList.length;i++)
				for(var j=0;j<this.contacts.length;j++)
					if(this.contacts[j].headerOnly==undefined)
					{
						vcard_element=this.contacts[j].vcard.match(vCard.pre['contentline_UID']);

						if(vcard_element!=null)	// only for contacts with UID (non-RFC contacts not contains UID)
						{
							// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
							parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

							if(vcardUIDList[i]==parsed[4] && $('#ABList div[data-id="'+jqueryEscapeSelector(this.contacts[j].uid)+'"]').hasClass('search_hide')==false)
								this.contacts[j].show=true;
						}
					}
		}

		var lastActive=null;
		var prevHeader=null;
		var lastContactForHeader=this.contacts.length-1;
		// the show attribute is now set, we can make changes in the interface
		for(var i=this.contacts.length-1;i>=0;i--)
		{
			if(this.contacts[i].headerOnly==undefined)
			{
				// find the previous header index
				for(var j=i-1;j>=0;j--)
					if(this.contacts[j].headerOnly==true)
					{
						prevHeader=j;
						break;
					}

				switch(this.contacts[i].show)
				{
					case false:
						$('#ABList').children().eq(i+1).css('display','none');
						if($('#ABList').children().eq(i+1).hasClass('ablist_item_selected'))
							lastActive=i;

						var hideHeader=true;
						for(j=prevHeader+1;j<=lastContactForHeader;j++)
							if(this.contacts[j].show==true)
								hideHeader=false;

						if(hideHeader)
							$('#ABList').children().eq(prevHeader+1).css('display','none');

						break;
					case true:
						// set the contact header to visible
						$('#ABList').children().eq(prevHeader+1).css('display','');

						// set the contact to visible
						$('#ABList').children().eq(i+1).css('display','');
						break;
				}
			}
			else
				lastContactForHeader=i-1;
		}

		// the previously loaded contact is hidden or not exists we need to select a new one
		if(inputForceLoadNext==true || $('[id=vcard_editor]').attr('data-editor-state')!='edit' && (lastActive!=null || $('#ABList').find('.ablist_item_selected').length==0))
		{
			var nextCandidateToLoad=null;
			// get the nearest candidate to load
			//  if we can go forward
			for(j=(previousActiveIndex == null ? 0 : previousActiveIndex)+1;j<this.contacts.length;j++)
				if(this.contacts[j].headerOnly!=true && this.contacts[j].show==true)
				{
					nextCandidateToLoad=this.contacts[j];
					break;
				}
			//  we must go backwards
			if(nextCandidateToLoad==null && previousActiveIndex!=null)
			{
				for(j=previousActiveIndex-1;j>=0;j--)
					if(this.contacts[j].headerOnly!=true && this.contacts[j].show==true)
					{
						nextCandidateToLoad=this.contacts[j];
						break;
					}
			}

			// make the contact active
			$('#ABList').find('.ablist_item').removeClass('ablist_item_selected');
			if(nextCandidateToLoad!=null)
				this.loadContactByUID(nextCandidateToLoad.uid);
			else
			{
				this.contactLoaded=null;
				$('#ABContact').html('');
			}
		}
	}

	this.removeContactGroup=function(inputUid, loadNext)
	{
		for(var i=this.contact_groups.length-1;i>=0;i--)
			if(this.contact_groups[i].uid==inputUid)
			{
				var uidRemoved=this.contact_groups[i].uid;
				var item=$('#ResourceList').find('[data-id^="'+jqueryEscapeSelector(this.contact_groups[i].uid)+'"]');

				// remove the item
				item.remove();
				this.contact_groups.splice(i,1);

				if(loadNext && this.contactGroupLoaded!=null && this.contactGroupLoaded.uid==inputUid)
				{
					this.contactGroupLoaded=null;

					// set the whole collection as active
					var tmp=uidRemoved.match(RegExp('(^.*/)'),'');
					globalResourceList.loadAddressbookByUID(tmp[1]);
				}
				break;
			}
	}

	this.getABCategories=function()
	{
		var categoriesArr=[];

		for(var category in this.contact_categories)
			categoriesArr.push(category);

		return categoriesArr.sort(
			function(x,y){
				var a = x.toLowerCase();
				var b = y.toLowerCase();
				if (a > b)
					return 1;
				if (a < b)
					return -1;
				return 0;
		});
	}

	this.getABCompanies=function()
	{
		var companiesArr=[];

		for(var company in this.contact_companies)
			companiesArr.push(company);

		return companiesArr.sort(
			function(x,y){
				var a = x.toLowerCase();
				var b = y.toLowerCase();
				if (a > b)
					return 1;
				if (a < b)
					return -1;
				return 0;
		});
	}

	this.getABCompanyDepartments=function(inputCompany)
	{
		if(this.contact_companies[inputCompany]!=undefined)
			return this.contact_companies[inputCompany].departments;
		else
			return [];
	}

	// Contact list is not sorted, instead "insert sort" is performed
	this.insertContact=function(inputContact, forceReload)
	{
		// Apple "group" vCards
		if(this.isContactGroup(inputContact.vcard))
			return this.insertContactGroup(inputContact);

		if((inputContact.sortkey=this.getSortKey(inputContact.vcard,globalCollectionSort))===false || (inputContact.displayvalue=this.getSortKey(inputContact.vcard,globalCollectionDisplay))===false)
			return false;	//invalid vcard

		// CATEGORIES suggestion
		var categoriesArr=(inputContact.categories=='' ? [] : vcardSplitValue(inputContact.categories,','));
		var allCategoriesArr=this.getABCategories();

		// The search funcionality uses this ASCII value (you can add additional data here)
		// ORG attribute
		var tmp=inputContact.vcard;
		var orgArr=[];
		var depArr=[];
		while((vcard_element=tmp.match(vCard.pre['contentline_ORG']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			tmp_in=vcardUnescapeValue(vcardSplitValue(parsed[4],';')[0]);
			if(tmp_in!='')
				orgArr[orgArr.length]=tmp_in;
			tmp_in=vcardUnescapeValue(vcardSplitValue(parsed[4],';')[1]);
			if(tmp_in!='')
				depArr[depArr.length]=tmp_in;

			// remove the processed parameter
			tmp=tmp.replace(vcard_element[0],'\r\n');
		}
		var allOrgArr=this.getABCompanies();

		// EMAIL attribute
		var emailArr=[];
		while((vcard_element=tmp.match(vCard.pre['contentline_EMAIL']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			emailArr[emailArr.length]=parsed[4];

			// remove the processed parameter
			tmp=tmp.replace(vcard_element[0],'\r\n');
		}

		// Search data (displayvalue+categories+orgs+emails)
		inputContact.searchvalue=(inputContact.displayvalue+' '+categoriesArr.join(' ')+' '+orgArr.join(' ')+' '+emailArr.join(' ')).multiReplace(globalSearchTransformAlphabet);

		// CATEGORIES suggestion
		for(var i=0;i<allCategoriesArr.length;i++)	// if a contact is changed remove it from previous categories
			if(categoriesArr.indexOf(allCategoriesArr[i])==-1)
			{
				var index=this.contact_categories[allCategoriesArr[i]].indexOf(inputContact.uid);
				if(index!=-1)
				{
					this.contact_categories[allCategoriesArr[i]].splice(index,1);

					if(this.contact_categories[allCategoriesArr[i]].length==0)
						delete this.contact_categories[allCategoriesArr[i]];
				}
			}
		for(var i=0;i<categoriesArr.length;i++)	// add contact to it's categories
			this.contact_categories[categoriesArr[i]]=(this.contact_categories[categoriesArr[i]]==undefined ? [] : this.contact_categories[categoriesArr[i]]).concat(inputContact.uid).sort().unique();

		// ORG suggestion
		for(var i=0;i<allOrgArr.length;i++)	// if a contact is changed remove it from previous companies
			if(orgArr.indexOf(allOrgArr[i])==-1)
			{
				var index=this.contact_companies[allOrgArr[i]].uids.indexOf(inputContact.uid);
				if(index!=-1)
				{
					this.contact_companies[allOrgArr[i]].uids.splice(index,1);

					if(this.contact_companies[allOrgArr[i]].uids.length==0)
						delete this.contact_companies[allOrgArr[i]];
				}
			}
		for(var i=0;i<orgArr.length;i++)	// add contact to it's companies
			this.contact_companies[orgArr[i]]={uids: (this.contact_companies[orgArr[i]]==undefined ? [] : this.contact_companies[orgArr[i]].uids).concat(inputContact.uid).sort().unique(), departments: (this.contact_companies[orgArr[i]]==undefined ? [] : this.contact_companies[orgArr[i]].departments).concat(depArr).sort().unique()};

		// check for company contact
		inputContact.isCompany=false;
		var vcard_element=inputContact.vcard.match(vCard.pre['X-ABShowAs']);
		if(vcard_element!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			if(vcardUnescapeValue(parsed[4]).match(RegExp('^company$','i')))
				inputContact.isCompany=true;
		}

		makeActive=null;

		// do not insert entry with duplicate UID
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].uid==inputContact.uid)
			{
				if(this.contacts[i].displayvalue==inputContact.displayvalue && this.contacts[i].isCompany==inputContact.isCompany)
				{
					// we perform the normalization here, because we need to check whether the vCard is changed or not
					//  normalize the vCard when it's loaded first time
					if(inputContact.normalized==false)
					{
						inputContact.normalized=true;
						inputContact.vcard=normalizeVcard(additionalRFCFixes(inputContact.vcard));
					}
					this.contacts[i]=inputContact;

					// if the contact is loaded and the editor is in 'show' state, reload it
					if(this.contactLoaded!=null && this.contactLoaded.uid==inputContact.uid && this.contactLoaded.vcard!=inputContact.vcard &&  $('[id=vcard_editor]').attr('data-editor-state')=='show')
					{
						this.loadContactByUID(inputContact.uid);
						show_editor_message('in','message_success',localization[globalInterfaceLanguage].contactConcurrentChange,globalHideInfoMessageAfter);
						return 0;
					}
					else	// we are editing the contact or it is not active
						return -1;
				}
				else
				{
					if(this.contactLoaded.uid==inputContact.uid)
					{
						makeActive=inputContact.uid;
						// if the contact is selected, we are editing it and forceReload mode is not set
					 	if($('[id=vcard_editor]').attr('data-editor-state')=='edit' && forceReload!=true)
							return -2;
					}

					// the contact name is changed and must be moved to correct place (we first remove it and then reinsert)
					this.removeContact(inputContact.uid,false);
					break;
				}
			}

		var headerChar='';
		// key value for most common non-alphabet characters is defined as '#'
		if(inputContact.sortkey[0]!=undefined)
		{
			var unicodeValue=inputContact.sortkey.charCodeAt(0);
			if(unicodeValue<65 || (unicodeValue>90 && unicodeValue<97) || (unicodeValue>122 && unicodeValue<127))
				headerChar='#';
			else
				headerChar=inputContact.sortkey.charAt(0).toUpperCase();
		}
		else
			headerChar='#';
		
		// create the header
		var headerObject={headerOnly: true, displayvalue: headerChar};
		// find the index where to insert the new contact
		var insertIndex=this.contacts.length;
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].headerOnly==undefined && this.contacts[i].sortkey.customCompare(inputContact.sortkey,globalSortAlphabet,1,false)==1)
			{
				insertIndex=i;
				// if the object predecessor is header which is different from current header we must go upward
				if(i>0 && this.contacts[i-1].headerOnly==true && this.contacts[i-1].displayvalue!=headerObject.displayvalue)
					--insertIndex;
				break;
			}

		// check for header existence
		var headerMiss=1;
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].headerOnly==true && this.contacts[i].displayvalue==headerObject.displayvalue)
				{headerMiss=0; break;}

		// insert the header if not exists
		if(headerMiss)
			this.contacts.splice(insertIndex,0,headerObject);
		// insert the contact
		this.contacts.splice(insertIndex+headerMiss,0,inputContact);

		// insert header to interface if not exists
		if(headerMiss)
		{
			var newElement=$('#ABListTemplate').find('.ablist_header').clone().wrap('<div>');
			newElement=newElement.text(headerObject.displayvalue);
			newElement=newElement.parent().html();
			$('#ABList').children().eq(insertIndex).after(newElement);
		}
		// insert the contact to interface
		var newElement=$('#ABListTemplate').find('.ablist_item').clone().wrap('<div>');
		newElement.attr('onclick','if($(this).hasClass(\'ablist_item_selected\')) return false; else globalAddressbookList.loadContactByUID(this.getAttribute(\'data-id\'));');
		newElement.attr('data-id',inputContact.uid);

		newElement.find('.ablist_item_data').text(vcardUnescapeValue(inputContact.displayvalue));
		newElement.find('div[data-type="searchable_data"]').text(vcardUnescapeValue(inputContact.searchvalue));

		// set the company icon
		if(inputContact.isCompany==true)
			newElement.addClass('company');

		newElement=newElement.parent().html();
		$('#ABList').children().eq(insertIndex+headerMiss).after(newElement);

		// if the collection is not read-only the element is draggable
		if(globalResourceList.getCollectionPrivByUID(inputContact.uid.replace(RegExp('[^/]*$'),''))==false)
			$('#ABList').children().eq(insertIndex+headerMiss+1).draggable({
				delay: 250,
				revert: 'invalid',
				scroll: false,
				opacity: 0.8,
				stack: '#System',
				containment: '#System',
				appendTo: 'body',
				helper: function(){
					$('#ResourceList').find('.resource').droppable( 'option', 'accept', false);
					$('#ResourceList').find('.group').droppable( 'option', 'accept', false);

					$('#ResourceList').find('.resource[data-id!='+jqueryEscapeSelector($(this).attr('data-id').replace(RegExp('[^/]+$'),''))+']').droppable( 'option', 'accept', '.ablist_item');
					$('#ResourceList').find('.group[data-id^='+jqueryEscapeSelector($(this).attr('data-id').replace(RegExp('[^/]+$'),''))+']').not('.resource_selected').droppable( 'option', 'accept', '.ablist_item');

					var tmp = $(this).clone();
					tmp.addClass('ablist_item_dragged');
					// we cannot use .css() here, because we need to add !important (problem with Gecko based browsers)
					var tmp_style='max-width: '+$(this).outerWidth()+'px;';
					if($(this).css('background-image')!='none')
						tmp_style+='background-image: url(' + OC.imagePath('carddavmate', 'company_s_w.svg') + ') !important;';
					tmp.attr('style', tmp_style);

					return tmp;
				}
			});

		// load the updated contact (because we first deleted it, we need to set it active)
		if(makeActive!=null)
		{
			// make the contact active
			$('#ABList').find('.ablist_item').removeClass('ablist_item_selected');
			$('#ABList').children().eq(insertIndex+headerMiss+1).addClass('ablist_item_selected');

			this.loadContactByUID(makeActive);
		}
	}

	this.removeCollectionContacts=function(inputUid)
	{
		for(var i=this.contacts.length-1;i>=0;i--)
			if(this.contacts[i].uid!=undefined && this.contacts[i].uid.replace(RegExp('[^/]+$',''),'')==inputUid)
				this.removeContact(this.contacts[i].uid,true);
	}

	this.removeContact=function(inputUid, loadNext)
	{
		// Apple "group" vCards
		for(var i=this.contact_groups.length-1;i>=0;i--)
			if(this.contact_groups[i].uid==inputUid)
				return this.removeContactGroup(inputUid, loadNext);

		for(var i=this.contacts.length-1;i>=0;i--)
			if(this.contacts[i].uid==inputUid)
			{
				// CATEGORIES suggestion
				var categoriesArr=vcardSplitValue(this.contacts[i].categories,',');
				for(var j=0;j<categoriesArr.length;j++)
					if(this.contact_categories[categoriesArr[j]]!=undefined)
					{
						var index=this.contact_categories[categoriesArr[j]].indexOf(this.contacts[i].uid);
						if(index!=-1)
						{
							this.contact_categories[categoriesArr[j]].splice(index,1);

							if(this.contact_categories[categoriesArr[j]].length==0)
								delete this.contact_categories[categoriesArr[j]];
						}
					}

				// ORG suggestion
				var tmp=this.contacts[i].vcard;
				var orgArr=[];
				while((vcard_element=tmp.match(vCard.pre['contentline_ORG']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
					orgArr[orgArr.length]=vcardUnescapeValue(vcardSplitValue(parsed[4],';')[0]);

					// remove the processed parameter
					tmp=tmp.replace(vcard_element[0],'\r\n');
				}
				for(var j=0;j<orgArr.length;j++)
					if(this.contact_companies[orgArr[j]].uids!=undefined)
					{
						var index=this.contact_companies[orgArr[j]].uids.indexOf(this.contacts[i].uid);
						if(index!=-1)
						{
							this.contact_companies[orgArr[j]].uids.splice(index,1);

							if(this.contact_companies[orgArr[j]].uids.length==0)
								delete this.contact_companies[orgArr[j]];
						}
					}

				var nextCandidateToLoad=null;
				var item=$('#ABList').find('[data-id^="'+jqueryEscapeSelector(this.contacts[i].uid)+'"]');

				// get the nearest candidate to load
				//  if we can go forward
				for(j=i+1;j<this.contacts.length;j++)
					if(this.contacts[j].headerOnly!=true && this.contacts[j].show==true)
					{
						nextCandidateToLoad=this.contacts[j];
						break;
					}
				//  we must go backwards
				if(nextCandidateToLoad==null)
				{
					for(j=i-1;j>=0;j--)
						if(this.contacts[j].headerOnly!=true && this.contacts[j].show==true)
						{
							nextCandidateToLoad=this.contacts[j];
							break;
						}
				}

				// remove the item
				item.remove();
				this.contacts.splice(i,1);

				// remove the header if there is no more contact
				var removeHeader=true;
				var prevHeader=null;
				// find the previous header index
				for(var j=i-1;j>=0;j--)
					if(this.contacts[j].headerOnly==true)
					{
						prevHeader=j;
						break;
					}

				// check for visible contact existence for the found header
				if((prevHeader+1)<this.contacts.length && this.contacts[prevHeader+1].headerOnly!=true)
					removeHeader=false;

				// remove the header
				if(removeHeader==true)
				{
					$('#ABList').children().eq(prevHeader+1).remove();
					this.contacts.splice(prevHeader,1);
				}

				// load next contact
				if(loadNext && this.contactLoaded!=null && this.contactLoaded.uid==inputUid)
				{
					if(nextCandidateToLoad!=null)
						this.loadContactByUID(nextCandidateToLoad.uid);
					else
					{
						this.contactLoaded=null;
						$('#ABContact').html('');
					}
				}
				break;
			}
	}

	this.checkAndTouchIfExists=function(inputUID,inputEtag,inputTimestamp)
	{
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].timestamp!=undefined && this.contacts[i].uid==inputUID)
			{
				this.contacts[i].timestamp=inputTimestamp;

				if(this.contacts[i].etag==inputEtag)
					return true;
				else
					return false;
			}
		return false;
	}

	this.removeOldContacts=function(inputUidBase, inputTimestamp)
	{
		for(var i=this.contacts.length-1;i>=0;i--)
			if(this.contacts[i]!=undefined /* because the header can be deleted with the contact */ && this.contacts[i].timestamp!=undefined && this.contacts[i].uid.indexOf(inputUidBase)==0 && this.contacts[i].timestamp<inputTimestamp)
				this.removeContact(this.contacts[i].uid, true);
	}

	this.loadContactByUID=function(inputUID)
	{
		editor_cleanup(false);		// Editor initialization

		// find the inputUID contact
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].uid==inputUID)
			{
				// normalize the vCard when it's loaded first time
				if(this.contacts[i].normalized==false)
				{
					this.contacts[i].normalized=true;
					this.contacts[i].vcard=normalizeVcard(additionalRFCFixes(this.contacts[i].vcard));
				}

				var is_readonly=globalResourceList.getCollectionPrivByUID(this.contacts[i].uid.replace(RegExp('[^/]*$'),''));
				var loadContact=this.contactLoaded=this.contacts[i];

				if(vcardToData(loadContact,is_readonly))
					$('#EditorBox').fadeTo(100,1);
				else
					show_editor_message('out','message_error',localization[globalInterfaceLanguage].contactRfcNotCompliant,globalHideInfoMessageAfter);

				// Make the selected contact active
				$('#ABList').find('.ablist_item').removeClass('ablist_item_selected');
				$('#ABList').find('[data-id='+jqueryEscapeSelector(this.contacts[i].uid)+']').addClass('ablist_item_selected');

				break;
			}
	}

	this.getContactByUID=function(inputUID)
	{
		// find the inputUID contact
		for(var i=0;i<this.contacts.length;i++)
			if(this.contacts[i].uid==inputUID)
				return this.contacts[i];

		return null;
	}
}
