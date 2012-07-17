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

function processEditorElements(processingType, inputIsReadonly)
{
	var cssShowAsTxtClass='element_show_as_text';
	var cssGrayedTxt='element_grayed';
	var cssElementNoDisplay='element_no_display';
	var cssElementHide='element_hide';

	if(processingType=='hide')
	{
		$('[id=vcard_editor]').attr('data-editor-state','show');
		disabled=true;
		readonly=true;
	}
	else
	{
		$('[id=vcard_editor]').attr('data-editor-state','edit');
		disabled=false;
		readonly=false;
	}

	// company checkbox and text
	var tmp=$('[id="vcard_editor"]').find('[data-type="isorg"]');
	tmp.prop('disabled',disabled);
	if(processingType=='hide' && !tmp.prop('checked'))
		tmp.parent().addClass(cssGrayedTxt);
	else
		tmp.parent().removeClass(cssGrayedTxt);

	$('[data-type^="date_"]').prop('disabled', disabled || readonly);

	// family name, given name, and organization name
	var typeList = new Array('family','given','middle','nickname','prefix','suffix','date_bday','date_anniversary','tags','title','department','org');
	for(i=0;i<typeList.length;i++)
		$('[id=vcard_editor]').find('[data-type="'+typeList[i]+'"]').prop('readonly',readonly);

	var tmp=$('[id=vcard_editor]').find('#tags_tag');
	tmp.prop('readonly',readonly);
	if(readonly)
		tmp.closest('div.tagsinput').addClass('readonly');
	else
		tmp.closest('div.tagsinput').removeClass('readonly');

	// set the visibility of the buttons
	var tmp=$('[id=vcard_editor]');
	if(processingType=='hide')
	{
		if(inputIsReadonly!=true)
			tmp.find('[data-type="edit"]').removeClass(cssElementNoDisplay);
		else
			tmp.find('[data-type="edit"]').addClass(cssElementNoDisplay);

		tmp.find('[data-type="save"]').addClass(cssElementNoDisplay);
		tmp.find('[data-type="cancel"]').addClass(cssElementNoDisplay);
		tmp.find('[data-type="delete_from_group"]').addClass(cssElementNoDisplay);
		tmp.find('[data-type="delete"]').addClass(cssElementNoDisplay);
	}
	else if(processingType=='add')
	{
		tmp.find('[data-type="edit"]').addClass(cssElementNoDisplay);
		tmp.find('[data-type="cancel"]').removeClass(cssElementNoDisplay);
		tmp.find('[data-type="delete_from_group"]').addClass(cssElementNoDisplay);
		tmp.find('[data-type="delete"]').addClass(cssElementNoDisplay);
	}
	else
	{
		tmp.find('[data-type="edit"]').addClass(cssElementNoDisplay);
		tmp.find('[data-type="save"]').removeClass(cssElementNoDisplay);
		tmp.find('[data-type="cancel"]').removeClass(cssElementNoDisplay);
		// show "Delete from Group" only if there is an active contact group
		if(globalResourceList.getLoadedAddressbook().filterUID!='')
			tmp.find('[data-type="delete_from_group"]').removeClass(cssElementNoDisplay);
		tmp.find('[data-type="delete"]').removeClass(cssElementNoDisplay);
	}


	var typeList = new Array('\\%address','\\%phone','\\%email','\\%url','\\%person','\\%im','\\%categories','\\%note');
	for(i=0;i<typeList.length;i++)
	{
		found_non_empty=0;

		tmp=$('[id=vcard_editor]').find('[data-type="'+typeList[i]+'"]');
		tmp.each(
			function(index,element)
			{
				var tmp=$(element).find('[data-type="value"]');

				var found=0;
				// check if there is any data present (if not, whe hide the element)
				if($(element).attr('data-type')=='%address')	// address is handled specially
					tmp.each(
						function(index,element)
						{
							if($(element).attr('data-addr-field')!='' && $(element).attr('data-addr-field')!='country' && $(element).val()!='')
							{
								found=1;
								return false;
							}
						}
					);
				else if(tmp.val()!='')	// other elements (not address)
					found=1;


				if(processingType=='hide')
				{
					if(!found)
					{
						$(element).addClass(cssElementNoDisplay);
					}
					else
					{
						$(element).find('[data-type="\\%add"]').find('input[type="image"]').addClass(cssElementNoDisplay);
						$(element).find('[data-type="\\%del"]').find('input[type="image"]').addClass(cssElementNoDisplay);
						$(element).find('select').prop('disabled',disabled);
						$(element).find('textarea').prop('disabled',disabled);
						tmp.prop('readonly',readonly);
						found_non_empty=1;
					}
					
				}	
				else	// 'show'
				{
					$(element).removeClass(cssElementNoDisplay);
					$(element).find('[data-type="\\%add"]').find('input[type="image"]').removeClass(cssElementNoDisplay);
					$(element).find('[data-type="\\%del"]').find('input[type="image"]').removeClass(cssElementNoDisplay);
					$(element).find('select').prop('disabled',disabled);
					$(element).find('textarea').prop('disabled',disabled);
					tmp.prop('readonly',readonly);
				}
			}
		);

		if(!found_non_empty)
		{
			if(processingType=='hide')
				tmp.prev().addClass(cssElementNoDisplay);
			else
				tmp.prev().removeClass(cssElementNoDisplay);
		}
	}
}

