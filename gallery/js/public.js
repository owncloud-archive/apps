$(document).ready(function () {
	if ($('#body-login').length > 0) {
		return true; //deactivate on login page
	}
	if ($('#filesApp').val() && $('#isPublic').val()) {
		images = $("#fileList").find("tr[data-mime^='image']").length;
		if (images > 0) {
			button = $('<div class="button" style="float:right; font-weight:normal;"></div>');
			button.append(t('gallery', 'Picture view' ));
			$('#controls').append(button);

			button.click( function (event) {
				window.location.href = window.location.href.replace('service=files', 'service=gallery');
			});
		}
	}
});
