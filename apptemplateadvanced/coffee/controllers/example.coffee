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


# This is an example of a controller. We pass in the Config via Dependency
# Injection. A factory creates a shared instance. You can also share objects
# across controllers this way
angular.module('AppTemplateAdvanced').factory '_ExampleController', ->

	class ExampleController

		constructor: (@$scope, @config, @request, @itemModel) ->

			# bind methods on the scope so that you can access them in the
			# controllers child HTML
			@$scope.saveName = (name) =>
				@saveName(name)

			@getName(@$scope)


		saveName: (name) ->
			@request.saveName(@config.routes.saveNameRoute, name)

		getName: (scope) ->
			@request.getName(@config.routes.getNameRoute, scope)

	return ExampleController