function editor_cleanup(inputLoadEmpty)
{
	cleanupRegexEnvironment();

	// Cleanup the editor and select the default country
	$('#ABContact').html(cleanVcardTemplate);

	var tmp=$('[id="vcard_editor"] [data-type="\\%address"]');
	var tmp_select=tmp.find('[data-type="\\%country"]').attr('data-autoselect');
	if(tmp_select!='')
	{
		tmp.find('[data-type="\\%country"]').children('[data-type="'+jqueryEscapeSelector(tmp_select)+'"]').prop('selected',true);
		tmp.find('[data-autoselect]').change();
	}

	$('#tags').tagsInput({
		'height': null,
		'width': '448px',
		'placeholderColor': '#e0e0e0',
		'useNativePlaceholder': true,
		'defaultText': localization[globalInterfaceLanguage].addCategory,
		'delimiter': ',',
		'allowDelimiterInValue': true,	// if true delimiter is escaped with '\' ('\' is escaped as '\\')
		'trimInput': false,
		'autocomplete_url': globalAddressbookList.getABCategories(),
		'autocomplete': {
			'autoFocus': true,
			'minLength': 0
		},
		'onAddTag': function(value)
		{
			// copy the array
			var xList=globalAddressbookList.getABCategories();
			var currentTags=$(this).val().splitCustom(',');
			for(var i=xList.length-1; i>=0; i--)
			{
				for(var j=0; j<currentTags.length; j++)
					if(xList[i] == currentTags[j])
						xList.splice(i, 1);
			}
			$('#tags_tag').autocomplete('option', 'source', xList);
		},
		'onRemoveTag': function()
		{
			// copy the array
			var xList=globalAddressbookList.getABCategories();
			var currentTags=$(this).val().splitCustom(',');
			for(var i=xList.length-1; i>=0; i--)
			{
				for(var j=0; j<currentTags.length; j++)
					if(xList[i] == currentTags[j])
						xList.splice(i, 1);
			}
			$('#tags_tag').autocomplete('option', 'source', xList);
		}
	});

	$('[data-type="org"]').autocomplete({'source': function(request, response){var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), 'i'); response($.grep(globalAddressbookList.getABCompanies(), function(value){ value = value.label || value.value || value; return matcher.test(value) || matcher.test(value.multiReplace(globalSearchTransformAlphabet));}));}, 'minLength': 0, 'change': function(){$('[data-type="department"]').autocomplete({'source': function(request, response){ var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), 'i'); response($.grep(globalAddressbookList.getABCompanyDepartments($('[id="vcard_editor"] [data-type="org"]').val()), function(value){ value = value.label || value.value || value; return matcher.test(value) || matcher.test(value.multiReplace(globalSearchTransformAlphabet));}));}, 'minLength': 0})}});

	// CUSTOM PLACEHOLDER (initialization for the editor)
	$('#ABContact').find('input[placeholder],textarea[placeholder]').placeholder();

	if(inputLoadEmpty==true)
		$('#EditorBox').fadeTo(100,1);
}

