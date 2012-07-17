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

function dataToVcard(accountUID, inputUID, inputFilterUID, inputEtag)
{
	var vCardText='';
	var groupCounter=0;

	// vCard BEGIN (required by RFC)
	if(vCard.tplM['begin']!=null && (process_elem=vCard.tplM['begin'][0])!=undefined)
		vCardText+=vCard.tplM['begin'][0];
	else
	{
		process_elem=vCard.tplC['begin'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
		vCardText+=process_elem;
	}

// VERSION (required by RFC)
	if(vCard.tplM['contentline_VERSION']!=null && (process_elem=vCard.tplM['contentline_VERSION'][0])!=undefined)
	{
		// replace the object and related objects' group names (+ append the related objects after the processed)
		parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
		if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
			process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
	}
	else
	{
		process_elem=vCard.tplC['contentline_VERSION'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
		process_elem=process_elem.replace('##:::##version##:::##','3.0');
	}
	vCardText+=process_elem;

// UID (required by RFC)
	if(vCard.tplM['contentline_UID']!=null && (process_elem=vCard.tplM['contentline_UID'][0])!=undefined)
	{
		// replace the object and related objects' group names (+ append the related objects after the processed)
		parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
		if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
			process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
	}
	else
	{
		process_elem=vCard.tplC['contentline_UID'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
		process_elem=process_elem.replace('##:::##params_wsc##:::##','');

		var newUID=globalAddressbookList.getNewUID();

		// it is VERY small probability, that for 2 newly created contacts the same UID is generated (but not impossible :( ...)
		process_elem=process_elem.replace('##:::##uid##:::##',newUID);
	}
	vCardText+=process_elem;

// N (required by RFC)
	if(vCard.tplM['contentline_N']!=null && (process_elem=vCard.tplM['contentline_N'][0])!=undefined)
	{
		// replace the object and related objects' group names (+ append the related objects after the processed)
		parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
		if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
			process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
	}
	else
	{
		process_elem=vCard.tplC['contentline_N'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
		process_elem=process_elem.replace('##:::##params_wsc##:::##','');
	}
	process_elem=process_elem.replace('##:::##family##:::##',vcardEscapeValue($('[id="vcard_editor"] [data-type="family"]').val()));
	process_elem=process_elem.replace('##:::##given##:::##',vcardEscapeValue($('[id="vcard_editor"] [data-type="given"]').val()));
	process_elem=process_elem.replace('##:::##middle##:::##',vcardEscapeValue($('[id="vcard_editor"] [data-type="middle"]').val()));
	process_elem=process_elem.replace('##:::##prefix##:::##',vcardEscapeValue($('[id="vcard_editor"] [data-type="prefix"]').val()));
	process_elem=process_elem.replace('##:::##suffix##:::##',vcardEscapeValue($('[id="vcard_editor"] [data-type="suffix"]').val()));
	vCardText+=process_elem;

// FN (extracted from newly created N [previous "process_elem"], required by RFC)
	// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
	parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
	// parsed_value = [0]->Family, [1]->Given, [2]->Middle, [3]->Prefix, [4]->Suffix
	parsed_value=vcardSplitValue(parsed[4],';');

	// if globalContactStoreFN is missing use globalCollectionDisplay
	if(globalContactStoreFN==undefined)
		globalContactStoreFN=globalCollectionDisplay;

	var tmp=globalContactStoreFN.replaceAll(' ','');
	tmp=tmp.replace(RegExp('surname|lastname|last|family','i'),'0');
	tmp=tmp.replace(RegExp('firstname|first|given','i'),'1');
	tmp=tmp.replace(RegExp('middlename|middle','i'),'2');
	tmp=tmp.replace(RegExp('prefix','i'),'3');
	tmp=tmp.replace(RegExp('suffix','i'),'4');
	tmp=tmp.split(',');

	var fn_value='';
	for(var i=0;i<tmp.length;i++)
	{
		if(parsed_value[tmp[i]]!=undefined)
			fn_value+=parsed_value[tmp[i]];
		if(fn_value!='' && tmp[i+1]!=undefined && parsed_value[tmp[i+1]]!=undefined && parsed_value[tmp[i+1]]!='')
			fn_value+=' ';
	}

	if(fn_value=='')	//empty FN -> we use the company name as FN
		fn_value=vcardEscapeValue($('[id="vcard_editor"] [data-type="org"]').val());

	if(vCard.tplM['contentline_FN']!=null && (process_elem=vCard.tplM['contentline_FN'][0])!=undefined)
	{
		// replace the object and related objects' group names (+ append the related objects after the processed)
		parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
		if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
			process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
	}
	else
	{
		process_elem=vCard.tplC['contentline_FN'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
		process_elem=process_elem.replace('##:::##params_wsc##:::##','');
	}
	process_elem=process_elem.replace('##:::##fn##:::##',fn_value);
	vCardText+=process_elem;

// CATEGORIES
	if((value=$('[id="vcard_editor"] [data-type="\\%categories"]').find('input[data-type="value"]').val())!='')
	{
		if(vCard.tplM['contentline_CATEGORIES']!=null && (process_elem=vCard.tplM['contentline_CATEGORIES'][0])!=undefined)
		{
			// replace the object and related objects' group names (+ append the related objects after the processed)
			parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
			if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
				process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
		}
		else
		{
			process_elem=vCard.tplC['contentline_CATEGORIES'];
			process_elem=process_elem.replace('##:::##group_wd##:::##','');
			process_elem=process_elem.replace('##:::##params_wsc##:::##','');
		}
		process_elem=process_elem.replace('##:::##value##:::##',value);	// we do not need to escape the value here!
		vCardText+=process_elem;
	}


// NOTE
	if((value=$('[id="vcard_editor"] [data-type="\\%note"]').find('textarea').val())!='')
	{
		if(vCard.tplM['contentline_NOTE']!=null && (process_elem=vCard.tplM['contentline_NOTE'][0])!=undefined)
		{
			// replace the object and related objects' group names (+ append the related objects after the processed)
			parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
			if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
				process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
		}
		else
		{
			process_elem=vCard.tplC['contentline_NOTE'];
			process_elem=process_elem.replace('##:::##group_wd##:::##','');
			process_elem=process_elem.replace('##:::##params_wsc##:::##','');
		}
		process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));
		vCardText+=process_elem;
	}

// REV
	if(vCard.tplM['contentline_REV']!=null && (process_elem=vCard.tplM['contentline_REV'][0])!=undefined)
	{
		// replace the object and related objects' group names (+ append the related objects after the processed)
		parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
		if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
			process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
	}
	else
	{
		process_elem=vCard.tplC['contentline_REV'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
	}
	process_elem=process_elem.replace('##:::##params_wsc##:::##','');
	var d = new Date();
	var utc=d.getUTCFullYear()+(d.getUTCMonth()+1<10 ? '0':'')+(d.getUTCMonth()+1)+(d.getUTCDate()<10 ? '0':'')+d.getUTCDate()+'T'+(d.getUTCHours()<10 ? '0':'')+d.getUTCHours()+(d.getUTCMinutes()<10 ? '0':'')+d.getUTCMinutes()+(d.getUTCSeconds()<10 ? '0':'')+d.getUTCSeconds()+'Z';
	process_elem=process_elem.replace('##:::##value##:::##',utc);
	vCardText+=process_elem;

// NICKNAME
	if((value=$('[id="vcard_editor"] [data-type="nickname"]').val())!='')
	{
		if(vCard.tplM['contentline_NICKNAME']!=null && (process_elem=vCard.tplM['contentline_NICKNAME'][0])!=undefined)
		{
			// replace the object and related objects' group names (+ append the related objects after the processed)
			parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
			if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
				process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
		}
		else
		{
			process_elem=vCard.tplC['contentline_NICKNAME'];
			process_elem=process_elem.replace('##:::##group_wd##:::##','');
			process_elem=process_elem.replace('##:::##params_wsc##:::##','');
		}
		process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));
		vCardText+=process_elem;
	}

// BDAY
	if((value=$('[id="vcard_editor"] [data-type="date_bday"]').val())!='')
	{
		var valid=true;
		try {var date=$.datepicker.parseDate(globalDatepickerFormat, value)}
		catch (e) {valid=false}

		if(valid==true)
		{
			if(vCard.tplM['contentline_BDAY']!=null && (process_elem=vCard.tplM['contentline_BDAY'][0])!=undefined)
			{
				// replace the object and related objects' group names (+ append the related objects after the processed)
				parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
				if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
			}
			else
			{
				process_elem=vCard.tplC['contentline_BDAY'];
				process_elem=process_elem.replace('##:::##group_wd##:::##','');
				process_elem=process_elem.replace('##:::##params_wsc##:::##',';VALUE=date');
			}

			process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue($.datepicker.formatDate('yy-mm-dd', date)));
			vCardText+=process_elem;
		}
	}

// X-ANNIVERSARY
	if((value=$('[id="vcard_editor"] [data-type="date_anniversary"]').val())!='')
	{
		var valid=true;
		try {var date=$.datepicker.parseDate(globalDatepickerFormat, value)}
		catch (e) {valid=false}

		if(valid==true)
		{
			if(vCard.tplM['contentline_X-ANNIVERSARY']!=null && (process_elem=vCard.tplM['contentline_X-ANNIVERSARY'][0])!=undefined)
			{
				// replace the object and related objects' group names (+ append the related objects after the processed)
				parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
				if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
			}
			else
			{
				process_elem=vCard.tplC['contentline_X-ANNIVERSARY'];
				process_elem=process_elem.replace('##:::##group_wd##:::##','');
				process_elem=process_elem.replace('##:::##params_wsc##:::##',';VALUE=date');
			}
			var date_value=vcardEscapeValue($.datepicker.formatDate('yy-mm-dd', date));

			if(globalCompatibility.anniversaryOutputFormat.indexOf('other')!=-1)
			{
				// X-ANNIVERSARY
				process_elem=process_elem.replace('##:::##value##:::##',date_value);
				vCardText+=process_elem;
			}
			if(globalCompatibility.anniversaryOutputFormat.indexOf('apple')!=-1)
			{
				// Apple specific X-ABDATE
				process_elem2='item'+(groupCounter)+'.X-ABDATE;TYPE=pref:'+date_value+'\r\n'+'item'+(groupCounter)+'.X-ABLabel:_$!<Anniversary>!$_\r\n';
				groupCounter++;
				vCardText+=process_elem2;
			}
		}
	}

// TITLE
	if((value=$('[id="vcard_editor"] [data-type="title"]').val())!='')
	{
		if(vCard.tplM['contentline_TITLE']!=null && (process_elem=vCard.tplM['contentline_TITLE'][0])!=undefined)
		{
			// replace the object and related objects' group names (+ append the related objects after the processed)
			parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
			if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
				process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
		}
		else
		{
			process_elem=vCard.tplC['contentline_TITLE'];
			process_elem=process_elem.replace('##:::##group_wd##:::##','');
			process_elem=process_elem.replace('##:::##params_wsc##:::##','');
		}
		process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));
		vCardText+=process_elem;
	}

// ORG
	value=$('[id="vcard_editor"] [data-type="org"]').val();
	value2=$('[id="vcard_editor"] [data-type="department"]').val();
	if(value!='' || value2!='')
	{
		if(vCard.tplM['contentline_ORG']!=null && (process_elem=vCard.tplM['contentline_ORG'][0])!=undefined)
		{
			// replace the object and related objects' group names (+ append the related objects after the processed)
			parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
			if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
				process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
		}
		else
		{
			process_elem=vCard.tplC['contentline_ORG'];
			process_elem=process_elem.replace('##:::##group_wd##:::##','');
			process_elem=process_elem.replace('##:::##params_wsc##:::##','');
			process_elem=process_elem.replace('##:::##units_wsc##:::##','');
		}
		process_elem=process_elem.replace('##:::##org##:::##',vcardEscapeValue(value)+(value2!=undefined && value2!='' ? ';'+vcardEscapeValue(value2) : ''));
		vCardText+=process_elem;
	}

