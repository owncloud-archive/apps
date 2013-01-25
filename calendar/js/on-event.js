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