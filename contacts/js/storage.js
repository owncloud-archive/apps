OC.Contacts = OC.Contacts || {};


(function(window, $, OC) {
	'use strict';
	/**
	* An object for saving contact data to backends
	*/
	var Storage = function() {
	}

	/**
	 * Get all address books registered for this user.
	 *
	 * @param string user The user to query for. Default to current user
	 * @param function cb Callback function returning the data
	 * @return array An array containing address book metadata e.g.:
	 * {
	 * 	backend:'database',
	 * 	id:'1234'
	 * 	permissions:31,
	 * 	displayname:'Contacts'
	 * }
	 */
	Storage.prototype.getAddressBooksForUser = function(user, cb) {
		user = user ? user : OC.currentUser;
	}

	OC.Contacts.Storage = Storage;

})(window, jQuery, OC);