// X-ABShowAs
	if($('[data-type="isorg"]').prop('checked'))
	{
		if(vCard.tplM['contentline_X-ABShowAs']!=null && (process_elem=vCard.tplM['contentline_X-ABShowAs'][0])!=undefined)
		{
			// replace the object and related objects' group names (+ append the related objects after the processed)
			parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
			if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
				process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+(groupCounter++)+'.').substring(2);
		}
		else
		{
			process_elem=vCard.tplC['contentline_X-ABShowAs'];
			process_elem=process_elem.replace('##:::##group_wd##:::##','');
			process_elem=process_elem.replace('##:::##params_wsc##:::##','');
			process_elem=process_elem.replace('##:::##value##:::##','COMPANY');
		}
		vCardText+=process_elem;
	}

// ADR
	$('[id="vcard_editor"] [data-type="\\%address"]').each(
		function (index,element)
		{
			// if data is present for the selected country's address fields
			var found=0;
			$(element).find('[data-addr-field]').each(
				function(index,element)
				{
					if($(element).attr('data-addr-field')!='' && $(element).attr('data-addr-field')!='country' && $(element).val()!='')
					{
						found=1;
						return false;
					}
				}
			);
			if(found)
			{
				var incGroupCounter=false;
				if(vCard.tplM['contentline_ADR']!=null && (process_elem=vCard.tplM['contentline_ADR'][$(element).attr('data-id')])!=undefined)
				{
					// replace the object and related objects' group names (+ append the related objects after the processed)
					parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
					if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					{
						process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+groupCounter+'.').substring(2);
						incGroupCounter=true;
					}
				}
				else
				{
					process_elem=vCard.tplC['contentline_ADR'];
					process_elem=process_elem.replace('##:::##group_wd##:::##','');
				}

				tmp_type=$(element).find('[data-type="address_type"] option').filter(':selected').attr('data-type');

				params_wsc='';
				tmp_normal_types=tmp_type.replace(RegExp('/[^/]+/','g'),'_').replaceAll('__','_').replace(RegExp('^_|_$','g'),'');
				if(tmp_normal_types!='')
					params_wsc=';TYPE='+vcardEscapeValue(tmp_normal_types).toUpperCase().replace(RegExp('_','g'),';TYPE=');

				process_elem=process_elem.replace('##:::##params_wsc##:::##',params_wsc);
				process_elem=process_elem.replace('##:::##pobox##:::##',vcardEscapeValue($(element).find('[data-addr-field="pobox"]').val()));
				process_elem=process_elem.replace('##:::##extaddr##:::##',vcardEscapeValue($(element).find('[data-addr-field="extaddr"]').val()));
				process_elem=process_elem.replace('##:::##street##:::##',vcardEscapeValue($(element).find('[data-addr-field="street"]').val()));
				process_elem=process_elem.replace('##:::##locality##:::##',vcardEscapeValue($(element).find('[data-addr-field="locality"]').val()));
				process_elem=process_elem.replace('##:::##region##:::##',vcardEscapeValue($(element).find('[data-addr-field="region"]').val()));
				process_elem=process_elem.replace('##:::##code##:::##',vcardEscapeValue($(element).find('[data-addr-field="code"]').val()));
				process_elem=process_elem.replace('##:::##country##:::##',vcardEscapeValue($(element).find('[data-type="%country"] option').filter(':selected').attr('data-full-name')));

				my_related='X-ABADR:'+vcardEscapeValue($(element).find('[data-type="%country"] option').filter(':selected').attr('data-type'))+'\r\n';
				parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
				if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
					process_elem+=parsed[1]+my_related;
				else
					process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				incGroupCounter=true;	// we always increate the group number, because the X-ABADR is always stored

				my_related='';
				tmp_related_type=tmp_type.match(RegExp('/([^/]+)/'));	// only one element of related (X-ABLabel) is supported

				if(tmp_related_type!=null && tmp_related_type[1]!='')
					my_related='X-ABLabel:'+vcardEscapeValue((dataTypes['address_type_store_as'][tmp_related_type[1]]!=undefined ? dataTypes['address_type_store_as'][tmp_related_type[1]] : tmp_related_type[1]))+'\r\n';

				if(my_related!='')
				{
					incGroupCounter=true;
					parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
					if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
						process_elem+=parsed[1]+my_related;
					else
						process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				}

				if(incGroupCounter) groupCounter++;
				vCardText+=process_elem;
			}
		}
	);

// TEL
	$('[id="vcard_editor"] [data-type="\\%phone"]').each(
		function (index,element)
		{
			if((value=$(element).find('[data-type="value"]').val())!='')
			{
				var incGroupCounter=false;
				if(vCard.tplM['contentline_TEL']!=null && (process_elem=vCard.tplM['contentline_TEL'][$(element).attr('data-id')])!=undefined)
				{
					// replace the object and related objects' group names (+ append the related objects after the processed)
					parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
					if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					{
						process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+groupCounter+'.').substring(2);
						incGroupCounter=true;
					}
				}
				else
				{
					process_elem=vCard.tplC['contentline_TEL'];
					process_elem=process_elem.replace('##:::##group_wd##:::##','');
				}

				tmp_type=$(element).find('[data-type="phone_type"] option').filter(':selected').attr('data-type');

				params_wsc='';
				tmp_normal_types=tmp_type.replace(RegExp('/[^/]+/','g'),'_').replaceAll('__','_').replace(RegExp('^_|_$','g'),'');
				if(tmp_normal_types!='')
					params_wsc=';TYPE='+vcardEscapeValue(tmp_normal_types).toUpperCase().replace(RegExp('_','g'),';TYPE=');

				process_elem=process_elem.replace('##:::##params_wsc##:::##',params_wsc);
				process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));

				my_related='';
				tmp_related_type=tmp_type.match(RegExp('/([^/]+)/'));	// only one element of related (X-ABLabel) is supported

				if(tmp_related_type!=null && tmp_related_type[1]!='')
					my_related='X-ABLabel:'+vcardEscapeValue((dataTypes['phone_type_store_as'][tmp_related_type[1]]!=undefined ? dataTypes['phone_type_store_as'][tmp_related_type[1]] : tmp_related_type[1]))+'\r\n';

				if(my_related!='')
				{
					incGroupCounter=true;
					parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
					if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
						process_elem+=parsed[1]+my_related;
					else
						process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				}

				if(incGroupCounter) groupCounter++;
				vCardText+=process_elem;
			}
		}
	);

// EMAIL
	$('[id="vcard_editor"] [data-type="\\%email"]').each(
		function (index,element)
		{
			if((value=$(element).find('[data-type="value"]').val())!='')
			{
				incGroupCounter=false;
				if(vCard.tplM['contentline_EMAIL']!=null && (process_elem=vCard.tplM['contentline_EMAIL'][$(element).attr('data-id')])!=undefined)
				{
					// replace the object and related objects' group names (+ append the related objects after the processed)
					parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
					if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					{
						process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+groupCounter+'.').substring(2);
						incGroupCounter=true;
					}
				}
				else
				{
					process_elem=vCard.tplC['contentline_EMAIL'];
					process_elem=process_elem.replace('##:::##group_wd##:::##','');
				}

				tmp_type=$(element).find('[data-type="email_type"] option').filter(':selected').attr('data-type');

				params_wsc='';
				tmp_normal_types=tmp_type.replace(RegExp('/[^/]+/','g'),'_').replaceAll('__','_').replace(RegExp('^_|_$','g'),'');
				if(tmp_normal_types!='')
					params_wsc=';TYPE='+vcardEscapeValue(tmp_normal_types).toUpperCase().replace(RegExp('_','g'),';TYPE=');

				process_elem=process_elem.replace('##:::##params_wsc##:::##',params_wsc);
				process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));

				my_related='';
				tmp_related_type=tmp_type.match(RegExp('/([^/]+)/'));	// only one element of related (X-ABLabel) is supported

				if(tmp_related_type!=null && tmp_related_type[1]!='')
					my_related='X-ABLabel:'+vcardEscapeValue((dataTypes['email_type_store_as'][tmp_related_type[1]]!=undefined ? dataTypes['email_type_store_as'][tmp_related_type[1]] : tmp_related_type[1]))+'\r\n';

				if(my_related!='')
				{
					incGroupCounter=true;
					parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
					if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
						process_elem+=parsed[1]+my_related;
					else
						process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				}

				if(incGroupCounter) groupCounter++;
				vCardText+=process_elem;
			}
		}
	);

// URL
	$('[id="vcard_editor"] [data-type="\\%url"]').each(
		function (index,element)
		{
			if((value=$(element).find('[data-type="value"]').val())!='')
			{
				incGroupCounter=false;
				if(vCard.tplM['contentline_URL']!=null && (process_elem=vCard.tplM['contentline_URL'][$(element).attr('data-id')])!=undefined)
				{
					// replace the object and related objects' group names (+ append the related objects after the processed)
					parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
					if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					{
						process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+groupCounter+'.').substring(2);
						incGroupCounter=true;
					}
				}
				else
				{
					process_elem=vCard.tplC['contentline_URL'];
					process_elem=process_elem.replace('##:::##group_wd##:::##','');
				}

				tmp_type=$(element).find('[data-type="url_type"] option').filter(':selected').attr('data-type');

				params_wsc='';
				tmp_normal_types=tmp_type.replace(RegExp('/[^/]+/','g'),'_').replaceAll('__','_').replace(RegExp('^_|_$','g'),'');
				if(tmp_normal_types!='')
					params_wsc=';TYPE='+vcardEscapeValue(tmp_normal_types).toUpperCase().replace(RegExp('_','g'),';TYPE=');

				process_elem=process_elem.replace('##:::##params_wsc##:::##',params_wsc);
				process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));

				my_related='';
				tmp_related_type=tmp_type.match(RegExp('/([^/]+)/'));	// only one element of related (X-ABLabel) is supported

				if(tmp_related_type!=null && tmp_related_type[1]!='')
					my_related='X-ABLabel:'+vcardEscapeValue((dataTypes['url_type_store_as'][tmp_related_type[1]]!=undefined ? dataTypes['url_type_store_as'][tmp_related_type[1]] : tmp_related_type[1]))+'\r\n';

				if(my_related!='')
				{
					incGroupCounter=true;
					parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
					if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
						process_elem+=parsed[1]+my_related;
					else
						process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				}

				if(incGroupCounter) groupCounter++;
				vCardText+=process_elem;
			}
		}
	);

// X-ABRELATEDNAMES
	$('[id="vcard_editor"] [data-type="\\%person"]').each(
		function (index,element)
		{
			if((value=$(element).find('[data-type="value"]').val())!='')
			{
				incGroupCounter=false;
				if(vCard.tplM['contentline_X-ABRELATEDNAMES']!=null && (process_elem=vCard.tplM['contentline_X-ABRELATEDNAMES'][$(element).attr('data-id')])!=undefined)
				{
					// replace the object and related objects' group names (+ append the related objects after the processed)
					parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
					if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					{
						process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+groupCounter+'.').substring(2);
						incGroupCounter=true;
					}
				}
				else
				{
					process_elem=vCard.tplC['contentline_X-ABRELATEDNAMES'];
					process_elem=process_elem.replace('##:::##group_wd##:::##','');
				}

				tmp_type=$(element).find('[data-type="person_type"] option').filter(':selected').attr('data-type');

				params_wsc='';
				tmp_normal_types=tmp_type.replace(RegExp('/[^/]+/','g'),'_').replaceAll('__','_').replace(RegExp('^_|_$','g'),'');
				if(tmp_normal_types!='')
					params_wsc=';TYPE='+vcardEscapeValue(tmp_normal_types).toUpperCase().replace(RegExp('_','g'),';TYPE=');

				process_elem=process_elem.replace('##:::##params_wsc##:::##',params_wsc);
				process_elem=process_elem.replace('##:::##value##:::##',vcardEscapeValue(value));

				my_related='';
				tmp_related_type=tmp_type.match(RegExp('/([^/]+)/'));	// only one element of related (X-ABLabel) is supported

				if(tmp_related_type!=null && tmp_related_type[1]!='')
					my_related='X-ABLabel:'+vcardEscapeValue((dataTypes['person_type_store_as'][tmp_related_type[1]]!=undefined ? dataTypes['person_type_store_as'][tmp_related_type[1]] : tmp_related_type[1]))+'\r\n';

				if(my_related!='')
				{
					incGroupCounter=true;
					parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
					if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
						process_elem+=parsed[1]+my_related;
					else
						process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				}

				if(incGroupCounter) groupCounter++;

				if(tmp_related_type!=null && tmp_related_type[1]!='')
				{
					// In addition of the X-ABRELATEDNAMES attributes add also the old style X-* attributes
					switch(tmp_related_type[1])
					{
						case '_$!<assistant>!$_':
							process_elem+='X-ASSISTANT:'+vcardEscapeValue(value)+'\r\n';
							// process_elem+='X-EVOLUTION-ASSISTANT:'+vcardEscapeValue(value)+'\r\n';
							break;
						case '_$!<manager>!$_':
							process_elem+='X-MANAGER:'+vcardEscapeValue(value)+'\r\n';
							// process_elem+='X-EVOLUTION-MANAGER:'+vcardEscapeValue(value)+'\r\n';
							break;
						case '_$!<spouse>!$_':
							process_elem+='X-SPOUSE:'+vcardEscapeValue(value)+'\r\n';
							// process_elem+='X-EVOLUTION-SPOUSE:'+vcardEscapeValue(value)+'\r\n';
							break;
					}
				}

				vCardText+=process_elem;
			}
		}
	);

