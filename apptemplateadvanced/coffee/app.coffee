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

angular.module('AppTemplateAdvanced', ['OC']).config ['$provide', '$interpolateProvider', 
($provide, $interpolateProvider) ->

	# uses doulbe square brackets instead of double curly braces because twig
	# already uses doulbe curly braces
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');

	# Use this for configuration values
	Config =
		myParam: 'test'

	# declare your routes here
	# Hint: angularjs comes with an own route system with which you can
	# build HTML5 apps with enabled history access, meaning: you can go
	# forward and backward and change the state of your app
	# http:#docs.angularjs.org/api/ng.$route
	Config.routes =
		saveNameRoute: 'apptemplate_advanced_ajax_setsystemvalue'
		getNameRoute: 'apptemplate_advanced_ajax_getsystemvalue'

	return $provide.value('Config', Config)
]


