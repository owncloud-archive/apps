function indexCtrl($scope) {

}
;

function updateCtrl($scope, $routeParams) {
	$scope.update = function() {
		$('#upd-progress').show();
		$('#updater-start').hide();
		var updateEventSource = new OC.EventSource(OC.filePath('updater', 'ajax', 'update.php'));
		updateEventSource.listen('success', function(message) {
			$('<span>').append(message).append('<br />').appendTo($('#upd-progress'));
		});
		updateEventSource.listen('error', function(message) {
			$('<span>').addClass('error').append(message).append('<br />').appendTo($('#upd-progress'));
			message = 'Please fix this and retry.';
			$('<span>').addClass('error').append(message).append('<br />').appendTo($('#upd-progress'));
			updateEventSource.close();
		});
		updateEventSource.listen('failure', function(message) {
			$('<span>').addClass('error').append(message).append('<br />').appendTo($('#upd-progress'));
			$('<span>')
					.addClass('error bold')
					.append('<br />')
					.append(t('updater', 'The update was unsuccessful. Please report this issue to the <a href="https://github.com/owncloud/apps/issues" target="_blank">ownCloud community</a>.'))
					.appendTo($('#upd-progress'));
		});
		updateEventSource.listen('done', function(message) {
			$('<span>').addClass('bold').append('<br />').append('<a href="' + OC.webroot + '">' + OC.webroot + '</a>').appendTo($('#upd-progress'));
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