// IMPP
	$('[id="vcard_editor"] [data-type="\\%im"]').each(
		function (index,element)
		{
			if((value=$(element).find('[data-type="value"]').val())!='')
			{
				incGroupCounter=false;
				if(vCard.tplM['contentline_IMPP']!=null && (process_elem=vCard.tplM['contentline_IMPP'][$(element).attr('data-id')])!=undefined)
				{
					// replace the object and related objects' group names (+ append the related objects after the processed)
					parsed=('\r\n'+process_elem).match(RegExp('\r\n((?:'+vCard.re['group']+'\\.)?)','m'));
					if(parsed[1]!='')	// if group is present, replace the object and related objects' group names
					{
						process_elem=('\r\n'+process_elem).replace(RegExp('\r\n'+parsed[1].replace('.','\\.'),'mg'),'\r\nitem'+groupCounter+'.').substring(2);
						incGroupCounter=true;
					}
				}
				else
				{
					process_elem=vCard.tplC['contentline_IMPP'];
					process_elem=process_elem.replace('##:::##group_wd##:::##','');
				}

				tmp_type=$(element).find('[data-type="im_type"] option').filter(':selected').attr('data-type');

				params_wsc=params_wsc_old_repr='';
				tmp_normal_types=tmp_type.replace(RegExp('/[^/]+/','g'),'_').replaceAll('__','_').replace(RegExp('^_|_$','g'),'');
				if(tmp_normal_types!='')
					params_wsc=params_wsc_old_repr=';TYPE='+vcardEscapeValue(tmp_normal_types).toUpperCase().replace(RegExp('_','g'),';TYPE=');

				tmp_service_type=$(element).find('[data-type="im_service_type"] option').filter(':selected').attr('data-type');
				if(dataTypes['im_service_type_store_as'][tmp_service_type]!=undefined)
					tmp_service_type=dataTypes['im_service_type_store_as'][tmp_service_type];
				params_wsc=';X-SERVICE-TYPE='+vcardEscapeValue(tmp_service_type)+params_wsc;

				process_elem=process_elem.replace('##:::##params_wsc##:::##',params_wsc);
				switch(tmp_service_type.toLowerCase())	// RFC4770
				{
					case 'aim':
						im_value='aim:'+vcardEscapeValue(value);
						break;
					case 'facebook':
						im_value='xmpp:'+vcardEscapeValue(value);
						break;
					case 'googletalk':
						im_value='xmpp:'+vcardEscapeValue(value);
						break;
					case 'icq':
						im_value='aim:'+vcardEscapeValue(value);
						break;
					case 'irc':
						im_value='irc:'+vcardEscapeValue(value);
						break;
					case 'jabber':
						im_value='xmpp:'+vcardEscapeValue(value);
						break;
					case 'msn':
						im_value='msnim:'+vcardEscapeValue(value);
						break;
					case 'skype':
						im_value='skype:'+vcardEscapeValue(value);
						break;
					case 'yahoo':
						im_value='ymsgr:'+vcardEscapeValue(value);
						break;
					default:	// 'gadugadu', 'qq', ...
						im_value='x-apple:'+vcardEscapeValue(value);
						break;
				}
				process_elem=process_elem.replace('##:::##value##:::##',im_value);

				my_related='';
				tmp_related_type=tmp_type.match(RegExp('/([^/]+)/'));	// only one element of related (X-ABLabel) is supported

				if(tmp_related_type!=null && tmp_related_type[1]!='')
					my_related='X-ABLabel:'+vcardEscapeValue((dataTypes['im_type_store_as'][tmp_related_type[1]]!=undefined ? dataTypes['im_type_store_as'][tmp_related_type[1]] : tmp_related_type[1]))+'\r\n';

				if(my_related!='')
				{
					incGroupCounter=true;
					parsed=('\r\n'+process_elem).match(vCard.pre['contentline_parse']);
					if(parsed[1]!='')	// if group is present, we use it, otherwise we create a new group
						process_elem+=parsed[1]+my_related;
					else
						process_elem='item'+groupCounter+'.'+process_elem+'item'+groupCounter+'.'+my_related;
				}
				if(incGroupCounter) groupCounter++;

				// In addition of the IMPP attributes add also the old style X-* attributes
				process_elem_old_repr='';
				switch(tmp_service_type.toLowerCase())
				{
					case 'aim':
						new_group_wd='';
						if(incGroupCounter)
						{
							new_group_wd='item'+groupCounter+'.';
							process_elem_old_repr=('\r\n'+process_elem).replace(RegExp('\r\nitem'+(groupCounter-1)+'\\.','mg'),'\r\n'+new_group_wd);
							groupCounter++;
						}
						else
							process_elem_old_repr='\r\n'+process_elem;
						process_elem+=process_elem_old_repr.replace('\r\n'+new_group_wd+'IMPP;X-SERVICE-TYPE='+ vcardEscapeValue(tmp_service_type),new_group_wd+'X-AIM').replace(im_value+'\r\n',vcardEscapeValue(value)+'\r\n');
						break;
					case 'jabber':
						new_group_wd='';
						if(incGroupCounter)
						{
							new_group_wd='item'+groupCounter+'.';
							process_elem_old_repr=('\r\n'+process_elem).replace(RegExp('\r\nitem'+(groupCounter-1)+'\\.','mg'),'\r\n'+new_group_wd);
							groupCounter++;
						}
						else
							process_elem_old_repr='\r\n'+process_elem;
						process_elem+=process_elem_old_repr.replace('\r\n'+new_group_wd+'IMPP;X-SERVICE-TYPE='+ vcardEscapeValue(tmp_service_type),new_group_wd+'X-JABBER').replace(im_value+'\r\n',vcardEscapeValue(value)+'\r\n');
						break;
					case 'msn':
						new_group_wd='';
						if(incGroupCounter)
						{
							new_group_wd='item'+groupCounter+'.';
							process_elem_old_repr=('\r\n'+process_elem).replace(RegExp('\r\nitem'+(groupCounter-1)+'\\.','mg'),'\r\n'+new_group_wd);
							groupCounter++;
						}
						else
							process_elem_old_repr='\r\n'+process_elem;
						process_elem+=process_elem_old_repr.replace('\r\n'+new_group_wd+'IMPP;X-SERVICE-TYPE='+ vcardEscapeValue(tmp_service_type),new_group_wd+'X-MSN').replace(im_value+'\r\n',vcardEscapeValue(value)+'\r\n');
						break;
					case 'yahoo':
						new_group_wd='';
						process_elem_tmp=process_elem;
						if(incGroupCounter)
						{
							new_group_wd='item'+groupCounter+'.';
							process_elem_old_repr=('\r\n'+process_elem_tmp).replace(RegExp('\r\nitem'+(groupCounter-1)+'\\.','mg'),'\r\n'+new_group_wd);
							groupCounter++;
						}
						else
							process_elem_old_repr='\r\n'+process_elem;
						process_elem+=process_elem_old_repr.replace('\r\n'+new_group_wd+'IMPP;X-SERVICE-TYPE='+ vcardEscapeValue(tmp_service_type),new_group_wd+'X-YAHOO').replace(im_value+'\r\n',vcardEscapeValue(value)+'\r\n');

						new_group_wd='';
						if(incGroupCounter)
						{
							new_group_wd='item'+groupCounter+'.';
							process_elem_old_repr=('\r\n'+process_elem_tmp).replace(RegExp('\r\nitem'+(groupCounter-2)+'\\.','mg'),'\r\n'+new_group_wd);
							groupCounter++;
						}
						else
							process_elem_old_repr='\r\n'+process_elem;
						process_elem+=process_elem_old_repr.replace('\r\n'+new_group_wd+'IMPP;X-SERVICE-TYPE='+ vcardEscapeValue(tmp_service_type),new_group_wd+'X-YAHOO-ID').replace(im_value+'\r\n',vcardEscapeValue(value)+'\r\n');
						break;
					case 'icq':
						new_group_wd='';
						if(incGroupCounter)
						{
							new_group_wd='item'+groupCounter+'.';
							process_elem_old_repr=('\r\n'+process_elem).replace(RegExp('\r\nitem'+(groupCounter-1)+'\\.','mg'),'\r\n'+new_group_wd);
							groupCounter++;
						}
						else
							process_elem_old_repr='\r\n'+process_elem;
						process_elem+=process_elem_old_repr.replace('\r\n'+new_group_wd+'IMPP;X-SERVICE-TYPE='+ vcardEscapeValue(tmp_service_type),new_group_wd+'X-ICQ').replace(im_value+'\r\n',vcardEscapeValue(value)+'\r\n');
						break;
				}
				vCardText+=process_elem;
			}
		}
	);

	// PRODID
	vCardText+='PRODID:-//Inf-IT//CardDavMATE '+globalCardDavMATEVersion+'//EN\r\n';

	if(typeof vCard.tplM['unprocessed_unrelated']!='undefined')
		vCardText+=vCard.tplM['unprocessed_unrelated'].replace(RegExp('^\r\n'),'');
	
	// vCard END (required by RFC)
	if(vCard.tplM['end']!=null && (process_elem=vCard.tplM['end'][0])!=undefined)
		vCardText+=vCard.tplM['end'][0];
	else
	{
		process_elem=vCard.tplC['end'];
		process_elem=process_elem.replace('##:::##group_wd##:::##','');
		vCardText+=process_elem;
	}

	putVcardToCollectionMain({accountUID: accountUID, uid: inputUID, etag: inputEtag, vcard: vCardText}, inputFilterUID);
}

