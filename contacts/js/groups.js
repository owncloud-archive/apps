OC.Contacts = OC.Contacts || {};


(function(window, $, OC) {
	'use strict';

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

	GroupList.prototype.nameById = function(id) {
		return this.findById(id).contents().filter(function(){ return(this.nodeType == 3); }).text().trim();
	};

	GroupList.prototype.findById = function(id) {
		return this.$groupList.find('h3[data-id="' + id + '"]');
	};

	GroupList.prototype.isFavorite = function(contactid) {
		return this.inGroup(contactid, 'fav');
	};

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

	GroupList.prototype.inGroup = function(contactid, groupid) {
		var $groupelem = this.findById(groupid);
		var contacts = $groupelem.data('contacts');
		return (contacts.indexOf(contactid) !== -1);
	};

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
	* @param contactid An integer id or an array of integer ids.
	* @param groupid The integer id of the group
	* @param cb Optional call-back function
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

	GroupList.prototype.removeFromAll = function(contactid, alsospecial) {
		var self = this;
		var selector = alsospecial ? 'h3' : 'h3[data-type="category"]';
		$.each(this.$groupList.find(selector), function(i, group) {
			self.removeFrom(contactid, $(this).data('id'));
		});
	};

	GroupList.prototype.categoriesChanged = function(newcategories) {
		console.log('GroupList.categoriesChanged, I should do something');
	};

	GroupList.prototype.contactDropped = function(event, ui) {
		var dragitem = ui.draggable, droptarget = $(this);
		console.log('dropped', dragitem);
		if(dragitem.is('tr')) {
			console.log('tr dropped', dragitem.data('id'), 'on', $(this).data('id'));
			if($(this).data('type') === 'fav') {
				$(this).data('obj').setAsFavorite(dragitem.data('id'), true);
			} else {
				$(this).data('obj').addTo(dragitem.data('id'), $(this).data('id'));
			}
		}
	};

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

	GroupList.prototype.editGroup = function(id) {
		var self = this;
		if(this.$editelem) {
			console.log('Already editing, returning');
			return;
		}
		// NOTE: Currently this only works for adding, not renaming
		var saveChanges = function($elem, $input) {
			console.log('saveChanges', $input.val());
			var name = $input.val().trim();
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

	GroupList.prototype.addGroup = function(params, cb) {
		console.log('GroupList.addGroup', params.name);
		var name = params.name;
		contacts = []; // $.map(contacts, function(c) {return parseInt(c)});
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
		var acceptdrop = 'tr.contact';
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
