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
	* @param string user The user to query for. Defaults to current user
	*/
	var Storage = function(user) {
		this.user = user ? user : OC.currentUser;
		this.contactPhoto = 'data:image/png;base64,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';
	}

	/**
	 * Get all address books registered for this user.
	 *
	 * @return An array containing object of address book metadata e.g.:
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
	 * Add an address book to a specific backend
	 *
	 * @param string backend - currently defaults to 'database'
	 * @param object params An object {displayname:"My contacts", description:""}
	 * @return An array containing contact data e.g.:
	 * {
	 * 	metadata:
	 * 		{
	 * 		id:'1234'
	 * 		permissions:31,
	 * 		displayname:'My contacts',
	 * 		lastmodified: (unix timestamp),
	 * 		owner: 'joye',
	 * }
	 */
	Storage.prototype.addAddressBook = function(backend, parameters) {
		console.log('Storage.addAddressBook', backend);
		return this.requestRoute(
			'contacts_address_book_add',
			'POST',
			{user: this.user, backend: 'database'},
			params
		);
	}

	/**
	 * Delete an address book from a specific backend
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
	 */
	Storage.prototype.deleteAddressBook = function(backend, addressbookid) {
		console.log('Storage.deleteAddressBook', backend, addressbookid);
		return this.requestRoute(
			'contacts_address_book_delete',
			'POST',
			{user: this.user, backend: 'database', addressbookid: addressbookid}
		);
	}

	/**
	 * Get contacts from an address book from a specific backend
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
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
	Storage.prototype.getContacts = function(backend, addressbookid) {
		return this.requestRoute(
			'contacts_address_book_collection',
			'GET',
			{user: this.user, backend: backend, addressbookid: addressbookid}
		);
	}

	/**
	 * Add a contact to an address book from a specific backend
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
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
	Storage.prototype.addContact = function(backend, addressbookid) {
		console.log('Storage.addContact', backend, addressbookid);
		return this.requestRoute(
			'contacts_address_book_add_contact',
			'POST',
			{user: this.user, backend: backend, addressbookid: addressbookid}
		);
	}

	/**
	 * Delete a single property.
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
	 * @param string contactid Contact ID
	 * @param object params An object with the following properties:
	 * @param string name The name of the property e.g. EMAIL.
	 * @param string checksum For non-singular properties such as email this must contain
	 * 	an 8 character md5 checksum of the serialized \Sabre\Property
	 */
	Storage.prototype.deleteProperty = function(backend, addressbookid, contactid, params) {
		return this.requestRoute(
			'contacts_contact_delete_property',
			'POST',
			{user: this.user, backend: backend, addressbookid: addressbookid, contactid: contactid},
			params
		);
	}

	/**
	 * Save a property.
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
	 * @param string contactid Contact ID
	 * @param object params An object with the following properties:
	 * @param string name The name of the property e.g. EMAIL.
	 * @param string|array value The of the property
	 * @param array parameters Optional parameters for the property
	 * @param string checksum For non-singular properties such as email this must contain
	 * 	an 8 character md5 checksum of the serialized \Sabre\Property
	 */
	Storage.prototype.saveProperty = function(backend, addressbookid, contactid, params) {
		return this.requestRoute(
			'contacts_contact_save_property',
			'POST',
			{user: this.user, backend: backend, addressbookid: addressbookid, contactid: contactid},
			params
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
	 * Add a group
	 *
	 * @param string name
	 * @return A JSON object containing the (maybe sanitized) group name and its ID:
	 * {
	 * 	'id':1234,
	 * 	'name':'My group'
	 * }
	 */
	Storage.prototype.addGroup = function(name) {
		console.log('Storage.addGroup', name);
		return this.requestRoute(
			'contacts_categories_add',
			'POST',
			{user: this.user},
			{name: name}
		);
	}

	/**
	 * Delete a group
	 *
	 * @param string name
	 */
	Storage.prototype.deleteGroup = function(name) {
		return this.requestRoute(
			'contacts_categories_delete',
			'POST',
			{user: this.user},
			{name: name}
		);
	}

	/**
	 * Set a user preference
	 *
	 * @param string key
	 * @param string value
	 */
	Storage.prototype.setPreference = function(key, value) {
		return this.requestRoute(
			'contacts_setpreference',
			'POST',
			{user: this.user},
			{key: key, value:value}
		);
	}

	Storage.prototype.requestRoute = function(route, type, routeParams, params) {
		var self = this;
		var url = OC.Router.generate(route, routeParams);
		var ajaxParams = {type: type, url: url, dataType: 'json'};
		if(typeof params === 'object') {
			ajaxParams['data'] = params;
		}
		return $.ajax(ajaxParams);
	}

	OC.Contacts.Storage = Storage;

})(window, jQuery, OC);
