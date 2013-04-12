/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

Calendar={
	Util:{
		dateTimeToTimestamp:function(dateString, timeString){
			dateTuple = dateString.split('-');
			timeTuple = timeString.split(':');
			
			var day, month, year, minute, hour;
			day = parseInt(dateTuple[0], 10);
			month = parseInt(dateTuple[1], 10);
			year = parseInt(dateTuple[2], 10);
			hour = parseInt(timeTuple[0], 10);
			minute = parseInt(timeTuple[1], 10);
			
			var date = new Date(year, month-1, day, hour, minute);
			
			return parseInt(date.getTime(), 10);
		},
		formatDate:function(year, month, day){
			if(day < 10){
				day = '0' + day;
			}
			if(month < 10){
				month = '0' + month;
			}
			return day + '-' + month + '-' + year;
		},
		formatTime:function(hour, minute){
			if(hour < 10){
				hour = '0' + hour;
			}
			if(minute < 10){
				minute = '0' + minute;
			}
			return hour + ':' + minute;
		}, 
		adjustDate:function(){
			var fromTime = $('#fromtime').val();
			var fromDate = $('#from').val();
			var fromTimestamp = Calendar.Util.dateTimeToTimestamp(fromDate, fromTime);

			var toTime = $('#totime').val();
			var toDate = $('#to').val();
			var toTimestamp = Calendar.Util.dateTimeToTimestamp(toDate, toTime);

			if(fromTimestamp >= toTimestamp){
				fromTimestamp += 30*60*1000;
				
				var date = new Date(fromTimestamp);
				movedTime = Calendar.Util.formatTime(date.getHours(), date.getMinutes());
				movedDate = Calendar.Util.formatDate(date.getFullYear(),
						date.getMonth()+1, date.getDate());

				$('#to').val(movedDate);
				$('#totime').val(movedTime);
			}
		}
	},
	UI:{
		scrollcount: 0,
		loading: function(isLoading){
			if (isLoading){
				$('#loading').show();
			}else{
				$('#loading').hide();
			}
		},
		startEventDialog:function(){
			Calendar.UI.loading(false);
			$('.tipsy').remove();
			$('#fullcalendar').fullCalendar('unselect');
			Calendar.UI.lockTime();
			$( "#from" ).datepicker({
				dateFormat : 'dd-mm-yy',
				onSelect: function(){ Calendar.Util.adjustDate(); }
			});
			$( "#to" ).datepicker({
				dateFormat : 'dd-mm-yy'
			});
			$('#fromtime').timepicker({
				showPeriodLabels: false,
				onSelect: function(){ Calendar.Util.adjustDate(); }
			});
			$('#totime').timepicker({
				showPeriodLabels: false
			});
			$('#category').multiple_autocomplete({source: categories});
			Calendar.UI.repeat('init');
			$('#end').change(function(){
				Calendar.UI.repeat('end');
			});
			$('#repeat').change(function(){
				Calendar.UI.repeat('repeat');
			});
			$('#advanced_year').change(function(){
				Calendar.UI.repeat('year');
			});
			$('#advanced_month').change(function(){
				Calendar.UI.repeat('month');
			});
			$( "#event" ).tabs({ selected: 0});
			$('#event').dialog({
				width : 500,
				height: 600,
				close : function(event, ui) {
					$(this).dialog('destroy').remove();
				}
			});
			Calendar.UI.Share.init();
		},
		newEvent:function(start, end, allday){
			start = Math.round(start.getTime()/1000);
			if (end){
				end = Math.round(end.getTime()/1000);
			}
			if($('#event').dialog('isOpen') == true){
				// TODO: save event
				$('#event').dialog('destroy').remove();
			}else{
				Calendar.UI.loading(true);
				$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'new.form.php'), {start:start, end:end, allday:allday?1:0}, Calendar.UI.startEventDialog);
			}
		},
		editEvent:function(calEvent, jsEvent, view){
			if (calEvent.editable == false || calEvent.source.editable == false) {
				return;
			}
			var id = calEvent.id;
			if($('#event').dialog('isOpen') == true){
				// TODO: save event
				$('#event').dialog('destroy').remove();
			}else{
				Calendar.UI.loading(true);
				$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'edit.form.php'), {id: id}, Calendar.UI.startEventDialog);
			}
		},
		submitDeleteEventForm:function(url){
			var id = $('input[name="id"]').val();
			$('#errorbox').empty();
			Calendar.UI.loading(true);
			$.post(url, {id:id}, function(data){
					Calendar.UI.loading(false);
					if(data.status == 'success'){
						$('#fullcalendar').fullCalendar('removeEvents', $('#event_form input[name=id]').val());
						$('#event').dialog('destroy').remove();
					} else {
						$('#errorbox').html(t('calendar', 'Deletion failed'));
					}

			}, "json");
		},
		validateEventForm:function(url){
			var post = $( "#event_form" ).serialize();
			$("#errorbox").empty();
			Calendar.UI.loading(true);
			$.post(url, post,
				function(data){
					Calendar.UI.loading(false);
					if(data.status == "error"){
						var output = missing_field + ": <br />";
						if(data.title == "true"){
							output = output + missing_field_title + "<br />";
						}
						if(data.cal == "true"){
							output = output + missing_field_calendar + "<br />";
						}
						if(data.from == "true"){
							output = output + missing_field_fromdate + "<br />";
						}
						if(data.fromtime == "true"){
							output = output + missing_field_fromtime + "<br />";
						}
						if(data.to == "true"){
							output = output + missing_field_todate + "<br />";
						}
						if(data.totime == "true"){
							output = output + missing_field_totime + "<br />";
						}
						if(data.endbeforestart == "true"){
							output = output + missing_field_startsbeforeends + "!<br/>";
						}
						if(data.dberror == "true"){
							output = "There was a database fail!";
						}
						$("#errorbox").html(output);
					} else
					if(data.status == 'success'){
						$('#event').dialog('destroy').remove();
						$('#fullcalendar').fullCalendar('refetchEvents');
					}
				},"json");
		},
		moveEvent:function(event, dayDelta, minuteDelta, allDay, revertFunc){
			$('.tipsy').remove();
			Calendar.UI.loading(true);
			$.post(OC.filePath('calendar', 'ajax/event', 'move.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, allDay: allDay?1:0, lastmodified: event.lastmodified},
			function(data) {
				Calendar.UI.loading(false);
				if (data.status == 'success'){
					event.lastmodified = data.lastmodified;
					console.log("Event moved successfully");
				}else{
					revertFunc();
					$('#fullcalendar').fullCalendar('refetchEvents');
				}
			});
		},
		resizeEvent:function(event, dayDelta, minuteDelta, revertFunc){
			$('.tipsy').remove();
			Calendar.UI.loading(true);
			$.post(OC.filePath('calendar', 'ajax/event', 'resize.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, lastmodified: event.lastmodified},
			function(data) {
				Calendar.UI.loading(false);
				if (data.status == 'success'){
					event.lastmodified = data.lastmodified;
					console.log("Event resized successfully");
				}else{
					revertFunc();
					$('#fullcalendar').fullCalendar('refetchEvents');
				}
			});
		},
		showadvancedoptions:function(){
			$("#advanced_options").slideDown('slow');
			$("#advanced_options_button").css("display", "none");
		},
		showadvancedoptionsforrepeating:function(){
			if($("#advanced_options_repeating").is(":hidden")){
				$('#advanced_options_repeating').slideDown('slow');
			}else{
				$('#advanced_options_repeating').slideUp('slow');
			}
		},
		getEventPopupText:function(event){
			if (event.allDay){
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy]{ - [ddd d] MMMM yyyy}', {monthNamesShort: monthNamesShort, monthNames: monthNames, dayNames: dayNames, dayNamesShort: dayNamesShort}); //t('calendar', "ddd d MMMM[ yyyy]{ - [ddd d] MMMM yyyy}")
			}else{
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy] ' + defaulttime + '{ - [ ddd d MMMM yyyy]' + defaulttime + '}', {monthNamesShort: monthNamesShort, monthNames: monthNames, dayNames: dayNames, dayNamesShort: dayNamesShort}); //t('calendar', "ddd d MMMM[ yyyy] HH:mm{ - [ ddd d MMMM yyyy] HH:mm}")
				// Tue 18 October 2011 08:00 - 16:00
			}
			var html =
				'<div class="summary">' + escapeHTML(event.title) + '</div>' +
				'<div class="timespan">' + timespan + '</div>';
			if (event.description){
				html += '<div class="description">' + escapeHTML(event.description) + '</div>';
			}
			return html;
		},
		lockTime:function(){
			if($('#allday_checkbox').is(':checked')) {
				$("#fromtime").attr('disabled', true)
					.addClass('disabled');
				$("#totime").attr('disabled', true)
					.addClass('disabled');
			} else {
				$("#fromtime").attr('disabled', false)
					.removeClass('disabled');
				$("#totime").attr('disabled', false)
					.removeClass('disabled');
			}
		},
		showCalDAVUrl:function(username, calname){
			$('#caldav_url').val(totalurl + '/' + username + '/' + calname);
			$('#caldav_url').show();
			$("#caldav_url_close").show();
		},
		initScroll:function(){
			if(window.addEventListener)
				document.addEventListener('DOMMouseScroll', Calendar.UI.scrollCalendar, false);
			//}else{
				document.onmousewheel = Calendar.UI.scrollCalendar;
			//}
		},
		scrollCalendar:function(event){
			$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);
			$('.tipsy').remove();
			var direction;
			if(event.detail){
				if(event.detail < 0){
					direction = 'top';
				}else{
					direction = 'down';
				}
			}
			if (event.wheelDelta){
				if(event.wheelDelta > 0){
					direction = 'top';
				}else{
					direction = 'down';
				}
			}
			Calendar.UI.scrollcount++;
			if(Calendar.UI.scrollcount < 5){
				return;
			}

			var scroll = $(document).scrollTop(),
				doc_height = $(document).height(),
				win_height = $(window).height();
			if(direction == 'down'/* && win_height == (doc_height - scroll)*/){
				$('#fullcalendar').fullCalendar('next');
				$(document).scrollTop(0);
				event.preventDefault();
			}else/* if (direction == 'top' && scroll == 0) */{
				$('#fullcalendar').fullCalendar('prev');
				$(document).scrollTop(win_height);
				event.preventDefault();
			}
			Calendar.UI.scrollcount = 0;
		},
		repeat:function(task){
			if(task=='init'){
				$('#byweekno').multiselect({
					header: false,
					noneSelectedText: $('#advanced_byweekno').attr('title'),
					selectedList: 2,
					minWidth:'auto'
				});
				$('#weeklyoptions').multiselect({
					header: false,
					noneSelectedText: $('#weeklyoptions').attr('title'),
					selectedList: 2,
					minWidth:'auto'
				});
				$('input[name="bydate"]').datepicker({
					dateFormat : 'dd-mm-yy'
				});
				$('#byyearday').multiselect({
					header: false,
					noneSelectedText: $('#byyearday').attr('title'),
					selectedList: 2,
					minWidth:'auto'
				});
				$('#bymonth').multiselect({
					header: false,
					noneSelectedText: $('#bymonth').attr('title'),
					selectedList: 2,
					minWidth:'auto'
				});
				$('#bymonthday').multiselect({
					header: false,
					noneSelectedText: $('#bymonthday').attr('title'),
					selectedList: 2,
					minWidth:'auto'
				});
				Calendar.UI.repeat('end');
				Calendar.UI.repeat('month');
				Calendar.UI.repeat('year');
				Calendar.UI.repeat('repeat');
			}
			if(task == 'end'){
				$('#byoccurrences').css('display', 'none');
				$('#bydate').css('display', 'none');
				if($('#end option:selected').val() == 'count'){
					$('#byoccurrences').css('display', 'block');
				}
				if($('#end option:selected').val() == 'date'){
					$('#bydate').css('display', 'block');
				}
			}
			if(task == 'repeat'){
				$('#advanced_month').css('display', 'none');
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_weekofmonth').css('display', 'none');
				$('#advanced_byyearday').css('display', 'none');
				$('#advanced_bymonth').css('display', 'none');
				$('#advanced_byweekno').css('display', 'none');
				$('#advanced_year').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				if($('#repeat option:selected').val() == 'monthly'){
					$('#advanced_month').css('display', 'block');
					Calendar.UI.repeat('month');
				}
				if($('#repeat option:selected').val() == 'weekly'){
					$('#advanced_weekday').css('display', 'block');
				}
				if($('#repeat option:selected').val() == 'yearly'){
					$('#advanced_year').css('display', 'block');
					Calendar.UI.repeat('year');
				}
				if($('#repeat option:selected').val() == 'doesnotrepeat'){
					$('#advanced_options_repeating').slideUp('slow');
				}
			}
			if(task == 'month'){
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_weekofmonth').css('display', 'none');
				if($('#advanced_month_select option:selected').val() == 'weekday'){
					$('#advanced_weekday').css('display', 'block');
					$('#advanced_weekofmonth').css('display', 'block');
				}
			}
			if(task == 'year'){
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_byyearday').css('display', 'none');
				$('#advanced_bymonth').css('display', 'none');
				$('#advanced_byweekno').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				if($('#advanced_year_select option:selected').val() == 'byyearday'){
					//$('#advanced_byyearday').css('display', 'block');
				}
				if($('#advanced_year_select option:selected').val() == 'byweekno'){
					$('#advanced_byweekno').css('display', 'block');
				}
				if($('#advanced_year_select option:selected').val() == 'bydaymonth'){
					$('#advanced_bymonth').css('display', 'block');
					$('#advanced_bymonthday').css('display', 'block');
					$('#advanced_weekday').css('display', 'block');
				}
			}

		},
		setViewActive: function(view){
			$('#view input[type="button"]').removeClass('active');
			var id;
			switch (view) {
				case 'agendaWeek':
					id = 'oneweekview_radio';
					break;
				case 'month':
					id = 'onemonthview_radio';
					break;
				case 'list':
					id = 'listview_radio';
					break;
			}
			$('#'+id).addClass('active');
		},
		categoriesChanged:function(newcategories){
			categories = $.map(newcategories, function(v) {return v;});
			console.log('Calendar categories changed to: ' + categories);
			$('#category').multiple_autocomplete('option', 'source', categories);
		},
		Calendar:{
			overview:function(){
				if($('#choosecalendar_dialog').dialog('isOpen') == true){
					$('#choosecalendar_dialog').dialog('moveToTop');
				}else{
					Calendar.UI.loading(true);
					$('#dialog_holder').load(OC.filePath('calendar', 'ajax/calendar', 'overview.php'), function(){
						$('#choosecalendar_dialog').dialog({
							width : 600,
							height: 400,
							close : function(event, ui) {
								$(this).dialog('destroy').remove();
							}
						});
						Calendar.UI.loading(false);
					});
				}
			},
			activation:function(checkbox, calendarid)
			{
				Calendar.UI.loading(true);
				$.post(OC.filePath('calendar', 'ajax/calendar', 'activation.php'), { calendarid: calendarid, active: checkbox.checked?1:0 },
				  function(data) {
					Calendar.UI.loading(false);
					if (data.status == 'success'){
						checkbox.checked = data.active == 1;
						if (data.active == 1){
							$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
						}else{
							$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
						}
					}
				  });
			},
			newCalendar:function(object){
				var tr = $(document.createElement('tr'))
					.load(OC.filePath('calendar', 'ajax/calendar', 'new.form.php'),
						function(){Calendar.UI.Calendar.colorPicker(this)});
				$(object).closest('tr').after(tr).hide();
			},
			edit:function(object, calendarid){
				var tr = $(document.createElement('tr'))
					.load(OC.filePath('calendar', 'ajax/calendar', 'edit.form.php'), {calendarid: calendarid},
						function(){Calendar.UI.Calendar.colorPicker(this)});
				$(object).closest('tr').after(tr).hide();
			},
			deleteCalendar:function(calid){
				var check = confirm("Do you really want to delete this calendar?");
				if(check == false){
					return false;
				}else{
					$.post(OC.filePath('calendar', 'ajax/calendar', 'delete.php'), { calendarid: calid},
					  function(data) {
						if (data.status == 'success'){
							var url = 'ajax/events.php?calendar_id='+calid;
							$('#fullcalendar').fullCalendar('removeEventSource', url);
							$('#choosecalendar_dialog').dialog('destroy').remove();
							Calendar.UI.Calendar.overview();
							$('#calendar tr[data-id="'+calid+'"]').fadeOut(400,function(){
								$('#calendar tr[data-id="'+calid+'"]').remove();
							});
							$('#fullcalendar').fullCalendar('refetchEvents');
						}
					  });
				}
			},
			submit:function(button, calendarid){
				var displayname = $.trim($("#displayname_"+calendarid).val());
				var active = $("#edit_active_"+calendarid+":checked").length;
				var description = $("#description_"+calendarid).val();
				var calendarcolor = $("#calendarcolor_"+calendarid).val();
				if(displayname == ''){
					$("#displayname_"+calendarid).css('background-color', '#FF2626');
					$("#displayname_"+calendarid).focus(function(){
						$("#displayname_"+calendarid).css('background-color', '#F8F8F8');
					});
				}

				var url;
				if (calendarid == 'new'){
					url = OC.filePath('calendar', 'ajax/calendar', 'new.php');
				}else{
					url = OC.filePath('calendar', 'ajax/calendar', 'update.php');
				}
				$.post(url, { id: calendarid, name: displayname, active: active, description: description, color: calendarcolor },
					function(data){
						if(data.status == 'success'){
							$(button).closest('tr').prev().html(data.page).show().next().remove();
							$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
							$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
							if (calendarid == 'new'){
								$('#choosecalendar_dialog > table:first').append('<tr><td colspan="6"><a href="#" id="chooseCalendar"><input type="button" value="' + newcalendar + '"></a></td></tr>');
							}
						}else{
							$("#displayname_"+calendarid).css('background-color', '#FF2626');
							$("#displayname_"+calendarid).focus(function(){
								$("#displayname_"+calendarid).css('background-color', '#F8F8F8');
							});
						}
					}, 'json');
			},
			cancel:function(button, calendarid){
				$(button).closest('tr').prev().show().next().remove();
			},
			colorPicker:function(container){
				// based on jquery-colorpicker at jquery.webspirited.com
				var obj = $('.colorpicker', container);
				var picker = $('<div class="calendar-colorpicker"></div>');
				//build an array of colors
				var colors = {};
				$(obj).children('option').each(function(i, elm) {
					colors[i] = {};
					colors[i].color = $(elm).val();
					colors[i].label = $(elm).text();
				});
				for (var i in colors) {
					picker.append('<span class="calendar-colorpicker-color ' + (colors[i].color == $(obj).children(":selected").val() ? ' active' : '') + '" rel="' + colors[i].label + '" style="background-color: ' + colors[i].color + ';"></span>');
				}
				picker.delegate(".calendar-colorpicker-color", "click", function() {
					$(obj).val($(this).attr('rel'));
					$(obj).change();
					picker.children('.calendar-colorpicker-color.active').removeClass('active');
					$(this).addClass('active');
				});
				$(obj).after(picker);
				$(obj).css({
					position: 'absolute',
					left: -10000
				});
			}
		},
		Share:{
			init:function(){
				var itemShares = [OC.Share.SHARE_TYPE_USER, OC.Share.SHARE_TYPE_GROUP];
				$('#sharewith').autocomplete({minLength: 2, source: function(search, response) {
					$.get(OC.filePath('core', 'ajax', 'share.php'), { fetch: 'getShareWith', search: search.term, itemShares: itemShares }, function(result) {
						if (result.status == 'success' && result.data.length > 0) {
							response(result.data);
						}
					});
				},
				focus: function(event, focused) {
					event.preventDefault();
				},
				select: function(event, selected) {
					var itemType = 'event';
					var itemSource = $('#sharewith').data('item-source');
					var shareType = selected.item.value.shareType;
					var shareWith = selected.item.value.shareWith;
					$(this).val(shareWith);
					// Default permissions are Read and Share
					var permissions = OC.PERMISSION_READ | OC.PERMISSION_SHARE;
					OC.Share.share(itemType, itemSource, shareType, shareWith, permissions, function(data) {
						var newitem = '<li data-item-type="event"'
							+ 'data-share-with="'+shareWith+'" '
							+ 'data-permissions="'+permissions+'" '
							+ 'data-share-type="'+shareType+'">'+shareWith+' ('+(shareType == OC.Share.SHARE_TYPE_USER ? t('core', 'user') : t('core', 'group'))+')'
							+ '<span class="shareactions"><input class="update" type="checkbox" title="'+t('core', 'Editable')+'">'
							+ '<input class="share" type="checkbox" title="'+t('core', 'Shareable')+'" checked="checked">'
							+ '<input class="delete" type="checkbox" title="'+t('core', 'Deletable')+'">'
							+ '<img class="svg action delete" title="Unshare"src="'+ OC.imagePath('core', 'actions/delete.svg') +'"></span></li>';
						$('.sharedby.eventlist').append(newitem);
						$('#sharedWithNobody').remove();
						$('#sharewith').val('');
					});
					return false;
				}
				});

				$('.shareactions > input:checkbox').change(function() {
					var container = $(this).parents('li').first();
					var permissions = parseInt(container.data('permissions'));
					var itemType = container.data('item-type');
					var shareType = container.data('share-type');
					var itemSource = container.data('item');
					var shareWith = container.data('share-with');
					var permission = null;
					if($(this).hasClass('update')) {
						permission = OC.PERMISSION_UPDATE;
					} else if($(this).hasClass('share')) {
						permission = OC.PERMISSION_SHARE;
					} else if($(this).hasClass('delete')) {
						permission = OC.PERMISSION_DELETE;
					}
					// This is probably not the right way, but it works :-P
					if($(this).is(':checked')) {
						permissions += permission;
					} else {
						permissions -= permission;
					}
					OC.Share.setPermissions(itemType, itemSource, shareType, shareWith, permissions);
				});

				$('.shareactions > .delete').click(function() {
					var container = $(this).parents('li').first();
					var itemType = container.data('item-type');
					var shareType = container.data('share-type');
					var itemSource = container.data('item');
					var shareWith = container.data('share-with');
					OC.Share.unshare(itemType, itemSource, shareType, shareWith, function() {
						container.remove();
					});
				});
			}
		},
		Drop:{
			init:function(){
				if (typeof window.FileReader === 'undefined') {
					console.log('The drop-import feature is not supported in your browser :(');
					return false;
				}
				droparea = document.getElementById('fullcalendar');
				droparea.ondrop = function(e){
					e.preventDefault();
					Calendar.UI.Drop.drop(e);
				}
				console.log('Drop initialized successfully');
			},
			drop:function(e){
				var files = e.dataTransfer.files;
				for(var i = 0;i < files.length;i++){
					var file = files[i];
					var reader = new FileReader();
					reader.onload = function(event){
						Calendar.UI.Drop.doImport(event.target.result);
						$('#fullcalendar').fullCalendar('refetchEvents');
					}
					reader.readAsDataURL(file);
				}
			},
			doImport:function(data){
				$.post(OC.filePath('calendar', 'ajax/import', 'dropimport.php'), {'data':data},function(result) {
					if(result.status == 'success'){
						$('#fullcalendar').fullCalendar('addEventSource', result.eventSource);
						$('#notification').html(result.message);
						$('#notification').slideDown();
						window.setTimeout(function(){$('#notification').slideUp();}, 5000);
						return true;
					}else{
						$('#notification').html(result.message);
						$('#notification').slideDown();
						window.setTimeout(function(){$('#notification').slideUp();}, 5000);
					}
				});
			}
		}
	},
	Settings:{
		//
	},

}
$.fullCalendar.views.list = ListView;
function ListView(element, calendar) {
	var t = this;

	// imports
	jQuery.fullCalendar.views.month.call(t, element, calendar);
	var opt = t.opt;
	var trigger = t.trigger;
	var eventElementHandlers = t.eventElementHandlers;
	var reportEventElement = t.reportEventElement;
	var formatDate = calendar.formatDate;
	var formatDates = calendar.formatDates;
	var addDays = $.fullCalendar.addDays;
	var cloneDate = $.fullCalendar.cloneDate;
	function skipWeekend(date, inc, excl) {
		inc = inc || 1;
		while (!date.getDay() || (excl && date.getDay()==1 || !excl && date.getDay()==6)) {
			addDays(date, inc);
		}
		return date;
	}

	// overrides
	t.name='list';
	t.render=render;
	t.renderEvents=renderEvents;
	t.setHeight=setHeight;
	t.setWidth=setWidth;
	t.clearEvents=clearEvents;

	function setHeight(height, dateChanged) {
	}

	function setWidth(width) {
	}

	function clearEvents() {
		this.reportEventClear();
	}

	// main
	function sortEvent(a, b) {
		return a.start - b.start;
	}

	function render(date, delta) {
		if (!t.start){
			t.start = addDays(cloneDate(date, true), -7);
			t.end = addDays(cloneDate(date, true), 7);
		}
		if (delta) {
			if (delta < 0){
				addDays(t.start, -7);
				addDays(t.end, -7);
				if (!opt('weekends')) {
					skipWeekend(t.start, delta < 0 ? -1 : 1);
				}
			}else{
				addDays(t.start, 7);
				addDays(t.end, 7);
				if (!opt('weekends')) {
					skipWeekend(t.end, delta < 0 ? -1 : 1);
				}
			}
		}
		t.title = formatDates(
			t.start,
			t.end,
			opt('titleFormat', 'week')
		);
		t.visStart = cloneDate(t.start);
		t.visEnd = cloneDate(t.end);
	}

	function eventsOfThisDay(events, theDate) {
		var start = cloneDate(theDate, true);
		var end = addDays(cloneDate(start), 1);
		var retArr = new Array();
		for (i in events) {
			var event_end = t.eventEnd(events[i]);
			if (events[i].start < end && event_end >= start) {
				retArr.push(events[i]);
			}
		}
		return retArr;
	}

	function renderEvent(event) {
		if (event.allDay) { //all day event
			var time = opt('allDayText');
		}
		else {
			var time = formatDates(event.start, event.end, opt('timeFormat', 'agenda'));
		}
		var classes = ['fc-event', 'fc-list-event'];
		classes = classes.concat(event.className);
		if (event.source) {
			classes = classes.concat(event.source.className || []);
		}
		var html = '<tr>' +
			'<td>&nbsp;</td>' +
			'<td class="fc-list-time">' +
			time +
			'</td>' +
			'<td>&nbsp;</td>' +
			'<td class="fc-list-event">' +
			'<span id="list' + event.id + '"' +
			' class="' + classes.join(' ') + '"' +
			'>' +
			'<span class="fc-event-title">' +
			escapeHTML(event.title) +
			'</span>' +
			'</span>' +
			'</td>' +
			'</tr>';
		return html;
	}

	function renderDay(date, events) {
		var dayRows = $('<tr>' +
			'<td colspan="4" class="fc-list-date">' +
			'<span>' +
			formatDate(date, opt('titleFormat', 'day')) +
			'</span>' +
			'</td>' +
			'</tr>');
		for (i in events) {
			var event = events[i];
			var eventElement = $(renderEvent(event));
			triggerRes = trigger('eventRender', event, event, eventElement);
			if (triggerRes === false) {
				eventElement.remove();
			}else{
				if (triggerRes && triggerRes !== true) {
					eventElement.remove();
					eventElement = $(triggerRes);
				}
				$.merge(dayRows, eventElement);
				eventElementHandlers(event, eventElement);
				reportEventElement(event, eventElement);
			}
		}
		return dayRows;
	}

	function renderEvents(events, modifiedEventId) {
		events = events.sort(sortEvent);

		var table = $('<table class="fc-list-table"></table>');
		var total = events.length;
		if (total > 0) {
			var date = cloneDate(t.visStart);
			while (date <= t.visEnd) {
				var dayEvents = eventsOfThisDay(events, date);
				if (dayEvents.length > 0) {
					table.append(renderDay(date, dayEvents));
				}
				date=addDays(date, 1);
			}
		}

		this.element.html(table);
	}
}
$(document).ready(function(){
	Calendar.UI.initScroll();
	$('#fullcalendar').fullCalendar({
		header: false,
		firstDay: firstDay,
		editable: true,
		defaultView: defaultView,
		timeFormat: {
			agenda: agendatime,
			'': defaulttime
			},
		columnFormat: {
			month: t('calendar', 'ddd'),    // Mon
			week: t('calendar', 'ddd M/d'), // Mon 9/7
			day: t('calendar', 'dddd M/d')  // Monday 9/7
			},
		titleFormat: {
			month: t('calendar', 'MMMM yyyy'),
					// September 2009
			week: t('calendar', "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}"),
					// Sep 7 - 13 2009
			day: t('calendar', 'dddd, MMM d, yyyy'),
					// Tuesday, Sep 8, 2009
			},
		axisFormat: defaulttime,
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		allDayText: allDayText,
		viewDisplay: function(view) {
			$('#datecontrol_date').val($('<p>').html(view.title).text());
			if (view.name != defaultView) {
				$.post(OC.filePath('calendar', 'ajax', 'changeview.php'), {v:view.name});
				defaultView = view.name;
			}
			Calendar.UI.setViewActive(view.name);
			if (view.name == 'agendaWeek') {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 0.1);
			}
			else {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 1.35);
			}
		},
		columnFormat: {
		    week: 'ddd d. MMM'
		},
		selectable: true,
		selectHelper: true,
		select: Calendar.UI.newEvent,
		eventClick: Calendar.UI.editEvent,
		eventDrop: Calendar.UI.moveEvent,
		eventResize: Calendar.UI.resizeEvent,
		eventRender: function(event, element) {
			element.find('.fc-event-title').text($("<div/>").html(escapeHTML(event.title)).text())
			element.tipsy({
				className: 'tipsy-event',
				opacity: 0.9,
				gravity:$.fn.tipsy.autoBounds(150, 's'),
				fade:true,
				delayIn: 400,
				html:true,
				title:function() {
					return Calendar.UI.getEventPopupText(event);
				}
			});
		},
		loading: Calendar.UI.loading,
		eventSources: eventSources
	});
	$('#datecontrol_date').datepicker({
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true,
		beforeShow: function(input, inst) {
			var calendar_holder = $('#fullcalendar');
			var date = calendar_holder.fullCalendar('getDate');
			inst.input.datepicker('setDate', date);
			inst.input.val(calendar_holder.fullCalendar('getView').title);
			return inst;
		},
		onSelect: function(value, inst) {
			var date = inst.input.datepicker('getDate');
			$('#fullcalendar').fullCalendar('gotoDate', date);
		}
	});
	fillWindow($('#content'));
	OCCategories.changed = Calendar.UI.categoriesChanged;
	OCCategories.app = 'calendar';
	OCCategories.type = 'event';
	$('#oneweekview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'agendaWeek');
	});
	$('#onemonthview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'month');
	});
	$('#listview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'list');
	});
	$('#today_input').click(function(){
		$('#fullcalendar').fullCalendar('today');
	});
	$('#datecontrol_left').click(function(){
		$('#fullcalendar').fullCalendar('prev');
	});
	$('#datecontrol_right').click(function(){
		$('#fullcalendar').fullCalendar('next');
	});
	Calendar.UI.Share.init();
	Calendar.UI.Drop.init();
	$('#choosecalendar .generalsettings').on('click keydown', function(event) {
		event.preventDefault();
		OC.appSettings({appid:'calendar', loadJS:true, cache:false, scriptName:'settingswrapper.php'});
	});
	$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);
});
