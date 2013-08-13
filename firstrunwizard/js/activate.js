jQuery(document).ready(function () {
	//do not show when upgrade is in progress
	if (jQuery('#upgrade').length === 0) {
		showfirstrunwizard();
	}
});
