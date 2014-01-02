jQuery(document).ready(function () {
	//do not show when upgrade is in progress or an error message
	//is visible on the login page
	if (jQuery('#upgrade').length === 0 && jQuery('#body-login .error').length === 0) {
		showfirstrunwizard();
	}
});
