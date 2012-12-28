###
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

# Define your local request functions in an object that inherits from the
# Request object
angular.module('AppTemplateAdvanced').factory '_AppTemplateAdvancedRequest',
['_Request',
(_Request) ->

	class AppTemplateAdvancedRequest extends _Request


		constructor: ($http, $rootScope, Config, Publisher) ->
			super($http, $rootScope, Config, Publisher)


		saveName: (route, name) ->
			data =
				somesetting: name

			@post(route, {}, data)


		# Create your local request methods in here
		#
		# myReqest: (route, ...) ->


	return AppTemplateAdvancedRequest
]