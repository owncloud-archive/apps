$('#newCalendar').live('click', function () {
	Calendar.UI.Calendar.newCalendar(this);
});
$('#caldav_url_close').live('click', function () {
	$('#caldav_url').hide();$('#caldav_url_close').hide();
});
$('#caldav_url').live('mouseover', function () {
	$('#caldav_url').select();
});
$('#editCategories').live('click', function () {
	$(this).tipsy('hide');OCCategories.edit();
});
$('#allday_checkbox').live('click', function () {
	Calendar.UI.lockTime();
});
$('#advanced_options_button').live('click', function () {
	Calendar.UI.showadvancedoptions();
});
$('#advanced_options_button_repeat').live('click', function () {
	Calendar.UI.showadvancedoptionsforrepeating();
});
$('#submitNewEvent').live('click', function () {
	Calendar.UI.validateEventForm($(this).data('link'));
});
$('#chooseCalendar').live('click', function () {
	Calendar.UI.Calendar.newCalendar(this);
});
$('.activeCalendar').live('change', function () {
	Calendar.UI.Calendar.activation(this,$(this).data('id'));
});
$('#allday_checkbox').live('click', function () {
	Calendar.UI.lockTime();
});
$('#editEvent-submit').live('click', function () {
	console.log('submit-event');
	Calendar.UI.validateEventForm($(this).data('link'));
});
$('#editEvent-delete').live('click', function () {
	Calendar.UI.submitDeleteEventForm($(this).data('link'));
});
$('#editEvent-export').live('click', function () {
	window.location = $(this).data('link');
});
$('#chooseCalendar-showCalDAVURL').live('click', function () {
	Calendar.UI.showCalDAVUrl($(this).data('user'), $(this).data('caldav'));
});
$('#chooseCalendar-edit').live('click', function () {
	Calendar.UI.Calendar.edit($(this), $(this).data('id'));
});
$('#chooseCalendar-delete').live('click', function () {
	Calendar.UI.Calendar.deleteCalendar($(this).data('id'));
});
$('#editCalendar-submit').live('click', function () {
	Calendar.UI.Calendar.submit($(this), $(this).data('id'));
});
$('#editCalendar-cancel').live('click', function () {
	Calendar.UI.Calendar.cancel($(this), $(this).data('id'));
});
$('.choosecalendar-rowfield-active').live('click', function () {
	Calendar.UI.Share.activation($(this), $(this).data('id'));
});