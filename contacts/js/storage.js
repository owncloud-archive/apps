OC.Contacts = OC.Contacts || {};

/**
 * TODO: Use $.Deferred.
 */

(function(window, $, OC) {
	'use strict';

	var JSONResponse = function(response, jqXHR) {
		this.getAllResponseHeaders = jqXHR.getAllResponseHeaders;
		this.getResponseHeader = jqXHR.getResponseHeader;
		this.status = jqXHR.status;
		if(!response || !response.status || response.status === 'error') {
			this.error = true;
			this.message = response.data.message || 'Unknown error.';
		} else {
			this.error = false;
			if(response.data) {
				this.data = response.data;
			} else {
				this.data = response;
			}
		}
	}

	/**
	* An object for saving contact data to backends
	*
	* All methods returns a jQuery.Deferred object which resolves
	* to either the requested response or an error object:
	* {
	*	error: true,
	*	message: The error message
	* }
	*
	* @param string user The user to query for. Defaults to current user
	*/
	var Storage = function(user) {
		this.user = user ? user : OC.currentUser;
	}

	/**
	 * Get all address books registered for this user.
	 *
	 * @return An array containing object of address book metadata e.g.:
	 * {
	 * 	backend:'local',
	 * 	id:'1234'
	 * 	permissions:31,
	 * 	displayname:'Contacts'
	 * }
	 */
	Storage.prototype.getAddressBooksForUser = function() {
		return this.requestRoute(
			'contacts_address_books_for_user',
			'GET',
			{}
		);
	}

	/**
	 * Add an address book to a specific backend
	 *
	 * @param string backend - currently defaults to 'local'
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
			{backend: 'local'},
			parameters
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
			'DELETE',
			{backend: 'local', addressbookid: addressbookid}
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
			{backend: backend, addressbookid: addressbookid}
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
			{backend: backend, addressbookid: addressbookid}
		);
	}

	/**
	 * Delete a contact from an address book from a specific backend
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
	 * @param string contactid Address book ID
	 */
	Storage.prototype.deleteContact = function(backend, addressbookid, contactid) {
		console.log('Storage.deleteContact', backend, addressbookid, contactid);
		return this.requestRoute(
			'contacts_address_book_delete_contact',
			'DELETE',
			{backend: backend, addressbookid: addressbookid, contactid: contactid}
		);
	}

	/**
	 * Get Image instance for a contacts profile picture
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
	 * @param string contactid Address book ID
	 * @return Image
	 */
	Storage.prototype.getContactPhoto = function(backend, addressbookid, contactid) {
		var photo = new Image();
		var url = OC.Router.generate(
			'contacts_contact_photo',
			{backend: backend, addressbookid: addressbookid, contactid: contactid}
		);
		var defer = $.Deferred();
		$.when(
			$(photo).load(function() {
				defer.resolve(photo);
			})
			.error(function() {
				console.log('Error loading default photo', arguments)
			})
			.attr('src', url + '?refresh=' + Math.random())
		)
		.fail(function(jqxhr, textStatus, error) {
			defer.reject();
			var err = textStatus + ', ' + error;
			console.log( "Request Failed: " + err);
			$(document).trigger('status.contact.error', {
				message: t('contacts', 'Failed loading photo: {error}', {error:err})
			});
		});
		return defer.promise();
	}

	/**
	 * Get Image instance for default profile picture
	 *
	 * This method loads the default picture only once and caches it.
	 *
	 * @return Image
	 */
	Storage.prototype.getDefaultPhoto = function() {
		console.log('Storage.getDefaultPhoto');
		if(!this.defaultPhoto) {
			var defer = $.Deferred();
			var url = OC.imagePath('contacts', 'person_large.png');
			this.defaultPhoto = new Image();
			var self = this;
			$(this.defaultPhoto)
				.load(function() {
					defer.resolve(this);
				}).error(function(event) {
					defer.reject();
				}).attr('src', url)
			return defer.promise();
		} else {
			return this.defaultPhoto;
		}
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
			{backend: backend, addressbookid: addressbookid, contactid: contactid},
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
			{backend: backend, addressbookid: addressbookid, contactid: contactid},
			params
		);
	}

	/**
	 * Save all properties. Used when merging contacts.
	 *
	 * @param string backend
	 * @param string addressbookid Address book ID
	 * @param string contactid Contact ID
	 * @param object params An object with the all properties:
	 */
	Storage.prototype.saveAllProperties = function(backend, addressbookid, contactid, params) {
		console.log('Storage.saveAllProperties', params);
		return this.requestRoute(
			'contacts_contact_save_all',
			'POST',
			{backend: backend, addressbookid: addressbookid, contactid: contactid},
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
			{}
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
			{},
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
			{},
			{name: name}
		);
	}

	/**
	 * Add contacts to a group
	 *
	 * @param array contactids
	 */
	Storage.prototype.addToGroup = function(contactids, categoryid) {
		console.log('Storage.addToGroup', contactids, categoryid);
		return this.requestRoute(
			'contacts_categories_addto',
			'POST',
			{categoryid: categoryid},
			{contactids: contactids}
		);
	}

	/**
	 * Remove contacts from a group
	 *
	 * @param array contactids
	 */
	Storage.prototype.removeFromGroup = function(contactids, categoryid) {
		console.log('Storage.addToGroup', contactids, categoryid);
		return this.requestRoute(
			'contacts_categories_removefrom',
			'POST',
			{categoryid: categoryid},
			{contactids: contactids}
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
			{},
			{key: key, value:value}
		);
	}

	Storage.prototype.startImport = function(backend, addressbookid, params) {
		console.log('Storage.startImport', backend, addressbookid);
		return this.requestRoute(
			'contacts_import_start',
			'POST',
			{backend: backend, addressbookid: addressbookid},
			params
		);
	}

	Storage.prototype.importStatus = function(backend, addressbookid, params) {
		return this.requestRoute(
			'contacts_import_status',
			'POST',
			{backend: backend, addressbookid: addressbookid},
			params
		);
	}

	Storage.prototype.requestRoute = function(route, type, routeParams, params) {
		var isJSON = (typeof params === 'string');
		var contentType = isJSON ? 'application/json' : 'application/x-www-form-urlencoded';
		var processData = !isJSON;
		contentType += '; charset=UTF-8';
		var self = this;
		var url = OC.Router.generate(route, routeParams);
		var ajaxParams = {
			type: type,
			url: url,
			dataType: 'json',
			ifModified: true,
			contentType: contentType,
			processData: processData,
			data: params
		};

		var defer = $.Deferred();

		var jqxhr = $.ajax(ajaxParams)
			.done(function(response, textStatus, jqXHR) {
				defer.resolve(new JSONResponse(response, jqXHR));
			})
			.fail(function(jqxhr, textStatus, error) {
				defer.reject(
					new JSONResponse({
						error:true,
						data:{message:t('contacts', 'Request failed: {error}', {error:textStatus + ', ' + error})}
					}, jqXHR)
				);
			});

		return defer.promise();
	}

	OC.Contacts.Storage = Storage;

})(window, jQuery, OC);