function animate_message(messageSelector,messageTextSelector,duration,operation)
{
	if(operation==undefined)
		operation='+=';
	var height=$(messageTextSelector).height()+14;
	var animation=500;

	$(messageSelector).animate({'max-height': height+'px', height: (operation==undefined ? '+=' : operation)+height+'px'}, animation,
		function()
		{
			if(operation=='+=')
				setTimeout(function(){animate_message(messageSelector,messageTextSelector,0,'-=');},duration);
		}
	);
	return duration+2*animation;
}

function show_editor_message(inputPosition,inputSetClass,inputMessage,inputDuration)
{
	if(inputPosition==undefined || inputPosition=='in')
	{
		messageSelector='#ABInMessage';
		messageTextSelector='#ABInMessageText';
	}
	else
	{
		messageSelector='#ABMessage';
		messageTextSelector='#ABMessageText';
	}

	$(messageTextSelector).attr('class',inputSetClass);
	$(messageTextSelector).text(inputMessage);
	return animate_message(messageSelector,messageTextSelector,inputDuration);
}

function set_address_country(inputSelectedAddressObj)
{
	var selectedCountry=$(inputSelectedAddressObj).find('option').filter(':selected').attr('data-type');
	var addressElement=$(inputSelectedAddressObj).closest('[data-type="\\%address"]');

	// cleanup the data-addr-fields and placeholders
	addressElement.find('[data-addr-fid]').each(
		function(index,element)
		{
			$(element).find('input').attr({'data-addr-field': '', 'placeholder': ''}).unplaceholder();	// REMOVE CUSTOM PLACEHOLDER
		}
	);

	if(addressTypes[selectedCountry]!=undefined)
	{
		for(var i=0;i<addressTypes[selectedCountry].length;i++)
		{
			if(addressTypes[selectedCountry][i]['type']=='input')
			{
				var tmp=addressElement.find('[data-addr-fid="'+jqueryEscapeSelector(addressTypes[selectedCountry][i]['fid'])+'"]').find('input');
				tmp.attr('data-addr-field',addressTypes[selectedCountry][i]['data-addr-field']);
				tmp.attr('placeholder',addressTypes[selectedCountry][i]['placeholder']);
			}
			else if(addressTypes[selectedCountry][i]['type']=='country')
			{
				var tmp=addressElement.find('[data-type="\\%country"]');
				tmp.find('option[data-type]').prop('selected',false);
				tmp.find('option[data-type="'+jqueryEscapeSelector(selectedCountry)+'"]').prop('selected',true);

				// the country selector is in wrong container -> we need to move it
				if(addressTypes[selectedCountry][i]['fid']!=tmp.closest('[data-addr-fid]').attr('data-addr-fid'))
					$(addressElement).find('[data-addr-fid="'+jqueryEscapeSelector(addressTypes[selectedCountry][i]['fid'])+'"]').append(tmp); 
			}
		}
	}

	// hide the unused fields by changing the CSS
	addressElement.find('[data-type="container"]').each(
		function(index,element)
		{
			var found=0;
			$(element).find('[data-addr-field]').each(
				function(index,element)
				{
					if($(element).attr('data-addr-field')!='')
					{
						found=1;
						return false;
					}
				}
			);

			if(found)
				$(element).removeClass('element_no_display_af');
			else
				$(element).addClass('element_no_display_af');
		}
	);

	// CUSTOM PLACEHOLDER (reinitialization due to possible placeholder value change)
	addressElement.find('input[data-type="value"][placeholder],textarea[data-type="value"][placeholder]').placeholder();
}

