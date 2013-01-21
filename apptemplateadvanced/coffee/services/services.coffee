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

angular.module('AppTemplateAdvanced').factory 'AppTemplateAdvancedRequest',
['$http', '$rootScope', 'Config', '_AppTemplateAdvancedRequest', 'Publisher',
($http, $rootScope, Config, _AppTemplateAdvancedRequest, Publisher) ->

	return new _AppTemplateAdvancedRequest($http, $rootScope, Config, Publisher)
]

angular.module('AppTemplateAdvanced').factory 'ItemModel',
['_ItemModel', 'Publisher',
(_ItemModel, Publisher) ->

	model = new _ItemModel()
	Publisher.subscribeModelTo(model, 'items')
	return model
]