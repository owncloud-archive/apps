OC.Contacts = OC.Contacts || {};


(function(window, $, OC) {
	'use strict';

	/**
	 * GroupList object
	 * Currently all data but the categories array is saved in the jquery DOM elements.
	 * This may change in the future.
	 * Each group element has a data-id and a data-type attribute. data-type can be
	 * 'fav' for favorites, 'all' for all elements, 'category' for group and 'shared' for
	 * a shared addressbook.
	 * data-id can be 'fav', 'all' or a numeric group or addressbook id.
	 * In addition each elements holds data entries for:
	 *   'contacts': An array of contact ids belonging to that group
	 *   'obj': A reference to the groupList object.
	 */
	var GroupList = function(groupList, listItemTmpl) {
		this.$groupList = groupList;
		var self = this;
		var numtypes = ['category', 'fav', 'all'];
		this.$groupList.on('click', 'h3', function(event) {
			$('.tipsy').remove();
			if(wrongKey(event)) {
				return;
			}
			console.log($(event.target));
			if($(event.target).is('.action.delete')) {
				var id = $(event.target).parents('h3').first().data('id');
				self.deleteGroup(id, function(response) {
					if(response.status !== 'success') {
						OC.notify({message:response.data.message});
					}
				});
			} else {
				self.selectGroup({element:$(this)});
			}
		});

		this.$groupListItemTemplate = listItemTmpl;
		this.categories = [];
	};

	/**
	 * Set a group as being currently selected
	 * 
	 * @param object params. A map containing either a group id
	 * or a jQuery group element.
	 * This triggers a 'status.group.selected' event unless if
	 * the group hasn't been saved/created yet.
	 */
	GroupList.prototype.selectGroup = function(params) {
		var id, $elem;
		if(typeof params.id !== 'undefined') {
			id = params.id;
			$elem = this.findById(id);
		} else if(typeof params.element !== 'undefined') {
			id = params.element.data('id');
			$elem = params.element;
		}
		if(!$elem) {
			self.selectGroup('all');
			return;
		}
		console.log('selectGroup', id, $elem);
		this.$groupList.find('h3').removeClass('active');
		$elem.addClass('active');
		if(id === 'new') {
			return;
		}
		this.lastgroup = id;
		$(document).trigger('status.group.selected', {
			id: this.lastgroup,
			type: $elem.data('type'),
			contacts: $elem.data('contacts')
		});
	};

	/**
	 * Get the group name by id.
	 * 
	 * Kind of a hack. Need to get the immidiate text without the enclosed spans with number etc.
	 * Not sure this works in IE8 and maybe others.
	 * 
	 * @param integer id. The numeric group or addressbook id.
	 * @returns string The name of the group.
	 */
	GroupList.prototype.nameById = function(id) {
		return $.trim(this.findById(id).clone().find("*").remove().end().text()); //.contents().filter(function(){ return(this.nodeType == 3); }).text().trim();
	};

	/** Get the group element by id.
	 * 
	 * @param integer id. The numeric group or addressbook id.
	 * @returns object The jQuery object.
	 */
	GroupList.prototype.findById = function(id) {
		return this.$groupList.find('h3[data-id="' + id + '"]');
	};

	/**
	 * Check if a contact is favorited.
	 * @param integer contactid.
	 * @returns boolean.
	 */
	GroupList.prototype.isFavorite = function(contactid) {
		return this.inGroup(contactid, 'fav');
	};

	/**
	 * Check if a contact is in a specfic group.
	 * @param integer contactid.
	 * @param integer groupid.
	 * @returns boolean.
	 */
	GroupList.prototype.inGroup = function(contactid, groupid) {
		var $groupelem = this.findById(groupid);
		var contacts = $groupelem.data('contacts');
		return (contacts.indexOf(contactid) !== -1);
	};

	/**
	 * Mark/unmark a contact as favorite.
	 * 
	 * @param integer contactid.
	 * @param boolean state.
	 * @param function cb. Optional callback function.
	 */
	GroupList.prototype.setAsFavorite = function(contactid, state, cb) {
		contactid = parseInt(contactid);
		var $groupelem = this.findById('fav');
		var contacts = $groupelem.data('contacts');
		if(state) {
			OCCategories.addToFavorites(contactid, 'contact', function(jsondata) {
				if(jsondata.status === 'success') {
					contacts.push(contactid);
					$groupelem.data('contacts', contacts);
					$groupelem.find('.numcontacts').text(contacts.length);
					if(contacts.length > 0 && $groupelem.is(':hidden')) {
						$groupelem.show();
					}
				}
				if(typeof cb === 'function') {
					cb(jsondata);
				} else if(jsondata.status !== 'success') {
					OC.notify({message:t('contacts', jsondata.data.message)});
				}
			});
		} else {
			OCCategories.removeFromFavorites(contactid, 'contact', function(jsondata) {
				if(jsondata.status === 'success') {
					contacts.splice(contacts.indexOf(contactid), 1);
					//console.log('contacts', contacts, contacts.indexOf(id), contacts.indexOf(String(id)));
					$groupelem.data('contacts', contacts);
					$groupelem.find('.numcontacts').text(contacts.length);
					if(contacts.length === 0 && $groupelem.is(':visible')) {
						$groupelem.hide();
					}
				}
				if(typeof cb === 'function') {
					cb(jsondata);
				} else if(jsondata.status !== 'success') {
					OC.notify({message:t('contacts', jsondata.data.message)});
				}
			});
		}
	};

	/**
	* Add one or more contact ids to a group
	* @param integer|array contactid. An integer id or an array of integer ids.
	* @param integer groupid. The integer id of the group
	* @param function cb. Optional call-back function
	*/
	GroupList.prototype.addTo = function(contactid, groupid, cb) {
		console.log('GroupList.addTo', contactid, groupid);
		var $groupelem = this.findById(groupid);
		var contacts = $groupelem.data('contacts');
		var ids = [];
		if(!contacts) {
			console.log('Contacts not found, adding list!!!');
			contacts = [];
		}
		var self = this;
		var doPost = false;
		if(typeof contactid === 'number') {
			if(contacts.indexOf(contactid) === -1) {
				ids.push(contactid);
				doPost = true;
			} else {
				if(typeof cb == 'function') {
					cb({status:'error', message:t('contacts', 'Contact is already in this group.')});
				}
			}
		} else if(utils.isArray(contactid)) {
			$.each(contactid, function(i, id) {
				if(contacts.indexOf(id) === -1) {
					ids.push(id);
				}
			});
			if(ids.length > 0) {
				doPost = true;
			} else {
				if(typeof cb == 'function') {
					cb({status:'error', message:t('contacts', 'Contacts are already in this group.')});
				}
			}
		} else {
			console.warn('Invalid data type: ' + typeof contactid);
		}
		if(doPost) {
			$.post(OC.filePath('contacts', 'ajax', 'categories/addto.php'), {contactids: ids, categoryid: groupid},function(jsondata) {
				if(!jsondata) {
					if(typeof cb === 'function') {
						cb({status:'error', message:'Network or server error. Please inform administrator.'});
					}
					return;
				}
				if(jsondata.status === 'success') {
					contacts = contacts.concat(ids).sort();
					$groupelem.data('contacts', contacts);
					var $numelem = $groupelem.find('.numcontacts');
					$numelem.text(contacts.length).switchClass('', 'active', 200);
					setTimeout(function() {
						$numelem.switchClass('active', '', 1000);
					}, 2000);
					if(typeof cb === 'function') {
						cb({status:'success', ids:ids});
					} else {
						$(document).trigger('status.group.contactadded', {
							contactid: contactid,
							groupid: groupid,
							groupname: self.nameById(groupid)
						});
					}
				} else {
					if(typeof cb == 'function') {
						cb({status:'error', message:jsondata.data.message});
					}
				}
			});
		}
	};

	/**
	* Removes one or more contact ids from a group
	* @param integer|array contactid. An integer id or an array of integer ids.
	* @param integer groupid. The integer id of the group
	* @param function cb. Optional call-back function
	*/
	GroupList.prototype.removeFrom = function(contactid, groupid, cb) {
		console.log('GroupList.removeFrom', contactid, groupid);
		var $groupelem = this.findById(groupid);
		var contacts = $groupelem.data('contacts');
		var ids = [];

		// If it's the 'all' group simply decrement the number
		if(groupid === 'all') {
			var $numelem = $groupelem.find('.numcontacts');
			$numelem.text(parseInt($numelem.text()-1)).switchClass('', 'active', 200);
			setTimeout(function() {
				$numelem.switchClass('active', '', 1000);
			}, 2000);
			if(typeof cb === 'function') {
				cb({status:'success', ids:[id]});
			}
		}
		// If the contact is in the category remove it from internal list.
		if(!contacts) {
			if(typeof cb === 'function') {
				cb({status:'error', message:t('contacts', 'Couldn\'t get contact list.')});
			}
			return;
		}
		var doPost = false;
		if(typeof contactid === 'number') {
			if(contacts.indexOf(contactid) !== -1) {
				ids.push(contactid);
				doPost = true;
			} else {
				if(typeof cb == 'function') {
					cb({status:'error', message:t('contacts', 'Contact is not in this group.')});
				}
			}
		} else if(utils.isArray(contactid)) {
			$.each(contactid, function(i, id) {
				if(contacts.indexOf(id) !== -1) {
					ids.push(id);
				}
			});
			if(ids.length > 0) {
				doPost = true;
			} else {
				console.log(contactid, 'not in', contacts);
				if(typeof cb == 'function') {
					cb({status:'error', message:t('contacts', 'Contacts are not in this group.')});
				}
			}
		}
		if(doPost) {
			$.post(OC.filePath('contacts', 'ajax', 'categories/removefrom.php'), {contactids: ids, categoryid: groupid},function(jsondata) {
				if(!jsondata) {
					if(typeof cb === 'function') {
						cb({status:'error', message:'Network or server error. Please inform administrator.'});
					}
					return;
				}
				if(jsondata.status === 'success') {
					$.each(ids, function(idx, id) {
						contacts.splice(contacts.indexOf(id), 1);
					});
					//console.log('contacts', contacts, contacts.indexOf(id), contacts.indexOf(String(id)));
					$groupelem.data('contacts', contacts);
					var $numelem = $groupelem.find('.numcontacts');
					$numelem.text(contacts.length).switchClass('', 'active', 200);
					setTimeout(function() {
						$numelem.switchClass('active', '', 1000);
					}, 2000);
					if(typeof cb === 'function') {
						cb({status:'success', ids:ids});
					}
				} else {
					if(typeof cb == 'function') {
						cb({status:'error', message:jsondata.data.message});
					}
				}
			});
		}
	};

	/**
	 * Remove a contact from all groups. Used on contact deletion.
	 * 
	 * @param integer contactid.
	 * @param boolean alsospecial. Whether the contact should also be
	 *    removed from non 'category' groups.
	 */
	GroupList.prototype.removeFromAll = function(contactid, alsospecial) {
		var self = this;
		var selector = alsospecial ? 'h3' : 'h3[data-type="category"]';
		$.each(this.$groupList.find(selector), function(i, group) {
			self.removeFrom(contactid, $(this).data('id'));
		});
	};

	/**
	 * Handler that will be called by OCCategories if any groups have changed.
	 * This is called when categories are edited by the generic categories edit
	 * dialog, and will probably not be used in this app.
	 */
	GroupList.prototype.categoriesChanged = function(newcategories) {
		console.log('GroupList.categoriesChanged, I should do something');
	};

	/**
	 * Drop handler for for adding contact to group/favorites.
	 * FIXME: The drag helper object goes below the group elements
	 * during drag, and the drop target is hard to hit.
	 */
	GroupList.prototype.contactDropped = function(event, ui) {
		var dragitem = ui.draggable, droptarget = $(this);
		console.log('dropped', dragitem);
		if(dragitem.is('td.name')) {
			var id = dragitem.parent().data('id');
			console.log('td dropped', id, 'on', $(this).data('id'));
			if($(this).data('type') === 'fav') {
				$(this).data('obj').setAsFavorite(id, true);
			} else {
				$(this).data('obj').addTo(id, $(this).data('id'));
			}
		}
	};

	/**
	 * Remove a group from backend.
	 * 
	 * On success this triggers a 'status.group.groupremoved' event with an object
	 * containing the properties:
	 * 
	 *   groupid: The numeric id of the removed group
	 *   groupname: The string value of the group.
	 *   newgroupid: The id of the group that is selected after deletion.
	 *   contacts: An array of integer ids of contacts that must updated.
	 * 
	 * The handler for that event must take care of updating all contact objects
	 * internal CATEGORIES value and saving them to backend.
	 * 
	 * @param integer groupid.
	 * @param function cb. Optional callback function.
	 */
	GroupList.prototype.deleteGroup = function(groupid, cb) {
		var $elem = this.findById(groupid);
		var $newelem = $elem.prev('h3');
		var name = this.nameById(groupid);
		var contacts = $elem.data('contacts');
		var self = this;
		console.log('delete group', groupid, contacts);
		$.post(OC.filePath('contacts', 'ajax', 'categories/delete.php'), {categories: name}, function(jsondata) {
			if (jsondata && jsondata.status == 'success') {
				$(document).trigger('status.group.groupremoved', {
					groupid: groupid,
					newgroupid: parseInt($newelem.data('id')),
					groupname: self.nameById(groupid),
					contacts: contacts
				});
				$elem.remove();
				self.selectGroup({element:$newelem});
			} else {
				//
			}
			if(typeof cb === 'function') {
				cb(jsondata);
			}
		});
	};

	/**
	 * Edit a groups name.
	 * Currently only used for adding, as renaming a group also
	 * requires updating all contacts in that group.
	 * 
	 * @param integer id. Group id NOTE: Not used yet.
	 * FIXME: This works fine for adding, but will need refactoring
	 * if used for renaming.
	 */
	GroupList.prototype.editGroup = function(id) {
		var self = this;
		if(this.$editelem) {
			console.log('Already editing, returning');
			return;
		}
		// NOTE: Currently this only works for adding, not renaming
		var saveChanges = function($elem, $input) {
			console.log('saveChanges', $input.val());
			var name = $.trim($input.val());
			if(name.length === 0) {
				return false;
			}
			$input.prop('disabled', true);
			$elem.data('name', '');
			self.addGroup({name:name, element:$elem}, function(response) {
				if(response.status === 'success') {
					$elem.prepend(name).removeClass('editing').attr('data-id', response.id);
					$input.next('.checked').remove();
					$input.remove();
					self.$editelem = null;
				} else {
					$input.prop('disabled', false);
					OC.notify({message:response.message});
				}
			});
		};

		if(typeof id === 'undefined') {
			// Add new group
			var tmpl = this.$groupListItemTemplate;
			self.$editelem = (tmpl).octemplate({
				id: 'new',
				type: 'category',
				num: 0,
				name: ''
			});
			var $input = $('<input type="text" class="active" /><a class="action checked disabled" />');
			self.$editelem.prepend($input).addClass('editing');
			self.$editelem.data('contacts', []);
			this.$groupList.find('h3.group[data-type="category"]').first().before(self.$editelem);
			this.selectGroup({element:self.$editelem});
			$input.on('input', function(event) {
				if($(this).val().length > 0) {
					$(this).next('.checked').removeClass('disabled');
				} else {
					$(this).next('.checked').addClass('disabled');
				}
			});
			$input.on('keyup', function(event) {
				var keyCode = Math.max(event.keyCode, event.which);
				if(keyCode === 13) {
					saveChanges(self.$editelem, $(this));
				} else if(keyCode === 27) {
					self.$editelem.remove();
					self.$editelem = null;
				}
			});
			$input.next('.checked').on('click keydown', function(event) {
				console.log('clicked', event);
				if(wrongKey(event)) {
					return;
				}
				saveChanges(self.$editelem, $input);
			});
			$input.focus();
		} else if(utils.isUInt(id)) {
			alert('Renaming groups is not implemented!');
			return;
			var $elem = this.findById(id);
			var $text = $elem.contents().filter(function(){ return(this.nodeType == 3); });
			var name = $text.text();
			console.log('Group name', $text, name);
			$text.remove();
			var $input = $('<input type="text" class="active" value="' + name + '" /><a class="action checked disabled />');
			$elem.prepend($input).addClass('editing');
			$input.focus();

		} else {
			throw { name: 'WrongParameterType', message: 'GroupList.editGroup only accept integers.'};
		}
	};

	/**
	 * Add a new group.
	 * 
	 * After the addition a group element will be inserted in the list of group
	 * elements with data-type="category".
	 * NOTE: The element is inserted (semi) alphabetically, but since group elements
	 * can now be rearranged by dragging them it should probably be dropped.
	 * 
	 * @param object params. Map that can have the following properties:
	 *   'name': Mandatory. If a group with the same name already exists
	 *       (not case sensitive) the callback will be called with its 'status'
	 *       set to 'error' and the function returns.
	 *   'element': A jQuery group element. If this property isn't present
	 *       a new element will be created.
	 * @param function cb. On success the only parameter is an object with
	 *    'status': 'success', id: new id from the backend and 'name' the group name.
	 *     On error 'status' will be 'error' and 'message' will hold any error message
	 *     from the backend.
	 */
	GroupList.prototype.addGroup = function(params, cb) {
		console.log('GroupList.addGroup', params.name);
		var name = params.name;
		var contacts = []; // $.map(contacts, function(c) {return parseInt(c)});
		var self = this, exists = false;
		self.$groupList.find('h3[data-type="category"]').each(function() {
			if ($(this).data('name').toLowerCase() === name.toLowerCase()) {
				exists = true;
				return false; //break out of loop
			}
		});
		if(exists) {
			if(typeof cb === 'function') {
				cb({status:'error', message:t('contacts', 'A group named {group} already exists', {group: name})});
			}
			return;
		}
		$.post(OC.filePath('contacts', 'ajax', 'categories/add.php'), {category: name}, function(jsondata) {
			if (jsondata && jsondata.status == 'success') {
				var tmpl = self.$groupListItemTemplate;
				var $elem = params.element
					? params.element
					: (tmpl).octemplate({
						id: jsondata.data.id,
						type: 'category',
						num: contacts.length,
						name: name
					});
				self.categories.push({id: jsondata.data.id, name: name});
				$elem.data('obj', self);
				$elem.data('contacts', contacts);
				$elem.data('name', name);
				$elem.data('id', jsondata.data.id);
				var added = false;
				self.$groupList.find('h3.group[data-type="category"]').each(function() {
					if ($(this).data('name').toLowerCase().localeCompare(name.toLowerCase()) > 0) {
						$(this).before($elem);
						added = true;
						return false;
					}
				});
				if(!added) {
					$elem.insertAfter(self.$groupList.find('h3.group[data-type="category"]').last());
				}
				self.selectGroup({element:$elem});
				$elem.tipsy({trigger:'manual', gravity:'w', fallback: t('contacts', 'You can drag groups to\narrange them as you like.')});
				$elem.tipsy('show');
				if(typeof cb === 'function') {
					cb({status:'success', id:parseInt(jsondata.data.id), name:name});
				}
			} else {
				if(typeof cb === 'function') {
					cb({status:'error', message:jsondata.data.message});
				}
			}
		});
	};

	GroupList.prototype.loadGroups = function(numcontacts, cb) {
		var self = this;
		var acceptdrop = 'td.name';
		var $groupList = this.$groupList;
		var tmpl = this.$groupListItemTemplate;

		tmpl.octemplate({id: 'all', type: 'all', num: numcontacts, name: t('contacts', 'All')}).appendTo($groupList);
		$.getJSON(OC.filePath('contacts', 'ajax', 'categories/list.php'), {}, function(jsondata) {
			if (jsondata && jsondata.status == 'success') {
				self.lastgroup = jsondata.data.lastgroup;
				self.sortorder = jsondata.data.sortorder.length > 0
					? $.map(jsondata.data.sortorder.split(','), function(c) {return parseInt(c);})
					: [];
				console.log('sortorder', self.sortorder);
				// Favorites
				var contacts = $.map(jsondata.data.favorites, function(c) {return parseInt(c);});
				var $elem = tmpl.octemplate({
					id: 'fav',
					type: 'fav',
					num: contacts.length,
					name: t('contacts', 'Favorites')
				}).appendTo($groupList);
				$elem.data('obj', self);
				$elem.data('contacts', contacts).find('.numcontacts').before('<span class="starred action" />');
				$elem.droppable({
							drop: self.contactDropped,
							activeClass: 'ui-state-active',
							hoverClass: 'ui-state-hover',
							accept: acceptdrop
						});
				if(contacts.length === 0) {
					$elem.hide();
				}
				console.log('favorites', $elem.data('contacts'));
				// Normal groups
				$.each(jsondata.data.categories, function(c, category) {
					var contacts = $.map(category.contacts, function(c) {return parseInt(c);});
					var $elem = (tmpl).octemplate({
						id: category.id,
						type: 'category',
						num: contacts.length,
						name: category.name
					});
					self.categories.push({id: category.id, name: category.name});
					$elem.data('obj', self);
					$elem.data('contacts', contacts);
					$elem.data('name', category.name);
					$elem.data('id', category.id);
					$elem.droppable({
									drop: self.contactDropped,
									activeClass: 'ui-state-hover',
									accept: acceptdrop
								});
					$elem.appendTo($groupList);
				});

				var elems = $groupList.find('h3[data-type="category"]').get();

				elems.sort(function(a, b) {
					return self.sortorder.indexOf(parseInt($(a).data('id'))) > self.sortorder.indexOf(parseInt($(b).data('id')));
				});

				$.each(elems, function(index, elem) {
					$groupList.append(elem);
				});

				// Shared addressbook
				$.each(jsondata.data.shared, function(c, shared) {
					var sharedindicator = '<img class="shared svg" src="' + OC.imagePath('core', 'actions/shared') + '"'
						+ 'title="' + t('contacts', 'Shared by {owner}', {owner:shared.userid}) + '" />';
					var $elem = (tmpl).octemplate({
						id: shared.id,
						type: 'shared',
						num: '', //jsondata.data.shared.length,
						name: shared.displayname
					});
					$elem.find('.numcontacts').after(sharedindicator);
					$elem.data('obj', self);
					$elem.data('name', shared.displayname);
					$elem.data('id', shared.id);
					$elem.appendTo($groupList);
				});
				$groupList.sortable({
					items: 'h3[data-type="category"]',
					stop: function() {
						console.log('stop sorting', $(this));
						var ids = [];
						$.each($(this).children('h3[data-type="category"]'), function(i, elem) {
							ids.push($(elem).data('id'));
						});
						self.sortorder = ids;
						$(document).trigger('status.groups.sorted', {
							sortorder: self.sortorder.join(',')
						});
					}
				});
				var $elem = self.findById(self.lastgroup);
				$elem.addClass('active');
				$(document).trigger('status.group.selected', {
					id: self.lastgroup,
					type: $elem.data('type'),
					contacts: $elem.data('contacts')
				});
			} // TODO: else
			if(typeof cb === 'function') {
				cb();
			}
		});
	};

	OC.Contacts.GroupList = GroupList;

})(window, jQuery, OC);