function add_element(inputElementID, inputParentSelector, newElementSelector, inputAddClassSelector, inputDelClassSelector, maxElements, newElementID) // note: newElementSelector is always used with .last()
{
	// allow only maxElements items for this attribute 
	if((count=inputElementID.closest(inputParentSelector).parent().children(inputParentSelector).length) < maxElements)
	{
		newElement=$(newElementSelector).last().clone().wrap('<div>');

		// CUSTOM PLACEHOLDER
		// remove the "placeholder" data (custom placeholder label for IE)
		newElement.find('label').remove();
		newElement.find('[data-type="value"]').removeAttr('id','').removeClass('placeholder-input');

		// unselect each selected element
		newElement.find('option').prop('selected',false);
		// remove the form values
		newElement.find('[data-type="value"]').val('');
		// add the data-id value
		newElement.find(inputParentSelector).attr("data-id",newElementID);
		// disable the "add" on last element if maximum count is reached
		if(count==maxElements-1)
			newElement.find(inputAddClassSelector).css('visibility','hidden');

		newElement=newElement.closest(inputParentSelector).parent().html();

		// disable the "add" button on the current element
		inputElementID.filter(inputAddClassSelector).css('visibility','hidden');
		// add the new element (with enabled "add" button)
		inputElementID.parent().after(newElement);

		// CUSTOM PLACEHOLDER
		// enable custom placeholder support (it is enable only if needed)
		$(newElementSelector).last().find('input[data-type="value"][placeholder],textarea[data-type="value"][placeholder]').placeholder();

		// enable the "del" button on all elements
		$(inputParentSelector).find(inputDelClassSelector).css('visibility','');

		if(inputParentSelector=='[data-type="\\%address"]')
		{
			// execute the "autoselect"
			var tmp=inputElementID.closest(inputParentSelector).next();
			var tmp_select=tmp.find('[data-autoselect]').attr('data-autoselect');
			if(tmp_select!=null)
			{
				tmp.find('[data-type="\\%country"]').children('[data-type="'+jqueryEscapeSelector(tmp_select)+'"]').prop('selected',true);
				tmp.find('[data-autoselect]').change();
			}
		}
		return true;
	}
	else
		return false;
}

function del_element(inputElementID, inputParentSelector, inputAddClassSelector, inputDelClassSelector)
{
	// all elements except the last can be removed
	if(inputElementID.closest(inputParentSelector).siblings(inputParentSelector).length > 0)
	{
		inputElementID.closest(inputParentSelector).remove();
		// enable the "add" button on last element
		$(inputParentSelector).last().find(inputAddClassSelector).css('visibility','');
		// disable the "del" button if only one element is present
		if($(inputParentSelector).length==1)
			$(inputParentSelector).last().find(inputDelClassSelector).css('visibility','hidden');
	}
}

var globalCounter = new Object();
globalCounter['phoneID']=1;
globalCounter['emailID']=1;
globalCounter['urlID']=1;
globalCounter['personID']=1;
globalCounter['imID']=1;
globalCounter['addressID']=1;

$(document).on('keyup change', '[data-type^="date_"]', function(){
	if(!$(this).prop('readonly') && !$(this).prop('disabled'))
	{
		var valid=true;

		if($(this).val()!='')
		{
			try {$.datepicker.parseDate(globalDatepickerFormat, $(this).val())}
			catch (e) {valid=false}
		}

		if(valid)
			$(this).parent().find('img').css('display','none');
		else
			$(this).parent().find('img').css('display','inline');
	}
});

// Timepicker hack (prevent IE to re-open the datepicker on date click + focus)
var globalTmpTimePickerHackTime=new Object();

