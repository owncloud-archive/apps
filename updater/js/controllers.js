function indexCtrl($scope) {

}
;

function updateCtrl($scope, $routeParams) {
	$scope.update = function() {
		$('#upd-progress').show();
		$('#updater-start').hide();
		var updateEventSource = new OC.EventSource(OC.filePath('updater', 'ajax', 'update.php'));
		updateEventSource.listen('success', function(message) {
			$('<span></span>').append(message).append('<br />').appendTo($('#upd-progress'));
		});
		updateEventSource.listen('error', function(message) {
			$('<span></span>').addClass('error').append(message).append('<br />').appendTo($('#upd-progress'));
			message = 'Please fix this and retry.';
			$('<span></span>').addClass('error').append(message).append('<br />').appendTo($('#upd-progress'));
		});
		updateEventSource.listen('failure', function(message) {
			$('<span></span>').addClass('error').append(message).append('<br />').appendTo($('#upd-progress'));
			$('<span></span>')
					.addClass('error bold')
					.append('<br />')
					.append(t('updater', 'The update was unsuccessful. Please check logs at admin page and report this issue to the <a href="https://github.com/owncloud/apps/issues" target="_blank">ownCloud community</a>.'))
					.appendTo($('#upd-progress'));
			updateEventSource.close();
		});
		updateEventSource.listen('done', function(message) {
			var href = '/',
			title = t('Updater', 'Proceed');
			if (OC.webroot!=''){
				href = OC.webroot;
			}
			$('<span></span>').addClass('bold').append('<br />').append('<a href="' + href + '">' + title + '</a>').appendTo($('#upd-progress'));
		});
	};
}
;

function backupCtrl($scope, $http) {
	$http.get(OC.filePath('updater', 'ajax', 'backup/list.php'), {headers: {'requesttoken': oc_requesttoken}})
			.success(function(data) {
		$scope.entries = data.data;
	});

	$scope.doDelete = function(name) {
		$http.get(OC.filePath('updater', 'ajax', 'backup/delete.php'), {
			headers: {'requesttoken': oc_requesttoken},
			params: {'filename': name}
		}).success(function(data) {
			$http.get(OC.filePath('updater', 'ajax', 'backup/list.php'), {headers: {'requesttoken': oc_requesttoken}})
					.success(function(data) {
				$scope.entries = data.data;
			});
		});
	}
	$scope.doDownload = function(name) {
		window.open(OC.filePath('updater', 'ajax', 'backup/download.php')
				+ '?requesttoken=' + oc_requesttoken
				+ '&filename=' + name
				);
	}
}
;
