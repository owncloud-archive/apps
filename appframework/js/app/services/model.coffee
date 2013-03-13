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


# Model which offers basic crud for storing your data
angular.module('OC').factory '_Model', ->

	class Model

		constructor: ->
			@_data = []
			@_dataMap = {}
			@_filterCache = {}


		handle: (data) ->
			###
			Redirects to add method
			###
			@add(data)


		add: (data) ->
			###
			Adds a new entry or updates an entry if the id exists already
			###
			@_invalidateCache()
			if angular.isDefined(@_dataMap[data.id])
				@update(data)
			else
				@_data.push(data)
				@_dataMap[data.id] = data


		update: (data) ->
			###
			Update an entry by searching for its id
			###
			@_invalidateCache()
			entry = @getById(data.id)
			for key, value of data
				if key == 'id'
					continue
				else
					entry[key] = value


		getById: (id) ->
			###
			Return an entry by its id
			###
			return @_dataMap[id]


		getAll: ->
			###
			Returns all stored entries
			###
			return @_data


		removeById: (id) ->
			###
			Remove an entry by id
			###
			for entry, counter in @_data
				if entry.id == id
					@_data.splice(counter, 1)
					delete @_dataMap[id]
					@_invalidateCache()
					break


		clear: ->
			###
			Removes all cached elements
			###
			@_data.length = 0
			@_dataMap = {}
			@_invalidateCache()


		_invalidateCache: ->
			@_filterCache = {}


		get: (query) ->
			###
			Calls, caches and returns filtered results
			###
			hash = query.hashCode()
			if not angular.isDefined(@_filterCache[hash])
				@_filterCache[hash] = query.exec(@_data)
			return @_filterCache[hash]
			

		size: ->
			###
			Return the number of all stored entries
			###
			return @_data.length


	return Model