$(document).on('focus', '[data-type^="date_"]', function(){if(!$(this).hasClass('hasDatepicker')){$(this).datepicker({disabled: $(this).prop('readonly') || $(this).prop('disabled'), showMonthAfterYear: true, prevText: '', nextText: '', monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'], dateFormat: globalDatepickerFormat, defaultDate: '-'+Math.round(30*365.25-1), minDate: '-120y', maxDate: '+0', yearRange: 'c-120:+0', firstDay: 0, changeMonth: true, changeYear: true, showAnim: '',
	beforeShow: function(input, inst)	// set the datepicker value if the date is out of range (min/max)
	{
		var valid=true;
		try {var currentDate=$.datepicker.parseDate(globalDatepickerFormat, $(this).val())}
		catch (e) {valid=false}

		if(valid==true)
		{
			var minDateText=$(this).datepicker('option', 'dateFormat', globalDatepickerFormat).datepicker('option', 'minDate');
			var maxDateText=$(this).datepicker('option', 'dateFormat', globalDatepickerFormat).datepicker('option', 'maxDate');

			var minDate=$.datepicker.parseDate(globalDatepickerFormat, minDateText);
			var maxDate=$.datepicker.parseDate(globalDatepickerFormat, maxDateText);

			if(currentDate<minDate)
				$(this).val(minDateText);
			else if(currentDate>maxDate)
				$(this).val(maxDateText);
		}

		// Timepicker hack (prevent IE to re-open the datepicker on date click + focus)
		var index=$(this).attr("data-type");
		var d = new Date();
		if(globalTmpTimePickerHackTime[index]!=undefined && d.getTime()-globalTmpTimePickerHackTime[index]<200)
			return false;
	},
	onClose: function(dateText, inst)	// set the datepicker value if the date is out of range (min/max) and reset the value to proper format (for example 'yy-mm-dd' allows '2000-1-1' -> we need to reset the value to '2000-01-01')
	{
		var valid=true;
		try {var currentDate=$.datepicker.parseDate(globalDatepickerFormat, dateText)}
		catch (e) {valid=false}

		if(valid==true)
		{
			var minDateText=$(this).datepicker('option', 'dateFormat', globalDatepickerFormat).datepicker('option', 'minDate');
			var maxDateText=$(this).datepicker('option', 'dateFormat', globalDatepickerFormat).datepicker('option', 'maxDate');

			var minDate=$.datepicker.parseDate(globalDatepickerFormat, minDateText);
			var maxDate=$.datepicker.parseDate(globalDatepickerFormat, maxDateText);

			if(currentDate<minDate)
				$(this).val(minDateText);
			else if(currentDate>maxDate)
				$(this).val(maxDateText);
			else
				$(this).val($.datepicker.formatDate(globalDatepickerFormat, currentDate));
		}

		// Timepicker hack (prevent IE to re-open the datepicker on date click + focus)
		var index=$(this).attr("data-type");
		var d = new Date();
		globalTmpTimePickerHackTime[index]=d.getTime();

		$(this).focus();
	}
});
$(this).mousedown(function(){
	if($(this).datepicker('widget').css('display')=='none')
		$(this).datepicker('show');
	else
		$(this).datepicker('hide');
});
$(this).blur(function(event){
	// handle onblur event because datepicker can be already closed
	// note: because onblur is called more than once we can handle it only if there is a value change!
	if($(this).val()!=$.datepicker.formatDate(globalDatepickerFormat, $.datepicker.parseDate(globalDatepickerFormat, $(this).val())))
	{
		var valid=true;
		try {var currentDate=$.datepicker.parseDate(globalDatepickerFormat, $(this).val())}
		catch (e) {valid=false}

		if(valid==true)
		{
			var minDateText=$(this).datepicker('option', 'dateFormat', globalDatepickerFormat).datepicker('option', 'minDate');
			var maxDateText=$(this).datepicker('option', 'dateFormat', globalDatepickerFormat).datepicker('option', 'maxDate');

			var minDate=$.datepicker.parseDate(globalDatepickerFormat, minDateText);
			var maxDate=$.datepicker.parseDate(globalDatepickerFormat, maxDateText);

			if(currentDate<minDate)
				$(this).val(minDateText);
			else if(currentDate>maxDate)
				$(this).val(maxDateText);
			else
				$(this).val($.datepicker.formatDate(globalDatepickerFormat, currentDate));
		}
	}
})
}});

