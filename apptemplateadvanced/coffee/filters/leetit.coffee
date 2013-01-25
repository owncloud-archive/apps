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

# Use filters to perform tasks that need to be done when rendering
# This simply turns some letters into numbers
angular.module('AppTemplateAdvanced').filter 'leetIt', ->

	return (leetThis) ->
		return leetThis.replace('e', '3').replace('i', '1')
