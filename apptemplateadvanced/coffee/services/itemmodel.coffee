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

angular.module('AppTemplateAdvanced').factory '_ItemModel',
['_Model',
(_Model) ->

	class ItemModel extends _Model

		constructor: ->
			super()
			#@hasForeignKey('user')


	return ItemModel
]