if(typeof globalEnableKbNavigation=='undefined' || globalEnableKbNavigation!==false)
{
	$(document.documentElement).keyup(function (event)
	{
		if($('#System').css('display')!='none' && $('#ABListLoader').css('display')=='none' && $('#ABListOverlay').css('display')=='none' && !$('input[data-type="search"]').is(':focus'))
		{
			// 37 = left, 38 = up, 39 = right, 40 = down
			if((selected_contact=$('#ABList').find('.ablist_item_selected')).length==1)
			{
				if(event.keyCode == 38 && (next_contact=selected_contact.prevAll('.ablist_item').filter(':visible').first()).attr('data-id')!=undefined || event.keyCode == 40 && (next_contact=selected_contact.nextAll('.ablist_item').filter(':visible').first()).attr('data-id')!=undefined)
					globalAddressbookList.loadContactByUID(next_contact.attr('data-id'));
			}
		}
	});

	$(document.documentElement).keydown(function(event)
	{
		if($('#System').css('display')!='none' && $('#ABListLoader').css('display')=='none' && $('#ABListOverlay').css('display')=='none' && !$('input[data-type="search"]').is(':focus'))
		{
			// 37 = left, 38 = up, 39 = right, 40 = down
			if((selected_contact=$('#ABList').find('.ablist_item_selected')).length==1)
			{
				if(event.keyCode == 38 && (next_contact=selected_contact.prevAll('.ablist_item').filter(':visible').first()).attr('data-id')!=undefined || event.keyCode == 40 &&  (next_contact=selected_contact.nextAll('.ablist_item').filter(':visible').first()).attr('data-id')!=undefined)
				{
					switch(event.keyCode)
					{
						case 38:
							event.preventDefault();
							if(next_contact.offset().top<$('#ABList').height()*0.25)
							{
								var move=next_contact.offset().top-selected_contact.offset().top;
								if(next_contact.offset().top<0)
									$('#ABList').scrollTop($('#ABList').scrollTop()+next_contact.offset().top-$('#ABList').height()*0.25);
								else
									$('#ABList').scrollTop(Math.max(0,$('#ABList').scrollTop()+move));
							}
							else if(next_contact.offset().top>$('#ABList').height()*0.75)	/* contact invisible (scrollbar moved) */
								$('#ABList').scrollTop($('#ABList').scrollTop()+next_contact.offset().top-($('#ABList').height()*0.75));
							else
								return false;
							break;
						case 40:
							event.preventDefault();
							if(selected_contact.offset().top>$('#ABList').height()*0.75)
							{
								var move=next_contact.offset().top-selected_contact.offset().top;
								if($('#ABList').scrollTop()+$('#ABList').height()*0.75<selected_contact.offset().top)
									$('#ABList').scrollTop(selected_contact.offset().top-$('#ABList').height()*0.75);
								else
									$('#ABList').scrollTop(Math.min($('#ABList').prop('scrollHeight'),$('#ABList').scrollTop()+move));
							}
							else if(selected_contact.offset().top<$('#ABList').height()*0.25)	/* contact invisible (scrollbar moved) */
								$('#ABList').scrollTop($('#ABList').scrollTop()+selected_contact.offset().top-($('#ABList').height()*0.25));
							else
								return false;
							break;
						default:
							break;
					}
				}
				else	// no previous contact and up pressed || no next contact and down pressed
				{

					switch(event.keyCode)
					{
						case 38:
							$('#ABList').scrollTop(0);
							break;
						case 40:
							$('#ABList').scrollTop($('#ABList').prop('scrollHeight'));
							break;
						default:
							break;
					}
				}
			}
		}
	});
}

