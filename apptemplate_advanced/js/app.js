/**
* ownCloud - App Template Example
*
* @author Bernhard Posselt
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * README:
 *
 * This is only a small example. If you're going to use angularjs in your project,
 * split your javascript into multiple files and compile it a buildscript.
 * You could also use coffeescript and a cakefile for this task
 *
 * When you create this with coffeescript or a javascript tool, create four folders
 * for your angular files: controllers/ filters/ services/ and directives/
 * The main file should be named app.js
 *
 */
(function(window, $, angular, OC, CSRFToken){

	'use strict';

	/**
	 * With this block you define your app. This has to be at the top the js,
	 * the following things are not needed to in order. Make sure to mind this
	 * when you use a custom compile script
	 */
	var app = angular.module('AppTemplateAdvanced', [])
		.config(['$provide', function($provide){

			// Use this for configuration values
			var Config = {
				// your config values here
			};

			// declare your routes here
			// Hint: angularjs comes with an own route system with which you can
			// build HTML5 apps with enabled history access, meaning: you can go
			// forward and backward and change the state of your app
			// http://docs.angularjs.org/api/ng.$route
			Config.routes = {
				saveNameRoute: 'apptemplate_advanced_ajax_setsystemvalue'
			};
			
			return $provide.value('Config', Config);
		}
	]);


	/**
	 * This function is run once angular is set up. That doesnt mean though that
	 * the document is ready
	 */
	angular.module('AppTemplateAdvanced').
		run(['$rootScope', function($rootScope){

			var init = function(){
				$rootScope.$broadcast('routesLoaded');
			};

			// this registers a callback that is executed once the routes have
			// finished loading. Before this you cant really do request
			OC.Router.registerLoadedCallback(init);
		}
	]);


	/**
	 * Instantiate your controllers in a seperate function to stay flexible and
	 * be able to test them later on
	 */
	angular.module('AppTemplateAdvanced').
		controller('ExampleController', ['$scope', 'Config', 'Request', '_ExampleController',
		function($scope, Config, Request, _ExampleController){
			return new _ExampleController($scope, Config, Request);
		}
	]);


	/**
	 * This is an example of a controller. We pass in the Config via Dependency
	 * Injection. A factory creates a shared instance. You can also share objects
	 * across controllers this way
	 */
	angular.module('AppTemplateAdvanced').
		factory('_ExampleController', [function(){

			// use prototyping to stay flexible. If you use coffeescript,
			var Controller = function($scope, Config, Request){
				var self = this;

				this.$scope = $scope;
				this.config = Config;
				this.request = Request;

				// bind methods on the scope so that you can access them in the
				// controllers child HTML
				this.$scope.saveName = function(name){
					self.saveName(name);
				};
			};


			/**
			 * Makes an ajax query to save the name
			 */
			Controller.prototype.saveName = function(name){
				this.request.saveName(this.config.routes.saveNameRoute, name);
			};

			return Controller;
		}
	]);


	/**
	 * Its always good to put the object that does routes request into a seperate
	 * object to be able to adjust it easily
	 */
	angular.module('AppTemplateAdvanced').
		factory('Request', ['$http', '$rootScope', 'Config', function($http, $rootScope, Config){

			var Request = function($http, $rootScope, Config){
				var self = this;

				this.$http = $http;
				this.$rootScope = $rootScope;
				this.config = Config;

				// if the routes are not yet initialized we dont want to lose
				// requests. Save all requests and run them when the routes are
				// ready
				this.initialized = false;
				this.shelvedRequests = [];

				this.$rootScope.$on('routesLoaded', function(){
					for(var i=0; i<self.shelvedRequests.length; i++){
						var req = self.shelvedRequests[i];
						self.post(req.route, req.routeParams, req.data,
								req.onSuccess, req.onFailure);
					}

					self.initialized = true;
					self.shelvedRequests = [];
				});

			};


			/**
			 * Do the actual post request
			 * @param string route: the url which we want to request
			 * @param object routeParams: Parameters that are needed to generate
			 *                            the route
			 * @param object data: the post params that we want to pass
			 * @param function onSuccess: the function that will be called if
			 *                            the request was successful
			 * @param function onFailure: the function that will be called if the
			 *                          request failed
			 */
			Request.prototype.post = function(route, routeParams, data, onSuccess, onFailure){

				// if routes are not ready yet, save the request
				if(!this.initialized){
					var request = {
						route: route,
						routeParams: routeParams,
						data: data,
						onSuccess: onSuccess,
						onFailure: onFailure
					};
					this.shelvedRequests.push(request);
					return;
				}

				var url;
				if(routeParams){
					url = OC.Router.generate(route, routeParams);
				} else {
					url = OC.Router.generate(route);
				}

				// encode data object for post
				var postData = data || {};
				postData = $.param(data);

				// pass the CSRF token as header
				var headers = {
					requesttoken: CSRFToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				};

				// do the actual request
				this.$http.post(url, postData, {headers: headers}).
					success(function(data, status, headers, config){

						if(onSuccess){
							onSuccess(data);
						}
					}).
					error(function(data, status, headers, config){

						if(onFailure){
							onFailure(data);
						}
					});
			};


			/**
			 * Save the name to the server
			 * @param string route: the route for the server
			 * @param string name: the new name
			 */
			Request.prototype.saveName = function(route, name) {
				this.post(route, {}, {somesetting: name});
			};

			return new Request($http, $rootScope, Config);
		}
	]);


	/**
	 * Use filters to perform tasks that need to be done when rendering
	 * This simply turns some letters into numbers
	 */
	angular.module('AppTemplateAdvanced').
		filter('leetIt', function(){

			var leetIt = function(leetThis){
				return leetThis.replace('e', '3').replace('i', '1');
			};

			return leetIt;
		}
	);


})(window, jQuery, angular, OC, oc_requesttoken);