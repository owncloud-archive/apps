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

angular.module('OC').factory '_Request', ->

	class Request

		constructor: (@$http, @Config, @publisher) ->

			# if the routes are not yet initialized we dont want to lose
			# requests. Save all requests and run them when the routes are
			# ready
			@initialized = false
			@shelvedRequests = []
			OC.Router.registerLoadedCallback =>
				@initialized = true
				for req in @shelvedRequests
					@post(req.route, req.routeParams, req.data,
							req.onSuccess, req.onFailure)

				@shelvedRequests = []


		# Do the actual post request
		# @param string route: the url which we want to request
		# @param object routeParams: Parameters that are needed to generate
		#                            the route
		# @param object data: the post params that we want to pass
		# @param function onSuccess: the function that will be called if
		#                            the request was successful
		# @param function onFailure: the function that will be called if the
		#                          request failed
		post: (route, routeParams, data, onSuccess, onFailure) ->
			# if routes are not ready yet, save the request
			if not @initialized
				request =
					route: route
					routeParams: routeParams
					data: data
					onSuccess: onSuccess
					onFailure: onFailure

				@shelvedRequests.push(request)
				return

			if routeParams
				url = OC.Router.generate(route, routeParams)
			else
				url = OC.Router.generate(route)

			# encode data object for post
			data or= {}
			postData = $.param(data)

			# pass the CSRF token as header
			headers =
				headers:
					'requesttoken': oc_requesttoken
					'Content-Type': 'application/x-www-form-urlencoded'

			# do the actual request
			@$http.post(url, postData, headers)
				.success (data, status, headers, config) =>
					if onSuccess
						onSuccess(data)
					# publish data to models
					for name, value of data.data
						@publisher.publishDataTo(name, value)

				.error (data, status, headers, config) ->
					if onFailure
						onFailure(data)

	return Request
