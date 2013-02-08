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

angular.module('AppTemplateAdvanced').controller 'ExampleController',
['$scope', 'Config', 'AppTemplateAdvancedRequest', '_ExampleController', 'ItemModel',
($scope, Config, AppTemplateAdvancedRequest, _ExampleController, ItemModel) ->
	return new _ExampleController($scope, Config, AppTemplateAdvancedRequest, ItemModel)
]