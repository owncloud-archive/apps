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

###
# This file creates instances of classes
###

angular.module('OC').factory 'Publisher',
['_Publisher',
(_Publisher) ->
	return new _Publisher()
]