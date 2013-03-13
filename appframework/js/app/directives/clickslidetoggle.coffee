###

ownCloud - App Framework

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt nukeawhale@gmail.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

###



# Used to slide up an area and can be customized by passing an expression.
# If selector is defined, a different area is slid up on click
# If hideOnFocusLost is defined, the slid up area will hide when the focus is
# lost
angular.module('OC').directive 'ocClickSlideToggle',
['$rootScope', ($rootScope) ->

	return (scope, elm, attr) ->
		options = scope.$eval(attr.ocClickSlideToggle)

		# get selected slide area
		if angular.isDefined(options) and angular.isDefined(options.selector)
			slideArea = $(options.selector)
		else
			slideArea = elm

		elm.click ->
			if slideArea.is(':visible') and not slideArea.is(':animated')
				slideArea.slideUp()
			else
				slideArea.slideDown()

		# if focus lost is set use broadcast to be sure that the currently
		# active element doesnt get slid up
		if angular.isDefined(options) and
		angular.isDefined(options.hideOnFocusLost) and
		options.hideOnFocusLost
			$(document.body).click ->
				$rootScope.$broadcast 'ocLostFocus'

			$rootScope.$on 'ocLostFocus', (scope, params) ->
				if params != slideArea
					if slideArea.is(':visible') and not slideArea.is(':animated')
						slideArea.slideUp()

			slideArea.click (e) ->
				$rootScope.$broadcast 'ocLostFocus', slideArea
				e.stopPropagation()

			elm.click (e) ->
				$rootScope.$broadcast 'ocLostFocus', slideArea
				e.stopPropagation()

]