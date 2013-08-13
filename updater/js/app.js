	angular.module('updater', []).
		config(['$routeProvider', function($routeProvider) {
			$routeProvider.
			when('/index', {   controller: backupCtrl}).
			when('/update', {templateUrl: 'templates/partials/update.html', controller: updateCtrl}).
			otherwise({redirectTo: '/index'});
		}]);
