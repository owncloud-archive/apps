OC.Contacts = OC.Contacts || {};

/**
 * TODO: Use $.Deferred.
 */

(function(window, $, OC) {
	'use strict';
	/**
	* An object for saving contact data to backends
	*
	* All methods returns a jQuery.Deferred object which resolves
	* to either the requested response or an error object:
	* {
	*	status: 'error',
	*	message: The error message
	* }
	*
	* @param string user The user to query for. Default to current user
	*/
	var Storage = function(user) {
		this.user = user ? user : OC.currentUser;
	}

	/**
	 * Get all address books registered for this user.
	 *
	 * @param function cb Callback function returning the data
	 * @return An array containing address book metadata e.g.:
	 * {
	 * 	backend:'database',
	 * 	id:'1234'
	 * 	permissions:31,
	 * 	displayname:'Contacts'
	 * }
	 */
	Storage.prototype.getAddressBooksForUser = function() {
		return this.requestRoute(
			'contacts_address_books_for_user',
			'GET',
			{user: this.user}
		);
	}

	/**
	 * Get all groups for this user.
	 *
	 * @return An array containing the groups, the favorites, any shared
	 * address books, the last selected group and the sort order of the groups.
	 * {
	 * 	'categories': [{'id':1',Family'}, {...}],
	 * 	'favorites': [123,456],
	 * 	'shared': [],
	 * 	'lastgroup':'1',
	 * 	'sortorder':'3,2,4'
	 * }
	 */
	Storage.prototype.getGroupsForUser = function() {
		console.log('getGroupsForUser');
		return this.requestRoute(
			'contacts_categories_list',
			'GET',
			{user: this.user}
		);
	}

	/**
	 * Get contacts from an address book from a specific backend
	 *
	 * @param string backend
	 * @param string id Address book ID
	 * @return An array containing contact data e.g.:
	 * {
	 * 	metadata:
	 * 		{
	 * 		id:'1234'
	 * 		permissions:31,
	 * 		displayname:'John Q. Public',
	 * 		lastmodified: (unix timestamp),
	 * 		owner: 'joye',
	 * 		parent: (id of the parent address book)
	 * 	data: //array of VCard data
	 * }
	 */
	Storage.prototype.getContacts = function(backend, id) {
		return this.requestRoute(
			'contacts_address_book_collection',
			'GET',
			{user: this.user, backend: backend, id: id}
		);
	}

	Storage.prototype.requestRoute = function(route, type, params) {
		var self = this;
		//var dfd = new $.Deferred();
		var url = OC.Router.generate(route, params);
		return $.ajax({type: type, url: url});/*
			.done(function(jsondata) {
				if(!jsondata || !jsondata.status) {
					console.log(type, 'error. Response:', jsondata);
					dfd.reject({
						status: 'error',
						message: self.getMessage('network_or_server_error')
					});
				} else if(jsondata.status === 'success') {
					dfd.resolve(jsondata.data);
				} else if(jsondata.status === 'error') {
					dfd.reject({
						status: 'error',
						message: jsondata.data.message
					});
				}
			}).fail(function(jqxhr, textStatus, error) {
				var err = textStatus + ', ' + error;
				console.log( "Request Failed: " + err);
					dfd.reject({
						status: 'error',
						message: t('contacts', 'Failed getting address books: {error}', {error: err})
					});
			});
		return dfd.promise();*/
	}

	OC.Contacts.Storage = Storage;

})(window, jQuery, OC);