function vcardToData(inputContact, inputIsReadonly)
{
	if(inputContact.vcard==undefined)
		return false;

//	alert(inputContact.vcard);

	if(inputContact.vcard.match(vCard.pre['vcard']))
	{
		// ------------------------------------------------------------------------------------- //
		// BEGIN and END
		vcard_full=inputContact.vcard.split('\r\n');		// vCard data to array

		// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
		if((parsed=('\r\n'+vcard_full[0]+'\r\n').match(vCard.pre['contentline_parse']))==null)
			return false;
		// values not directly supported by the editor (old values are kept intact)
		vCard.tplM['begin'][0]=vCard.tplC['begin'].replace(/##:::##group_wd##:::##/g,vcard_begin_group=parsed[1]);
		// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
		if((parsed=('\r\n'+vcard_full[vcard_full.length-2]+'\r\n').match(vCard.pre['contentline_parse']))==null)
			return false;
		// values not directly supported by the editor (old values are kept intact)
		vCard.tplM['end'][0]=vCard.tplC['end'].replace(/##:::##group_wd##:::##/g,vcard_end_group=parsed[1]);

		if(vcard_begin_group!=vcard_end_group)
			return false;	// the vCard BEGIN and END "group" are different

		// remove the vCard BEGIN and END
		vcard='\r\n'+vcard_full.slice(1,vcard_full.length-2).join('\r\n')+'\r\n';

		// ------------------------------------------------------------------------------------- //
		// VERSION -> what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_VERSION']);
		if(vcard_element!=null && vcard_element.length==1)	// if the VERSION attribute is not present exactly once, vCard is considered invalid
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			if(parsed[3]=='' && parsed[4]=='3.0')	// RFC requirement
			{
				// values not directly supported by the editor (old values are kept intact)
				vCard.tplM['contentline_VERSION'][0]=vCard.tplC['contentline_VERSION'];
				vCard.tplM['contentline_VERSION'][0]=vCard.tplM['contentline_VERSION'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
				vCard.tplM['contentline_VERSION'][0]=vCard.tplM['contentline_VERSION'][0].replace(/##:::##version##:::##/g,parsed[4]);

				// remove the processed parameter
				vcard=vcard.replace(vcard_element[0],'\r\n');

				// find the corresponding group data (if exists)
				if(parsed[1]!='')
				{
					re=parsed[1].replace('.','\\..*')+'\r\n';
					while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
					{
						// append the parameter to its parent
						vCard.tplM['contentline_VERSION'][0]+=vcard_element_related[0].substr(2);
						// remove the processed parameter
						vcard=vcard.replace(vcard_element_related[0],'\r\n');
					}
				}
			}
			else
				return false;	// invalid input for "VERSION" (we support only vCard 3.0)
		}
		else
			return false;	// vcard "VERSION" not present or present more than once

		// ------------------------------------------------------------------------------------- //
		// UID -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_UID']);
		if(vcard_element!=null && vcard_element.length==1)	// if the UID attribute is not present exactly once, vCard is considered invalid
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_UID'][0]=vCard.tplC['contentline_UID'];
			vCard.tplM['contentline_UID'][0]=vCard.tplM['contentline_UID'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_UID'][0]=vCard.tplM['contentline_UID'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);
			vCard.tplM['contentline_UID'][0]=vCard.tplM['contentline_UID'][0].replace(/##:::##uid##:::##/g,parsed[4]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_UID'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}
// Old not RFC vCards not contain UID - we ignore this error (UID is generated if vCard is changed)
//		else
//			return false;	// vcard UID not present or present more than once

		// ------------------------------------------------------------------------------------- //
		// FN -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_FN']);
		if(vcard_element!=null && vcard_element.length==1)	// if the FN attribute is not present exactly once, vCard is considered invalid
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_FN'][0]=vCard.tplC['contentline_FN'];
			vCard.tplM['contentline_FN'][0]=vCard.tplM['contentline_FN'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_FN'][0]=vCard.tplM['contentline_FN'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_FN'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}
		else
			return false;	// vcard FN not present or present more than once

		// ------------------------------------------------------------------------------------- //
		// N -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_N']);
		if(vcard_element!=null && vcard_element.length==1)	// if the N attribute is not present exactly once, vCard is considered invalid
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [0]->Family, [1]->Given, [2]->Middle, [3]->Prefix, [4]->Suffix
			parsed_value=vcardSplitValue(parsed[4],';');

			if(parsed_value[0]!=undefined)
				$('[id="vcard_editor"] [data-type="family"]').val(vcardUnescapeValue(parsed_value[0])).change();
			if(parsed_value[1]!=undefined)
				$('[id="vcard_editor"] [data-type="given"]').val(vcardUnescapeValue(parsed_value[1])).change();
			if(parsed_value[2]!=undefined)
				$('[id="vcard_editor"] [data-type="middle"]').val(vcardUnescapeValue(parsed_value[2])).change();
			if(parsed_value[3]!=undefined)
				$('[id="vcard_editor"] [data-type="prefix"]').val(vcardUnescapeValue(parsed_value[3])).change();
			if(parsed_value[4]!=undefined)
				$('[id="vcard_editor"] [data-type="suffix"]').val(vcardUnescapeValue(parsed_value[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_N'][0]=vCard.tplC['contentline_N'];
			vCard.tplM['contentline_N'][0]=vCard.tplM['contentline_N'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_N'][0]=vCard.tplM['contentline_N'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_N'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}
		else
			return false;	// vcard N not present or present more than once

		// ------------------------------------------------------------------------------------- //
		// CATEGORIES -> present max. once because of the CardDavMATE vCard transformations
		vcard_element=vcard.match(vCard.pre['contentline_CATEGORIES']);
		if(vcard_element!=null && vcard_element.length==1)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			$('#tags').importTags(parsed[4]);	// we do not need to unescape the value here!

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_CATEGORIES'][0]=vCard.tplC['contentline_CATEGORIES'];
			vCard.tplM['contentline_CATEGORIES'][0]=vCard.tplM['contentline_CATEGORIES'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_CATEGORIES'][0]=vCard.tplM['contentline_CATEGORIES'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_CATEGORIES'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}

		// ------------------------------------------------------------------------------------- //
		// NOTE -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_NOTE']);
		if(vcard_element!=null)
		{
			if(vcard_element.length==1)	// if the NOTE attribute is present exactly once
			{
				// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
				parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

				$('[id="vcard_editor"] [data-type="\\%note"]').find('textarea').text(vcardUnescapeValue(parsed[4])).change();

				// values not directly supported by the editor (old values are kept intact)
				vCard.tplM['contentline_NOTE'][0]=vCard.tplC['contentline_NOTE'];
				vCard.tplM['contentline_NOTE'][0]=vCard.tplM['contentline_NOTE'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
				vCard.tplM['contentline_NOTE'][0]=vCard.tplM['contentline_NOTE'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

				// remove the processed parameter
				vcard=vcard.replace(vcard_element[0],'\r\n');

				// find the corresponding group data (if exists)
				if(parsed[1]!='')
				{
					re=parsed[1].replace('.','\\..*')+'\r\n';
					while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
					{
						// append the parameter to its parent
						vCard.tplM['contentline_NOTE'][0]+=vcard_element_related[0].substr(2);
						// remove the processed parameter
						vcard=vcard.replace(vcard_element_related[0],'\r\n');
					}
				}
			}
			else
				return false;	// vcard NOTE present more than once
		}

		// ------------------------------------------------------------------------------------- //
		// REV -> what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_REV']);
		if(vcard_element!=null)	// if the REV attribute is exists
		{
			if(vcard_element.length==1)	// and is present exactly once
			{
				// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
				parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

				// values not directly supported by the editor (old values are kept intact)
				vCard.tplM['contentline_REV'][0]=vCard.tplC['contentline_REV'];
				vCard.tplM['contentline_REV'][0]=vCard.tplM['contentline_REV'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);

				// remove the processed parameter
				vcard=vcard.replace(vcard_element[0],'\r\n');

				// find the corresponding group data (if exists)
				if(parsed[1]!='')
				{
					re=parsed[1].replace('.','\\..*')+'\r\n';
					while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
					{
						// append the parameter to its parent
						vCard.tplM['contentline_REV'][0]+=vcard_element_related[0].substr(2);
						// remove the processed parameter
						vcard=vcard.replace(vcard_element_related[0],'\r\n');
					}
				}
			}
			else
				return false;	// vcard REV present more than once
		}

		// ------------------------------------------------------------------------------------- //
		// NICKNAME -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_NICKNAME']);
		if(vcard_element!=null)
		{
			if(vcard_element.length!=1)	// if the NICKNAME attribute is present more than once, vCard is considered invalid
				return false;

			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			$('[data-type="nickname"]').val(vcardUnescapeValue(parsed[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_NICKNAME'][0]=vCard.tplC['contentline_NICKNAME'];
			vCard.tplM['contentline_NICKNAME'][0]=vCard.tplM['contentline_NICKNAME'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_NICKNAME'][0]=vCard.tplM['contentline_NICKNAME'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_NICKNAME'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}

		// ------------------------------------------------------------------------------------- //
		// BDAY
		vcard_element=vcard.match(vCard.pre['contentline_BDAY']);
		if(vcard_element!=null)
		{
			if(vcard_element.length!=1)	// if the BDAY attribute is present more than once, vCard is considered invalid
				return false;

			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			var valid=true;
			try {var date=$.datepicker.parseDate('yy-mm-dd', parsed[4])}
			catch (e) {valid=false}

			if(valid==true)
			{
				$('[data-type="date_bday"]').val(vcardUnescapeValue($.datepicker.formatDate(globalDatepickerFormat, date))).change();

				// values not directly supported by the editor (old values are kept intact)
				vCard.tplM['contentline_BDAY'][0]=vCard.tplC['contentline_BDAY'];
				vCard.tplM['contentline_BDAY'][0]=vCard.tplM['contentline_BDAY'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
				vCard.tplM['contentline_BDAY'][0]=vCard.tplM['contentline_BDAY'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

				// remove the processed parameter
				vcard=vcard.replace(vcard_element[0],'\r\n');

				// find the corresponding group data (if exists)
				if(parsed[1]!='')
				{
					re=parsed[1].replace('.','\\..*')+'\r\n';
					while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
					{
						// append the parameter to its parent
						vCard.tplM['contentline_BDAY'][0]+=vcard_element_related[0].substr(2);
						// remove the processed parameter
						vcard=vcard.replace(vcard_element_related[0],'\r\n');
					}
				}
			}
			else
				return false;	// if the date value is invalid, vCard is considered invalid
		}

		// ------------------------------------------------------------------------------------- //
		// X-ANNIVERSARY
		vcard_element=vcard.match(vCard.pre['contentline_X-ANNIVERSARY']);
		if(vcard_element!=null)
		{
			// if the X-ANNIVERSARY attribute is present more than once we use the first value only (vCard 4.0 like solution)
			//if(vcard_element.length!=1)	// if the X-ANNIVERSARY attribute is present more than once, vCard is considered invalid
			//	return false;

			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			var valid=true;
			try {var date=$.datepicker.parseDate('yy-mm-dd', parsed[4])}
			catch (e) {valid=false}

			if(valid==true)
			{
				$('[data-type="date_anniversary"]').val(vcardUnescapeValue($.datepicker.formatDate(globalDatepickerFormat, date))).change();

				// values not directly supported by the editor (old values are kept intact)
				vCard.tplM['contentline_X-ANNIVERSARY'][0]=vCard.tplC['contentline_X-ANNIVERSARY'];
				vCard.tplM['contentline_X-ANNIVERSARY'][0]=vCard.tplM['contentline_X-ANNIVERSARY'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
				vCard.tplM['contentline_X-ANNIVERSARY'][0]=vCard.tplM['contentline_X-ANNIVERSARY'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

				// remove the processed parameter
				vcard=vcard.replace(vcard_element[0],'\r\n');

				// find the corresponding group data (if exists)
				if(parsed[1]!='')
				{
					re=parsed[1].replace('.','\\..*')+'\r\n';
					while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
					{
						// append the parameter to its parent
						vCard.tplM['contentline_X-ANNIVERSARY'][0]+=vcard_element_related[0].substr(2);
						// remove the processed parameter
						vcard=vcard.replace(vcard_element_related[0],'\r\n');
					}
				}
			}
			else
				return false;	// if the date value is invalid, vCard is considered invalid
		}

		// ------------------------------------------------------------------------------------- //
		// TITLE -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_TITLE']);
		if(vcard_element!=null)
		{
			if(vcard_element.length!=1)	// if the TITLE attribute is present more than once, vCard is considered invalid
				return false;

			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			$('[data-type="title"]').val(vcardUnescapeValue(parsed[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_TITLE'][0]=vCard.tplC['contentline_TITLE'];
			vCard.tplM['contentline_TITLE'][0]=vCard.tplM['contentline_TITLE'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_TITLE'][0]=vCard.tplM['contentline_TITLE'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_TITLE'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}

		// ------------------------------------------------------------------------------------- //
		// ORG -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_ORG']);
		if(vcard_element!=null)
		{
			if(vcard_element.length!=1)	// if the ORG attribute is present more than once, vCard is considered invalid
				return false;

			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [0]->Org, [1..]->Org Units
			parsed_value=vcardSplitValue(parsed[4],';');

			if(parsed_value[0]!=undefined)
				$('[data-type="org"]').val(vcardUnescapeValue(parsed_value[0])).change();
			if(parsed_value[1]!=undefined)
				$('[data-type="department"]').val(vcardUnescapeValue(parsed_value[1])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_ORG'][0]=vCard.tplC['contentline_ORG'];
			vCard.tplM['contentline_ORG'][0]=vCard.tplM['contentline_ORG'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_ORG'][0]=vCard.tplM['contentline_ORG'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);
			vCard.tplM['contentline_ORG'][0]=vCard.tplM['contentline_ORG'][0].replace(/##:::##units_wsc##:::##/g,(parsed_value[2]==undefined ? '' : ';'+parsed_value.slice(2).join(';')));

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_ORG'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}

		// ------------------------------------------------------------------------------------- //
		// X-ABShowAs -> TODO: what to do if present more than once?
		var photo_show_org=false;
		vcard_element=vcard.match(vCard.pre['X-ABShowAs']);
		if(vcard_element!=null)
		{
			if(vcard_element.length>1)	// if the X-ABShowAs attribute is present more than once, vCard is considered invalid
				return false;

			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			if(vcardUnescapeValue(parsed[4]).match(RegExp('^company$','i')))
			{
				$('[data-type="isorg"]').prop('checked',true);
				photo_show_org=true;
			}

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_X-ABShowAs'][0]=vCard.tplC['contentline_X-ABShowAs'];
			vCard.tplM['contentline_X-ABShowAs'][0]=vCard.tplM['contentline_X-ABShowAs'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_X-ABShowAs'][0]=vCard.tplM['contentline_X-ABShowAs'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);
			vCard.tplM['contentline_X-ABShowAs'][0]=vCard.tplM['contentline_X-ABShowAs'][0].replace(/##:::##value##:::##/g,parsed[4]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_X-ABShowAs'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
		}

		// ------------------------------------------------------------------------------------- //
		// PHOTO -> TODO: what to do if present more than once?
		vcard_element=vcard.match(vCard.pre['contentline_PHOTO']);
		if(vcard_element!=null)	// if the PHOTO attribute is present more than once, we use the first value
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

			var img_type='';
			// parsed_value = [1..]->PHOTO-params
			parsed_value=vcardSplitParam(parsed[3]);
			for(i=1;i<parsed_value.length;i++)
				if((type_value=parsed_value[i].match(RegExp('TYPE=(.*)','i')))!=undefined)
				{
					img_type=type_value[1].toLowerCase();
					break;
				}

			if(img_type!='')
			{
				var photo='data:image/'+img_type+';base64,'+parsed[4];
				$('[id="vcard_editor"] [data-type="photo"]').css('visibility', 'hidden').prop('src', photo);

				function imageLoaded(real_width, real_height)
				{
					var photo_div=$('[id="vcard_editor"] .photo_div');
					var div_width=photo_div.width();
					var div_height=photo_div.height();
					var photo=$('[id="vcard_editor"] [data-type="photo"]');

					if(real_width-div_width<real_height-div_height)
						photo.css({width: '141px', height: 'auto', 'margin-top': Math.ceil((div_height-div_width/real_width*real_height)/2)+'px'});
					else
						photo.css({width: 'auto', height: '160px', 'margin-left': Math.ceil((div_width-div_height/real_height*real_width)/2)+'px'});

					photo.css('visibility', 'visible');
				}

				var newImg = new Image();
				newImg.src = photo;
				if(newImg.complete)
					imageLoaded(newImg.width, newImg.height);
				else
					$(newImg).load(function(){imageLoaded(newImg.width, newImg.height)});

			}
			// photo URL is used by iCloud but it requires iCloud session cookie :-(
//			else if(parsed[4].match(RegExp('^https?://','i'))!=null)
//				$('[id="vcard_editor"] [data-type="photo"]').attr('src',parsed[4]);

	// We only show the photo, no other operations are supported (we keep the "photo" unprocessed)
/*
			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_PHOTO'][0]=vCard.tplC['contentline_PHOTO'];
			vCard.tplM['contentline_PHOTO'][0]=vCard.tplM['contentline_PHOTO'][0].replace(/##:::##group_wd##:::##/g,parsed[1]);
			vCard.tplM['contentline_PHOTO'][0]=vCard.tplM['contentline_PHOTO'][0].replace(/##:::##params_wsc##:::##/g,parsed[3]);

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_PHOTO'][0]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
*/
		}
		else	// use default icons (see X-ABShowAs above)
		{
			if(photo_show_org==true)
				$('#vcard_editor').find('[data-type="photo"]').attr('src', OC.imagePath('carddavmate', 'company.svg'));
			else
				$('#vcard_editor').find('[data-type="photo"]').attr('src', OC.imagePath('carddavmate', 'user.svg'));
		}

		// ------------------------------------------------------------------------------------- //
		// ADR
		element_i=0;
		while((vcard_element=vcard.match(vCard.pre['contentline_ADR']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_param = [1..]->ADR-params
			parsed_param=vcardSplitParam(parsed[3]);
			// parsed_value = [1..]->ADR elements
			parsed_value=vcardSplitValue(parsed[4],';');

			// click to "add" button if not enought data rows present
			var found=0;
			$('[id="vcard_editor"] [data-type="\\%address"]').last().find('[data-type="value"]').each(
				function(index,element)
				{
					if($(element).val()!='')
					{
						found=1;
						return false;
					}
				}
			);
			if(found)
				$('[id="vcard_editor"] [data-type="\\%address"]').last().find('[data-type="\\%add"]').find('input[type="image"]').click();

			// too many attributes (increase the maximum allowed)
			var found=0;
			$('[id="vcard_editor"] [data-type="\\%address"]').last().find('[data-type="value"]').each(
				function(index,element)
				{
					if($(element).val()!='')
					{
						found=1;
						return false;
					}
				}
			);
			if(found)
				return false;

			// get the "TYPE=" values array
			pref=0;	//by default there is no preferred address
			type_values=Array();
			j=0;
			for(i=1;i<parsed_param.length;i++)
			{
				type_values_tmp=parsed_param[i].replace(/^[^=]+=/,'');
				// if one value is a comma separated value of parameters
				type_values_tmp_2=type_values_tmp.split(',');
				for(m=0;m<type_values_tmp_2.length;m++)
					if(type_values_tmp_2[m].match(RegExp('^pref$','i'))==undefined)
						type_values[j++]=vcardUnescapeValue(type_values_tmp_2[m]).toLowerCase();
					else
						pref=1;
			}
			// APPLE SPECIFIC data:
			// find the corresponding group.X-ABLabel: used by APPLE as "TYPE"
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABLabel:(.*)')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABLabel value
					if(type_values.indexOf(vcard_element_related[1].toLowerCase())==-1)
						type_values[j++]=vcardUnescapeValue('/'+vcard_element_related[1]+'/').toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			// find the corresponding group.X-ABADR: used by APPLE as short address country
			var addr_country='';
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABADR:(.*)')+'\r\n';
				if((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABADR value
					addr_country=vcardUnescapeValue(vcard_element_related[1]).toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}

			type_values_txt=type_values.unique().sort().join('_');	// TYPE=HOME;TYPE=HOME;TYPE=FAX; -> array('FAX','HOME') -> 'fax_home'
			type_values_txt_label=type_values.unique().sort().join(' ').replace(RegExp('/','g'),'');	// TYPE=HOME;TYPE=HOME;TYPE=FAX; -> array('FAX','HOME') -> 'fax home'
			// if no address type defined, we use the 'work' type as default
			if(type_values_txt=='')
				type_values_txt=type_values_txt_label='work';

			// get the default available types
			type_list=new Array();
			$('[data-type="\\%address"]:eq('+element_i+')').find('[data-type="address_type"]').children().each( function(index,element){type_list[type_list.length]=$(element).attr('data-type');});
			// if an existing type regex matches the new type, use the old type
			// and replace the old type definition with new type definition to comforn the server vCard type format
			for(i=0;i<type_list.length;i++)
				if(dataTypes['address_type'][type_list[i]]!=undefined && type_values_txt.match(RegExp(dataTypes['address_type'][type_list[i]]))!=null)
				{
					$('[data-type="\\%address"]').find('[data-type="address_type"]').find('[data-type="'+type_list[i]+'"]').attr('data-type',type_values_txt);
					break;
				}

			// address type: select or append to existing types and select
			select_element=$('[data-type="\\%address"]:eq('+element_i+') [data-type="address_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="address_type"] :first-child').clone().attr('data-type',type_values_txt).text(type_values_txt_label).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="address_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%address"]:eq('+element_i+') [data-type="address_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]').prop('selected',true);
			}

			var tmp=$('[data-type="\\%address"]:eq('+element_i+')');
			var found;
			if((found=tmp.find('[data-type="\\%country"]').children('[data-type="'+jqueryEscapeSelector(addr_country)+'"]')).length>0 || (found=tmp.find('[data-type="\\%country"]').children('[data-full-name="'+jqueryEscapeSelector(parsed_value[6])+'"]')).length>0)
				found.prop('selected',true);
			else if(typeof globalAddressCountryEquivalence!='undefined' && globalAddressCountryEquivalence.length>0 && parsed_value[6]!=undefined)	// unknown ADR format (country not detected)
			{
				for(i=0;i<globalAddressCountryEquivalence.length;i++)
					if(parsed_value[6].match(RegExp(globalAddressCountryEquivalence[i].regex,'i'))!=null)
					{
						tmp.find('[data-type="\\%country"]').children('[data-type="'+jqueryEscapeSelector(globalAddressCountryEquivalence[i].country)+'"]').prop('selected',true);
						break;
					}
			}
			// Note: 
			//  if no country detected, the default is used (see globalDefaultAddressCountry in config.js)

			tmp.find('[data-autoselect]').change();

			$('[data-type="\\%address"]:eq('+element_i+') [data-addr-field="pobox"]').val(vcardUnescapeValue(parsed_value[0])).change();
			$('[data-type="\\%address"]:eq('+element_i+') [data-addr-field="extaddr"]').val(vcardUnescapeValue(parsed_value[1])).change();
			$('[data-type="\\%address"]:eq('+element_i+') [data-addr-field="street"]').val(vcardUnescapeValue(parsed_value[2])).change();
			$('[data-type="\\%address"]:eq('+element_i+') [data-addr-field="locality"]').val(vcardUnescapeValue(parsed_value[3])).change();
			$('[data-type="\\%address"]:eq('+element_i+') [data-addr-field="region"]').val(vcardUnescapeValue(parsed_value[4])).change();
			$('[data-type="\\%address"]:eq('+element_i+') [data-addr-field="code"]').val(vcardUnescapeValue(parsed_value[5])).change();


			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_ADR'][element_i]=vCard.tplC['contentline_ADR'];
			vCard.tplM['contentline_ADR'][element_i]=vCard.tplM['contentline_ADR'][element_i].replace(/##:::##group_wd##:::##/g,parsed[1]);
			// if the address was preferred, we keep it so (we not support preferred address selection directly by editor)
			if(pref==1)
				vCard.tplM['contentline_ADR'][element_i]=vCard.tplM['contentline_ADR'][element_i].replace(/##:::##params_wsc##:::##/g, '##:::##params_wsc##:::##;TYPE=PREF');

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_ADR'][element_i]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			element_i++;
		}

		// ------------------------------------------------------------------------------------- //
		// TEL
		element_i=0;
		while((vcard_element=vcard.match(vCard.pre['contentline_TEL']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [1..]->TEL-params
			parsed_value=vcardSplitParam(parsed[3]);

			// click to "add" button if not enought data rows present
			if($('[id="vcard_editor"] [data-type="\\%phone"]').last().find('[data-type="value"]').val()!='')
				$('[id="vcard_editor"] [data-type="\\%phone"]').last().find('[data-type="\\%add"]').find('input[type="image"]').click();

			// too many attributes (increase the maximum allowed)
			if($('[id="vcard_editor"] [data-type="\\%phone"]').last().find('[data-type="value"]').val()!='')
				return false;

			// get the "TYPE=" values array
			pref=0;	//by default there is no preferred phone number
			type_values=Array();
			j=0;

			for(i=1;i<parsed_value.length;i++)
			{
				type_values_tmp=parsed_value[i].replace(/^[^=]+=/,'');
				// if one value is a comma separated value of parameters
				type_values_tmp_2=type_values_tmp.split(',');
				for(m=0;m<type_values_tmp_2.length;m++)
					if(type_values_tmp_2[m].match(RegExp('^pref$','i'))==undefined)
						type_values[j++]=vcardUnescapeValue(type_values_tmp_2[m]).toLowerCase();
					else
						pref=1;
			}
			// APPLE SPECIFIC types:
			// find the corresponding group.X-ABLabel: used by APPLE as "TYPE"
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABLabel:(.*)')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABLabel value
					if(type_values.indexOf(vcard_element_related[1].toLowerCase())==-1)
						type_values[j++]=vcardUnescapeValue('/'+vcard_element_related[1]+'/').toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}

			type_values_txt=type_values.unique().sort().join('_');	// TYPE=HOME;TYPE=HOME;TYPE=FAX; -> array('FAX','HOME') -> 'fax_home'
			type_values_txt_label=type_values.unique().sort().join(' ').replace(RegExp('/','g'),'');	// TYPE=HOME;TYPE=HOME;TYPE=FAX; -> array('FAX','HOME') -> 'fax home'
			// if no phone type defined, we use the 'cell' type as default
			if(type_values_txt=='')
				type_values_txt=type_values_txt_label='cell';

			// get the default available types
			type_list=new Array();
			$('[data-type="\\%phone"]:eq('+element_i+')').find('[data-type="phone_type"]').children().each( function(index,element){type_list[type_list.length]=$(element).attr('data-type');});
			// if an existing type regex matches the new type, use the old type
			// and replace the old type definition with new type definition to comforn the server vCard type format
			for(i=0;i<type_list.length;i++)
				if(dataTypes['phone_type'][type_list[i]]!=undefined && type_values_txt.match(RegExp(dataTypes['phone_type'][type_list[i]]))!=null)
				{
					$('[data-type="\\%phone"]').find('[data-type="phone_type"]').find('[data-type="'+type_list[i]+'"]').attr('data-type',type_values_txt);
					break;
				}

			// phone type: select or append to existing types and select
			select_element=$('[data-type="\\%phone"]:eq('+element_i+') [data-type="phone_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="phone_type"] :first-child').clone().attr('data-type',type_values_txt).text(type_values_txt_label).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="phone_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%phone"]:eq('+element_i+') [data-type="phone_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]').prop('selected',true);
			}

			$('[data-type="\\%phone"]:eq('+element_i+') [data-type="value"]').val(vcardUnescapeValue(parsed[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_TEL'][element_i]=vCard.tplC['contentline_TEL'];
			vCard.tplM['contentline_TEL'][element_i]=vCard.tplM['contentline_TEL'][element_i].replace(/##:::##group_wd##:::##/g,parsed[1]);
			// if the phone number was preferred, we keep it so (we not support preferred number selection directly by editor)
			if(pref==1)
				vCard.tplM['contentline_TEL'][element_i]=vCard.tplM['contentline_TEL'][element_i].replace(/##:::##params_wsc##:::##/g, '##:::##params_wsc##:::##;TYPE=PREF');

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_TEL'][element_i]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			element_i++;
		}

		// ------------------------------------------------------------------------------------- //
		// EMAIL
		element_i=0;
		while((vcard_element=vcard.match(vCard.pre['contentline_EMAIL']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [1..]->EMAIL-params
			parsed_value=vcardSplitParam(parsed[3]);

			// click to "add" button if not enought data rows present
			if($('[id="vcard_editor"] [data-type="\\%email"]').last().find('[data-type="value"]').val()!='')
				$('[id="vcard_editor"] [data-type="\\%email"]').last().find('[data-type="\\%add"]').find('input[type="image"]').click();

			// too many attributes (increase the maximum allowed)
			if($('[id="vcard_editor"] [data-type="\\%email"]').last().find('[data-type="value"]').val()!='')
				return false;

			// get the "TYPE=" values array
			pref=0;	//by default there is no preferred email address
			type_values=Array();
			j=0;
			for(i=1;i<parsed_value.length;i++)
			{
				type_values_tmp=parsed_value[i].replace(/^[^=]+=/,'');
				// if one value is a comma separated value of parameters
				type_values_tmp_2=type_values_tmp.split(',');
				for(m=0;m<type_values_tmp_2.length;m++)
					if(type_values_tmp_2[m].match(RegExp('^pref$','i'))==undefined)
						type_values[j++]=vcardUnescapeValue(type_values_tmp_2[m]).toLowerCase();
					else
						pref=1;
			}
			// APPLE SPECIFIC types:
			// find the corresponding group.X-ABLabel: used by APPLE as "TYPE"
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABLabel:(.*)')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABLabel value
					if(type_values.indexOf(vcard_element_related[1].toLowerCase())==-1)
						type_values[j++]=vcardUnescapeValue('/'+vcard_element_related[1]+'/').toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}

			type_values_txt=type_values.unique().sort().join('_');	// TYPE=INTERNET;TYPE=INTERNET;TYPE=HOME; -> array('HOME','INTERNET') -> 'home_internet'
			type_values_txt_label=type_values.unique().sort().join(' ').replace(RegExp('/','g'),'');	// TYPE=INTERNET;TYPE=INTERNET;TYPE=HOME; -> array('HOME','INTERNET') -> 'home internet'
			// if no email type defined, we use the 'home' type as default
			if(type_values_txt=='')
				type_values_txt=type_values_txt_label='home_internet';

			// get the default available types
			type_list=new Array();
			$('[data-type="\\%email"]:eq('+element_i+')').find('[data-type="email_type"]').children().each( function(index,element){type_list[type_list.length]=$(element).attr('data-type');});
			// if an existing type regex matches the new type, use the old type
			// and replace the old type definition with new type definition to comforn the server vCard type format
			for(i=0;i<type_list.length;i++)
				if(dataTypes['email_type'][type_list[i]]!=undefined && type_values_txt.match(RegExp(dataTypes['email_type'][type_list[i]]))!=null)
				{
					$('[data-type="\\%email"]').find('[data-type="email_type"]').find('[data-type="'+type_list[i]+'"]').attr('data-type',type_values_txt);
					break;
				}

			// email type: select or append to existing types and select
			select_element=$('[data-type="\\%email"]:eq('+element_i+') [data-type="email_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="email_type"] :first-child').clone().attr('data-type',type_values_txt).text(type_values_txt_label).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="email_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%email"]:eq('+element_i+') [data-type="email_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]').prop('selected',true);
			}

			$('[data-type="\\%email"]:eq('+element_i+') [data-type="value"]').val(vcardUnescapeValue(parsed[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_EMAIL'][element_i]=vCard.tplC['contentline_EMAIL'];
			vCard.tplM['contentline_EMAIL'][element_i]=vCard.tplM['contentline_EMAIL'][element_i].replace(/##:::##group_wd##:::##/g,parsed[1]);
			// if the phone number was preferred, we keep it so (we not support preferred number selection directly by editor)
			if(pref==1)
				vCard.tplM['contentline_EMAIL'][element_i]=vCard.tplM['contentline_EMAIL'][element_i].replace(/##:::##params_wsc##:::##/g, '##:::##params_wsc##:::##;TYPE=PREF');

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_EMAIL'][element_i]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			element_i++;
		}

		// ------------------------------------------------------------------------------------- //
		// URL
		element_i=0;
		while((vcard_element=vcard.match(vCard.pre['contentline_URL']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [1..]->URL-params
			parsed_value=vcardSplitParam(parsed[3]);

			// click to "add" button if not enought data rows present
			if($('[id="vcard_editor"] [data-type="\\%url"]').last().find('[data-type="value"]').val()!='')
				$('[id="vcard_editor"] [data-type="\\%url"]').last().find('[data-type="\\%add"]').find('input[type="image"]').click();

			// too many attributes (increase the maximum allowed)
			if($('[id="vcard_editor"] [data-type="\\%url"]').last().find('[data-type="value"]').val()!='')
				return false;

			// get the "TYPE=" values array
			pref=0;	//by default there is no preferred url address
			type_values=Array();
			j=0;
			for(i=1;i<parsed_value.length;i++)
			{
				type_values_tmp=parsed_value[i].replace(/^[^=]+=/,'');
				// if one value is a comma separated value of parameters
				type_values_tmp_2=type_values_tmp.split(',');
				for(m=0;m<type_values_tmp_2.length;m++)
					if(type_values_tmp_2[m].match(RegExp('^pref$','i'))==undefined)
						type_values[j++]=vcardUnescapeValue(type_values_tmp_2[m]).toLowerCase();
					else
						pref=1;
			}
			// APPLE SPECIFIC types:
			// find the corresponding group.X-ABLabel: used by APPLE as "TYPE"
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABLabel:(.*)')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABLabel value
					if(type_values.indexOf(vcard_element_related[1].toLowerCase())==-1)
						type_values[j++]=vcardUnescapeValue('/'+vcard_element_related[1]+'/').toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}

			type_values_txt=type_values.unique().sort().join('_');	// TYPE=WORK;TYPE=WORK;TYPE=HOME; -> array('HOME','WORK') -> 'home_work'
			type_values_txt_label=type_values.unique().sort().join(' ').replace(RegExp('/','g'),'');	// TYPE=WORK;TYPE=WORK;TYPE=HOME; -> array('HOME','WORK') -> 'home work'
			// if no url type defined, we use the 'homepage' type as default
			if(type_values_txt=='')
				type_values_txt=type_values_txt_label='homepage';

			// get the default available types
			type_list=new Array();
			$('[data-type="\\%url"]:eq('+element_i+')').find('[data-type="url_type"]').children().each( function(index,element){type_list[type_list.length]=$(element).attr('data-type');});
			// if an existing type regex matches the new type, use the old type
			// and replace the old type definition with new type definition to comforn the server vCard type format
			for(i=0;i<type_list.length;i++)
				if(dataTypes['url_type'][type_list[i]]!=undefined && type_values_txt.match(RegExp(dataTypes['url_type'][type_list[i]]))!=null)
				{
					$('[data-type="\\%url"]').find('[data-type="url_type"]').find('[data-type="'+type_list[i]+'"]').attr('data-type',type_values_txt);
					break;
				}

			// url type: select or append to existing types and select
			select_element=$('[data-type="\\%url"]:eq('+element_i+') [data-type="url_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="url_type"] :first-child').clone().attr('data-type',type_values_txt).text(type_values_txt_label).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="url_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%url"]:eq('+element_i+') [data-type="url_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]').prop('selected',true);
			}

			$('[data-type="\\%url"]:eq('+element_i+') [data-type="value"]').val(vcardUnescapeValue(parsed[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_URL'][element_i]=vCard.tplC['contentline_URL'];
			vCard.tplM['contentline_URL'][element_i]=vCard.tplM['contentline_URL'][element_i].replace(/##:::##group_wd##:::##/g,parsed[1]);
			// if the URL was preferred, we keep it so (we not support preferred number selection directly by editor)
			if(pref==1)
				vCard.tplM['contentline_URL'][element_i]=vCard.tplM['contentline_URL'][element_i].replace(/##:::##params_wsc##:::##/g, '##:::##params_wsc##:::##;TYPE=PREF');

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_URL'][element_i]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			element_i++;
		}

		// ------------------------------------------------------------------------------------- //
		// X-ABRELATEDNAMES
		element_i=0;
		while((vcard_element=vcard.match(vCard.pre['contentline_X-ABRELATEDNAMES']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [1..]->X-ABRELATEDNAMES-params
			parsed_value=vcardSplitParam(parsed[3]);

			// click to "add" button if not enought data rows present
			if($('[id="vcard_editor"] [data-type="\\%person"]').last().find('[data-type="value"]').val()!='')
				$('[id="vcard_editor"] [data-type="\\%person"]').last().find('[data-type="\\%add"]').find('input[type="image"]').click();

			// too many attributes (increase the maximum allowed)
			if($('[id="vcard_editor"] [data-type="\\%person"]').last().find('[data-type="value"]').val()!='')
				return false;

			// get the "TYPE=" values array
			pref=0;	//by default there is no preferred person
			type_values=Array();
			j=0;
			for(i=1;i<parsed_value.length;i++)
			{
				type_values_tmp=parsed_value[i].replace(/^[^=]+=/,'');
				// if one value is a comma separated value of parameters
				type_values_tmp_2=type_values_tmp.split(',');
				for(m=0;m<type_values_tmp_2.length;m++)
					if(type_values_tmp_2[m].match(RegExp('^pref$','i'))==undefined)
						type_values[j++]=vcardUnescapeValue(type_values_tmp_2[m]).toLowerCase();
					else
						pref=1;
			}
			// APPLE SPECIFIC types:
			// find the corresponding group.X-ABLabel: used by APPLE as "TYPE"
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABLabel:(.*)')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABLabel value
					if(type_values.indexOf(vcard_element_related[1].toLowerCase())==-1)
						type_values[j++]=vcardUnescapeValue('/'+vcard_element_related[1]+'/').toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}

			type_values_txt=type_values.unique().sort().join('_');	// TYPE=INTERNET;TYPE=INTERNET;TYPE=HOME; -> array('HOME','INTERNET') -> 'home_internet'
			type_values_txt_label=type_values.unique().sort().join(' ').replace(RegExp('/','g'),'');	// TYPE=INTERNET;TYPE=INTERNET;TYPE=HOME; -> array('HOME','INTERNET') -> 'home internet'
			// if no person type defined, we use the 'other' type as default
			if(type_values_txt=='')
				type_values_txt=type_values_txt_label='other';

			// get the default available types
			type_list=new Array();
			$('[data-type="\\%person"]:eq('+element_i+')').find('[data-type="person_type"]').children().each( function(index,element){type_list[type_list.length]=$(element).attr('data-type');});
			// if an existing type regex matches the new type, use the old type
			// and replace the old type definition with new type definition to comforn the server vCard type format
			for(i=0;i<type_list.length;i++)
				if(dataTypes['person_type'][type_list[i]]!=undefined && type_values_txt.match(RegExp(dataTypes['person_type'][type_list[i]]))!=null)
				{
					$('[data-type="\\%person"]').find('[data-type="person_type"]').find('[data-type="'+type_list[i]+'"]').attr('data-type',type_values_txt);
					break;
				}

			// person type: select or append to existing types and select
			select_element=$('[data-type="\\%person"]:eq('+element_i+') [data-type="person_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="person_type"] :first-child').clone().attr('data-type',type_values_txt).text(type_values_txt_label).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="person_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%person"]:eq('+element_i+') [data-type="person_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]').prop('selected',true);
			}

			$('[data-type="\\%person"]:eq('+element_i+') [data-type="value"]').val(vcardUnescapeValue(parsed[4])).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_X-ABRELATEDNAMES'][element_i]=vCard.tplC['contentline_X-ABRELATEDNAMES'];
			vCard.tplM['contentline_X-ABRELATEDNAMES'][element_i]=vCard.tplM['contentline_X-ABRELATEDNAMES'][element_i].replace(/##:::##group_wd##:::##/g,parsed[1]);
			// if the phone person was preferred, we keep it so (we not support preferred person selection directly by editor)
			if(pref==1)
				vCard.tplM['contentline_X-ABRELATEDNAMES'][element_i]=vCard.tplM['contentline_X-ABRELATEDNAMES'][element_i].replace(/##:::##params_wsc##:::##/g, '##:::##params_wsc##:::##;TYPE=PREF');

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_X-ABRELATEDNAMES'][element_i]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			element_i++;
		}

		// ------------------------------------------------------------------------------------- //
		// IMPP
		element_i=0;
		while((vcard_element=vcard.match(vCard.pre['contentline_IMPP']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			parsed=vcard_element[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [1..]->IMPP-params
			parsed_value=vcardSplitParam(parsed[3]);

			// click to "add" button if not enought data rows present
			if($('[id="vcard_editor"] [data-type="\\%im"]').last().find('[data-type="value"]').val()!='')
				$('[id="vcard_editor"] [data-type="\\%im"]').last().find('[data-type="\\%add"]').find('input[type="image"]').click();

			// too many attributes (increase the maximum allowed)
			if($('[id="vcard_editor"] [data-type="\\%im"]').last().find('[data-type="value"]').val()!='')
				return false;

			// get the "TYPE=" & "X-SERVICE-TYPE" values array
			pref=0;	//by default there is no preferred IM
			type_values=Array();
			service_type_value='';
			j=0;
			for(i=1;i<parsed_value.length;i++)
			{
				if(parsed_value[i].match(RegExp('^TYPE=','i')))
				{
					type_values_tmp=parsed_value[i].replace(/^[^=]+=/,'');
					// if one value is a comma separated value of parameters
					type_values_tmp_2=type_values_tmp.split(',');
					for(m=0;m<type_values_tmp_2.length;m++)
						if(type_values_tmp_2[m].match(RegExp('^pref$','i'))==undefined)
							type_values[j++]=vcardUnescapeValue(type_values_tmp_2[m]).toLowerCase();
						else
							pref=1;
				}
				else if(parsed_value[i].match(RegExp('^X-SERVICE-TYPE=','i')))
					service_type_value=vcardUnescapeValue(parsed_value[i].replace(/^[^=]+=/,'')).toLowerCase();
			}
			// APPLE SPECIFIC types:
			// find the corresponding group.X-ABLabel: used by APPLE as "TYPE"
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.X-ABLabel:(.*)')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// get the X-ABLabel value
					if(type_values.indexOf(vcard_element_related[1].toLowerCase())==-1)
						type_values[j++]=vcardUnescapeValue('/'+vcard_element_related[1]+'/').toLowerCase();
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}

			type_values_txt=type_values.unique().sort().join('_');	// TYPE=INTERNET;TYPE=INTERNET;TYPE=HOME; -> array('HOME','INTERNET') -> 'home_internet'
			type_values_txt_label=type_values.unique().sort().join(' ').replace(RegExp('/','g'),'');	// TYPE=INTERNET;TYPE=INTERNET;TYPE=HOME; -> array('HOME','INTERNET') -> 'home internet'
			// if no IMPP type defined, we use the 'other' type as default
			if(type_values_txt=='')
				type_values_txt=type_values_txt_label='other';

			// get the default available types
			type_list=new Array();
			$('[data-type="\\%im"]:eq('+element_i+')').find('[data-type="im_type"]').children().each( function(index,element){type_list[type_list.length]=$(element).attr('data-type');});
			// if an existing type regex matches the new type, use the old type
			// and replace the old type definition with new type definition to comforn the server vCard type format
			for(i=0;i<type_list.length;i++)
				if(dataTypes['im_type'][type_list[i]]!=undefined && type_values_txt.match(RegExp(dataTypes['im_type'][type_list[i]]))!=null)
				{
					$('[data-type="\\%im"]').find('[data-type="im_type"]').find('[data-type="'+type_list[i]+'"]').attr('data-type',type_values_txt);
					break;
				}

			// IM type: select or append to existing types and select
			select_element=$('[data-type="\\%im"]:eq('+element_i+') [data-type="im_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="im_type"] :first-child').clone().attr('data-type',type_values_txt).text(type_values_txt_label).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="im_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%im"]:eq('+element_i+') [data-type="im_type"]').find('[data-type="'+jqueryEscapeSelector(type_values_txt)+'"]').prop('selected',true);
			}
			// IM service type: select or append to existing types and select
			select_element=$('[data-type="\\%im"]:eq('+element_i+') [data-type="im_service_type"]').find('[data-type="'+jqueryEscapeSelector(service_type_value)+'"]');
			if(select_element.length==1)
				select_element.prop('selected',true);
			else if(select_element.length==0)
			{
				// create the missing option
				new_opt=$('[id="vcard_editor"] [data-type="im_service_type"] :first-child').clone().attr('data-type',service_type_value).text(service_type_value).wrap('<div>').parent().html();
				// append the option to all element of this type
				$('[id="vcard_editor"] [data-type="im_service_type"] :last-child').prev().after(new_opt);
				// select the option on the current type
				$('[data-type="\\%im"]:eq('+element_i+') [data-type="im_service_type"]').find('[data-type="'+jqueryEscapeSelector(service_type_value)+'"]').prop('selected',true);
			}

			$('[data-type="\\%im"]:eq('+element_i+') [data-type="value"]').val(vcardUnescapeValue(parsed[4].replace(RegExp('^[^:]+:'),''))).change();

			// values not directly supported by the editor (old values are kept intact)
			vCard.tplM['contentline_IMPP'][element_i]=vCard.tplC['contentline_IMPP'];
			vCard.tplM['contentline_IMPP'][element_i]=vCard.tplM['contentline_IMPP'][element_i].replace(/##:::##group_wd##:::##/g,parsed[1]);
			// if the IMPP accound was preferred, we keep it so (we not support preferred person selection directly by editor)
			if(pref==1)
				vCard.tplM['contentline_IMPP'][element_i]=vCard.tplM['contentline_IMPP'][element_i].replace(/##:::##params_wsc##:::##/g, '##:::##params_wsc##:::##;TYPE=PREF');

			// remove the processed parameter
			vcard=vcard.replace(vcard_element[0],'\r\n');

			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\..*')+'\r\n';
				while((vcard_element_related=vcard.match(RegExp('\r\n'+re,'m')))!=null)
				{
					// append the parameter to its parent
					vCard.tplM['contentline_IMPP'][element_i]+=vcard_element_related[0].substr(2);
					// remove the processed parameter
					vcard=vcard.replace(vcard_element_related[0],'\r\n');
				}
			}
			element_i++;
		}

		// ------------------------------------------------------------------------------------- //
		// Store the vCard URL to XML
		$('#vcard_editor').attr('data-account-uid',inputContact.accountUID);
		$('#vcard_editor').attr('data-url',inputContact.uid);
		$('#vcard_editor').attr('data-etag',inputContact.etag);

		// UID is stored also in the Cancel button (for Add -> Cancel support /loading the previous active contact/)
		$('#vcard_editor').find('[data-type="cancel"]').attr('data-id',inputContact.uid);

		processEditorElements('hide', inputIsReadonly);

		// Unprocessed unrelated vCard elements
		vCard.tplM['unprocessed_unrelated']=vcard;

//		if(vcard!='\r\n')
//			console.log('Warning: [vCard unprocessed unrelated]: '+vcard);

		return true;
	}
	else
		return false;
}

function basicRFCFixesAndCleanup(vcardString)
{
	// If vCard contains only '\n' instead of '\r\n' we fix it
	if(vcardString.match(RegExp('\r','m'))==null)
		vcardString=vcardString.replace(RegExp('\n','gm'),'\r\n');

	// remove multiple empty lines
	vcardString=vcardString.replace(RegExp('(\r\n)+','gm'),'\r\n');

	// append '\r\n' to the end of the vCard if missing
	if(vcardString[vcardString.length-1]!='\n')
		vcardString+='\r\n';

	// remove line folding
	vcardString=vcardString.replace(RegExp('\r\n'+vCard.re['WSP'],'gm'),'');

	// ------------------------------------------------------------------------------------- //
	// begin CATEGORIES merge to one CATEGORIES attribute (sorry for related attributes)
	// note: we cannot do this in RFCFixesAndCleanup or normalizeVcard
	var categoriesArr=[];
	while((vcard_element=vcardString.match(vCard.pre['contentline_CATEGORIES']))!=null)
	{
		// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
		parsed=vcard_element[0].match(vCard.pre['contentline_parse']);

		categoriesArr[categoriesArr.length]=parsed[4];

		// remove the processed parameter
		vcardString=vcardString.replace(vcard_element[0],'\r\n');

		// find the corresponding group data (if exists)
		if(parsed[1]!='')
		{
			re=parsed[1].replace('.','\\..*')+'\r\n';
			while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
				// remove the processed parameter
				vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
		}
	}
	var categoriesTxt=categoriesArr.join(',');

	var tmp=vcardString.split('\r\n');
	tmp.splice(tmp.length-2,0,'CATEGORIES:'+categoriesTxt);
	// end CATEGORIES cleanup
	// ------------------------------------------------------------------------------------- //

	// ------------------------------------------------------------------------------------- //
	// begin SoGo fixes (company vCards without N and FN attributes)
	//  we must perform vCard fixes here because the N and FN attributes are used in the collection list

	// if N attribute is missing we add it
	if(vcardString.match(vCard.pre['contentline_N'])==null)
		tmp.splice(1,0,'N:;;;;');

	// if FN attribute is missing we add it
	if(vcardString.match(vCard.pre['contentline_FN'])==null)
	{
		var fn_value='';
		// if there is an ORG attribute defined, we use the company name as fn_value (instead of empty string)
		if((tmp2=vcardString.match(vCard.pre['contentline_ORG']))!=null)
		{
			// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
			var parsed=tmp2[0].match(vCard.pre['contentline_parse']);
			// parsed_value = [0]->Org, [1..]->Org Units
			parsed_value=vcardSplitValue(parsed[4],';');
			fn_value=parsed_value[0];
		}
		tmp.splice(1,0,'FN:'+fn_value);
	}
	vcardString=tmp.join('\r\n');
	// end SoGo fixes
	// ------------------------------------------------------------------------------------- //

	return {vcard: vcardString, categories: categoriesTxt};
}

function additionalRFCFixes(vcardString)
{
	// ------------------------------------------------------------------------------------- //
	var tmp=vcardString.split('\r\n');

	// update non-RFC attributes (special transformations)
	for(var i=1;i<tmp.length-2;i++)
	{
		// parsed = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
		var parsed=('\r\n'+tmp[i]+'\r\n').match(vCard.pre['contentline_parse']);

		if(parsed!=null)
		{
			switch(parsed[2])
			{
				case 'TEL':
					// remove the non-RFC params (Evolution bug)
					parsed_value=vcardSplitParam(parsed[3]);
					for(var j=parsed_value.length-1;j>0;j--)
						if(parsed_value[j].match(RegExp('^'+vCard.re['tel-param']+'$','i'))==null)
							parsed_value.splice(j,1);

					parsed[3]=parsed_value.join(';');
					tmp[i]=parsed[1]+parsed[2]+parsed[3]+':'+parsed[4];
					break;
				case 'EMAIL':
					// transform the params separated by ',' to 'TYPE=' params and remove the non-RFC params (Evolution bug)
					parsed_value=vcardSplitParam(parsed[3]);
					for(var j=parsed_value.length-1;j>0;j--)
						if(parsed_value[j].match(RegExp('^'+vCard.re['email-param']+'$','i'))==null)
						{
							if((transformed=parsed_value[j].replace(RegExp(',','g'),';TYPE=')).match(RegExp('^'+vCard.re['email-param']+'(?:;'+vCard.re['email-param']+')*$','i'))!=null)
								parsed_value[j]=transformed;
							else
								parsed_value.splice(j,1);
						}

					parsed[3]=parsed_value.join(';');
					// add missing and required "internet" type (Sogo bug)
					if(parsed[3].match(RegExp(';TYPE=internet;|;TYPE=internet$','i'))==null)
						parsed[3]+=';TYPE=INTERNET';

					tmp[i]=parsed[1]+parsed[2]+parsed[3]+':'+parsed[4];
					break;
// the upcoming vCard 4.0 allows params for URL and many clients use it also in vCard 3.0
//				case 'URL':	// no params allowed for URL (Evolution bug)
//					tmp[i]=parsed[1]+parsed[2]+':'+parsed[4];
//					break;
				default:
					break;
			}
		}
	}
	vcardString=tmp.join('\r\n');
	// ------------------------------------------------------------------------------------- //

	return vcardString;
}

// transform the vCard to the editor expected format
function normalizeVcard(vcardString)
{
	// remove the PRODID element (unusable for the editor)
	while((parsed=vcardString.match(vCard.pre['contentline_PRODID']))!=null)
		vcardString=vcardString.replace(parsed[0],'\r\n');

	tmp=vcardString.split('\r\n');
	vcard_begin=tmp[0].replace(RegExp('^[^.]+\\.'),'item.')+'\r\n';
	vcard_end=tmp[tmp.length-2].replace(RegExp('^[^.]+\\.'),'item.')+'\r\n';
	// remove the vCard BEGIN and END and all duplicate entries (usually created by other buggy clients)
	vcardString='\r\n'+tmp.slice(1,tmp.length-2).sort().unique().join('\r\n')+'\r\n';

	vcard_out_grouped=new Array();
	while((parsed=vcardString.match(vCard.pre['contentline_parse']))!=null)
	{
		additional_related='';

		// parsed = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
		params_array=vcardSplitParam(parsed[3]);
		// we transform the old X-* IM attributes to new IMPP (internally used by editor)
		switch(parsed[2])
		{
			case 'X-ABDATE':	// stupid Apple specific DATE fields (by default hidden in interface)
				// here we try to extract the Anniversary from X-ABDATE and transform it to X-ANNIVERSARY
				// find the corresponding group data (if exists)
				if(parsed[1]!='')
				{
					re=parsed[1].replace('.','\\.X-ABLabel:_\\$!<Anniversary>!\\$_')+'\r\n';
					if((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
					{
						// remove the processed parameter
						vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						attr_name='X-ANNIVERSARY';
						params_swc=';VALUE=date';
						attr_value=parsed[4].substring(0, 10);	// sorry, we support only date (no date-time support)
					}
				}
				else
				{
					attr_name=parsed[2];
					params_swc=params_array.sort().join(';');
					attr_value=parsed[4];
				}
				break;
			case 'X-EVOLUTION-ANNIVERSARY':
			case 'X-ANNIVERSARY':
				attr_name='X-ANNIVERSARY';
				params_swc=';VALUE=date';
				var tmp=parsed[4].match(RegExp('^([0-9]{4})-?([0-9]{2})-?([0-9]{2})(.*)','i'));
				attr_value=tmp[1]+'-'+tmp[2]+'-'+tmp[3];	// sorry, we support only date (no date-time support)
				break;
			case 'BDAY':
				attr_name='BDAY';
				params_swc=';VALUE=date';
				var tmp=parsed[4].match(RegExp('^([0-9]{4})-?([0-9]{2})-?([0-9]{2})(.*)','i'));
				attr_value=tmp[1]+'-'+tmp[2]+'-'+tmp[3];	// sorry, we support only date (no date-time support)
				break;
			case 'X-AIM':
				attr_name='IMPP';
				if(params_array.length==0)
					params_array[0]='';	// after the join it generates ';' after the attribute name
				params_array[params_array.length]='X-SERVICE-TYPE=AIM';
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];

				// check for IMPP attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_IMPP']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4].replace(RegExp('^[^:]+:'),'')==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-JABBER':
				attr_name='IMPP';
				if(params_array.length==0)
					params_array[0]='';	// after the join it generates ';' after the attribute name
				params_array[params_array.length]='X-SERVICE-TYPE=Jabber';
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];

				// check for IMPP attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_IMPP']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4].replace(RegExp('^[^:]+:'),'')==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-MSN':
				attr_name='IMPP';
				if(params_array.length==0)
					params_array[0]='';	// after the join it generates ';' after the attribute name
				params_array[params_array.length]='X-SERVICE-TYPE=MSN';
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];

				// check for IMPP attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_IMPP']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4].replace(RegExp('^[^:]+:'),'')==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-YAHOO':
				attr_name='IMPP';
				if(params_array.length==0)
					params_array[0]='';	// after the join it generates ';' after the attribute name
				params_array[params_array.length]='X-SERVICE-TYPE=Yahoo';
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];

				// check for IMPP attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_IMPP']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4].replace(RegExp('^[^:]+:'),'')==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-YAHOO-ID':
				attr_name='IMPP';
				if(params_array.length==0)
					params_array[0]='';	// after the join it generates ';' after the attribute name
				params_array[params_array.length]='X-SERVICE-TYPE=Yahoo';
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];

				// check for IMPP attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_IMPP']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4].replace(RegExp('^[^:]+:'),'')==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-ICQ':
				attr_name='IMPP';
				if(params_array.length==0)
					params_array[0]='';	// after the join it generates ';' after the attribute name
				params_array[params_array.length]='X-SERVICE-TYPE=ICQ';
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];

				// check for IMPP attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_IMPP']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4].replace(RegExp('^[^:]+:'),'')==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'IMPP':
				attr_name=parsed[2];
				params_swc=params_array.sort().join(';');

				// remove the apple specific '*:' from the '*:value'
				//  but we add them back during the vcard generation from the interface
				attr_value=parsed[4].replace(RegExp('^[^\\\\]*?:'),'');
				break;
			case 'X-ASSISTANT':
			case 'X-EVOLUTION-ASSISTANT':
				attr_name='X-ABRELATEDNAMES';
				params_swc='';
				attr_value=parsed[4];
				additional_related='X-ABLabel:_$!<Assistant>!$_\r\n';

				// check for X-ABRELATEDNAMES attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_X-ABRELATEDNAMES']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4]==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-MANAGER':
			case 'X-EVOLUTION-MANAGER':
				attr_name='X-ABRELATEDNAMES';
				params_swc='';
				attr_value=parsed[4];
				additional_related='X-ABLabel:_$!<Manager>!$_\r\n';

				// check for X-ABRELATEDNAMES attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_X-ABRELATEDNAMES']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4]==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			case 'X-SPOUSE':
			case 'X-EVOLUTION-SPOUSE':
				attr_name='X-ABRELATEDNAMES';
				params_swc='';
				attr_value=parsed[4];
				additional_related='X-ABLabel:_$!<Spouse>!$_\r\n';

				// check for X-ABRELATEDNAMES attribute with the same value
				var found=false;
				var tmpVcardString=vcardString;
				while((tmp_vcard_element=tmpVcardString.match(vCard.pre['contentline_X-ABRELATEDNAMES']))!=null)
				{
					// parsed (contentline_parse) = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
					tmp_parsed_impp=tmp_vcard_element[0].match(vCard.pre['contentline_parse']);

					if(tmp_parsed_impp[4]==parsed[4])
					{
						found=true;
						break;
					}
					tmpVcardString=tmpVcardString.replace(tmp_vcard_element[0],'\r\n');
				}

				if(found==true)
				{
					// remove the processed element
					vcardString=vcardString.replace(parsed[0],'\r\n');
					// find the corresponding group data (if exists)
					if(parsed[1]!='')
					{
						re=parsed[1].replace('.','\\..*')+'\r\n';
						while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
						{
							// remove the processed parameter
							vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
						}
					}
					continue;
				}
				break;
			default:
				attr_name=parsed[2];
				params_swc=params_array.sort().join(';');
				attr_value=parsed[4];
				break;
		}
		// remove the processed element
		vcardString=vcardString.replace(parsed[0],'\r\n');
		if(attr_name!='FN' && attr_name!='N' && attr_value=='')	// attributes with empty values are not supported and are removed here
		{
			// find the corresponding group data (if exists)
			if(parsed[1]!='')
			{
				re=parsed[1].replace('.','\\.(.*)')+'\r\n';
				while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
					// remove the processed parameter
					vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
			}
			continue;
		}

		// add the new element to output array (without group)
		grouped_elem=new Array();
		grouped_elem[grouped_elem.length]=attr_name+params_swc+':'+attr_value+'\r\n';
		if(additional_related!='')	// used if we manually add related items as a part of transformation
			grouped_elem[grouped_elem.length]=additional_related;
		// find the corresponding group data (if exists)
		if(parsed[1]!='')
		{
			re=parsed[1].replace('.','\\.(.*)')+'\r\n';
			while((vcard_element_related=vcardString.match(RegExp('\r\n'+re,'m')))!=null)
			{
				// add the related element to array
				grouped_elem[grouped_elem.length]=vcard_element_related[1]+'\r\n';
				// remove the processed parameter
				vcardString=vcardString.replace(vcard_element_related[0],'\r\n');
			}
		}
		// add the new grouped element to output
		vcard_out_grouped[vcard_out_grouped.length]=grouped_elem.sort().join('');
	}

	// after the transformation and grouping we remove the identical
	//  elements (for example X-AIM and IMPP;X-SERVICE-TYPE=AIM)
	vcard_out_grouped=vcard_out_grouped.sort().unique();

	// add new group names ...
	elemCounter=0;
	for(i=0;i<vcard_out_grouped.length;i++)
		if(vcard_out_grouped[i].match(RegExp('\r\n','mg')).length>1)
			vcard_out_grouped[i]=(('\r\n'+vcard_out_grouped[i].substring(0, vcard_out_grouped[i].length-2)).replace(RegExp('\r\n','mg'),'\r\nitem'+(elemCounter++)+'.')+'\r\n').substring(2);

	vcard_out_grouped.unshift(vcard_begin);
	vcard_out_grouped.push(vcard_end);

	return vcard_out_grouped.join('');
}
