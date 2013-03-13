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


# Inherit from this baseclass to define your own routes
angular.module('OC').factory '_Request', ->

	class Request

		constructor: (@_$http, @_publisher, @_router) ->
			@_initialized = false
			@_shelvedRequests = []

			@_router.registerLoadedCallback =>
				@_initialized = true
				@_executeShelvedRequests()
				@_shelvedRequests = []


		request: (route, routeParams={}, data={}, onSuccess=null,
			onFailure=null, config={}) ->
			###
			Wrapper to do a normal request to the server. This needs to
			be done to hook the publisher into the requests and to handle
			requests, that come in before routes have been loaded
			###
			# if routes are not ready yet, save the request
			if not @_initialized
				@_shelveRequest(route, routeParams, data, onSuccess, onFailure,
					config)
				return

			url = @_router.generate(route, routeParams)

			defaultConfig =
				url: url
				data: data

			# overwrite default values from passed in config
			angular.extend(defaultConfig, config)

			@_$http(defaultConfig)
				.success (data, status, headers, config) =>
					if onSuccess != null
						onSuccess(data, status, headers, config)

					# publish data to models
					for name, value of data.data
						@_publisher.publishDataTo(name, value)

				.error (data, status, headers, config) ->
					if onFailure != null
						onFailure(data, status, headers, config)


		post: (route, routeParams={}, data={}, onSuccess=null,
			onFailure=null, config={}) ->
			###
			Request shortcut which sets the method to POST
			###
			config.method = 'POST'
			@request(route, routeParams, data, onSuccess, onFailure, config)


		get: (route, routeParams={}, data={}, onSuccess=null,
			onFailure=null, config={}) ->
			###
			Request shortcut which sets the method to GET
			###
			config.method = 'GET'
			@request(route, routeParams, data, onSuccess, onFailure, config)


		_shelveRequest: (route, routeParams, data, onSuccess, onFailure,
			config) ->
			###
			Saves requests for later if the routes have not been loaded
			###
			request =
				route: route
				routeParams: routeParams
				data: data
				onSuccess: onSuccess
				onFailure: onFailure
				config: config

			@_shelvedRequests.push(request)


		_executeShelvedRequests: ->
			###
			Run all saved requests that were done before routes were fully
			loaded
			###
			for r in @_shelvedRequests
				@request(r.route, r.routeParams, r.data, r.onSuccess,
					r.onFailure, r.config)



	return Request