phoneMax=20;
$(document).on('click', '[data-type="\\%phone"] [data-type="\\%add"] input', function(ignoreMaxElements){add_element($(this).parent(),'[data-type="\\%phone"]','[data-type="\\%phone"]','[data-type="\\%add"]','[data-type="\\%del"]',phoneMax,globalCounter['phoneID']++)});
$(document).on('click', '[data-type="\\%phone"] [data-type="\\%del"] input', function(){del_element($(this).parent(),'[data-type="\\%phone"]','[data-type="\\%add"]','[data-type="\\%del"]')});
//$('[data-type="\\%phone"]').children().filter('[data-type="\\%add"]').click();

emailMax=20;
$(document).on('click', '[data-type="\\%email"] [data-type="\\%add"] input', function(ignoreMaxElements){add_element($(this).parent(),'[data-type="\\%email"]','[data-type="\\%email"]','[data-type="\\%add"]','[data-type="\\%del"]',emailMax,globalCounter['emailID']++)});
$(document).on('click', '[data-type="\\%email"] [data-type="\\%del"] input', function(){del_element($(this).parent(),'[data-type="\\%email"]','[data-type="\\%add"]','[data-type="\\%del"]')});
//$('[data-type="\\%email"]').children().filter('[data-type="\\%add"]').click();

urlMax=20;
$(document).on('click', '[data-type="\\%url"] [data-type="\\%add"] input', function(ignoreMaxElements){add_element($(this).parent(),'[data-type="\\%url"]','[data-type="\\%url"]','[data-type="\\%add"]','[data-type="\\%del"]',urlMax,globalCounter['urlID']++)});
$(document).on('click', '[data-type="\\%url"] [data-type="\\%del"] input', function(){del_element($(this).parent(),'[data-type="\\%url"]','[data-type="\\%add"]','[data-type="\\%del"]')});
//$('[data-type="\\%url"]').children().filter('[data-type="\\%add"]').click();

personMax=20;
$(document).on('click', '[data-type="\\%person"] [data-type="\\%add"] input', function(ignoreMaxElements){add_element($(this).parent(),'[data-type="\\%person"]','[data-type="\\%person"]','[data-type="\\%add"]','[data-type="\\%del"]',personMax,globalCounter['personID']++)});
$(document).on('click', '[data-type="\\%person"] [data-type="\\%del"] input', function(){del_element($(this).parent(),'[data-type="\\%person"]','[data-type="\\%add"]','[data-type="\\%del"]')});
//$('[data-type="\\%person"]').children().filter('[data-type="\\%add"]').click();

imMax=20;
$(document).on('click', '[data-type="\\%im"] [data-type="\\%add"] input', function(ignoreMaxElements){add_element($(this).parent(),'[data-type="\\%im"]','[data-type="\\%im"]','[data-type="\\%add"]','[data-type="\\%del"]',imMax,globalCounter['imID']++)});
$(document).on('click', '[data-type="\\%im"] [data-type="\\%del"] input', function(){del_element($(this).parent(),'[data-type="\\%im"]','[data-type="\\%add"]','[data-type="\\%del"]')});
//$('[data-type="\\%im"]').children().filter('[data-type="\\%add"]').click();

addrMax=20;
$(document).on('click', '[data-type="\\%address"] [data-type="\\%add"] input', function(ignoreMaxElements){add_element($(this).parent(),'[data-type="\\%address"]','[data-type="\\%address"]','[data-type="\\%add"]','[data-type="\\%del"]',imMax,globalCounter['addressID']++)});
$(document).on('click', '[data-type="\\%address"] [data-type="\\%del"] input', function(){del_element($(this).parent(),'[data-type="\\%address"]','[data-type="\\%add"]','[data-type="\\%del"]')});
//$('[data-type="\\%address"]').children().filter('[data-type="\\%add"]').click();
$(document).on('change', '[data-type="\\%address"] [data-type="\\%country"]', function(){set_address_country(this); $(this).parent().find('[data-type="\\%country"]').focus();});
