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


# A query for returning a list with elements equal to the provided one
angular.module('OC').factory '_EqualQuery', ['_Query',
(_Query) ->

	class EqualQuery extends _Query

		constructor: (@_field, @_value) ->
			name = 'equal'
			super(name, [@_field, @_value])


		exec: (data) ->
			equal = []
			for entry in data
				if entry[@_field] == @_value
					equal.push(entry)

			return equal


	return EqualQuery
]

