$(document).ready(function () {
	if ($('#body-login').length > 0) {
		return true; //deactivate on login page
	}
	if ($('#filesApp').val() && $('#isPublic').val()) {
		images = $("#fileList").find("tr[data-mime^='image']").length;
		if (images > 0) {
			// toggle for opening shared file list as picture view
			// TODO find a way to not need to use inline CSS
			button = $('<div class="button"'
				+'style="position: absolute; right: 0; top: 0; font-weight: normal;">'
					+'<img class="svg" src="' + OC.filePath('core', 'img/actions', 'toggle-pictures.svg') + '"'
					+'alt="' + t('gallery', 'Picture view') + '"'
					+'style="vertical-align: text-top; '
					+'-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=50); '
					+'filter: alpha(opacity=50); opacity: .5;" />'
				+'</div>');
			$('#controls').append(button);

			button.click( function (event) {
				window.location.href = window.location.href.replace('service=files', 'service=gallery');
			});
		}
	}
});
