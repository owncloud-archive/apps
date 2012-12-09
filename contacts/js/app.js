var utils = {};

/**
 * utils.isArray
 *
 * Best guess if object is an array.
 */
utils.isArray = function(obj) {
     // do an instanceof check first
     if (obj instanceof Array) {
         return true;
     }
     // then check for obvious falses
     if (typeof obj !== 'object') {
         return false;
     }
     if (utils.type(obj) === 'array') {
         return true;
     }
     return false;
};

utils.isInt = function(s) {
  return typeof s === 'number' && (s.toString().search(/^-?[0-9]+$/) === 0);
}

utils.isUInt = function(s) {
  return typeof s === 'number' && (s.toString().search(/^[0-9]+$/) === 0);
}

/**
 * utils.type
 *
 * Attempt to ascertain actual object type.
 */
utils.type = function(obj) {
    if (obj === null || typeof obj === 'undefined') {
        return String (obj);
    }
    return Object.prototype.toString.call(obj)
        .replace(/\[object ([a-zA-Z]+)\]/, '$1').toLowerCase();
};

utils.moveCursorToEnd = function(el) {
	if (typeof el.selectionStart === 'number') {
		el.selectionStart = el.selectionEnd = el.value.length;
	} else if (typeof el.createTextRange !== 'undefined') {
		el.focus();
		var range = el.createTextRange();
		range.collapse(false);
		range.select();
	}
}

if (typeof Object.create !== 'function') {
	Object.create = function (o) {
		function F() {}
		F.prototype = o;
		return new F();
	};
}

Array.prototype.clone = function() {
  return this.slice(0);
};

Array.prototype.clean = function(deleteValue) {
	var arr = this.clone();
	for (var i = 0; i < arr.length; i++) {
		if (arr[i] == deleteValue) {
			arr.splice(i, 1);
			i--;
		}
	}
	return arr;
};

// Keep it DRY ;)
var wrongKey = function(event) {
	return (event.type === 'keydown' && (event.keyCode !== 32 && event.keyCode !== 13));
}

/**
 * Simply notifier
 * Arguments:
 * @param message The text message to show.
 * @param timeout The timeout in seconds before the notification disappears. Default 10.
 * @param timeouthandler A function to run on timeout.
 * @param clickhandler A function to run on click. If a timeouthandler is given it will be cancelled on click.
 * @param data An object that will be passed as argument to the timeouthandler and clickhandler functions.
 * @param cancel If set cancel all ongoing timer events and hide the notification.
 */
OC.notify = function(params) {
	var self = this;
	if(!self.notifier) {
		self.notifier = $('#notification');
		if(!self.notifier.length) {
			$('#content').prepend('<div id="notification" />');
			self.notifier = $('#notification');
		}
	}
	if(params.cancel) {
		self.notifier.off('click');
		for(var id in self.notifier.data()) {
			if($.isNumeric(id)) {
				clearTimeout(parseInt(id));
			}
		}
		self.notifier.text('').fadeOut().removeData();
		return;
	}
	self.notifier.text(params.message);
	self.notifier.fadeIn();
	self.notifier.on('click', function() { $(this).fadeOut();});
	var timer = setTimeout(function() {
		/*if(!self || !self.notifier) {
			var self = OC.Contacts;
			self.notifier = $('#notification');
		}*/
		self.notifier.fadeOut();
		if(params.timeouthandler && $.isFunction(params.timeouthandler)) {
			params.timeouthandler(self.notifier.data(dataid));
			self.notifier.off('click');
			self.notifier.removeData(dataid);
		}
	}, params.timeout && $.isNumeric(params.timeout) ? parseInt(params.timeout)*1000 : 10000);
	var dataid = timer.toString();
	if(params.data) {
		self.notifier.data(dataid, params.data);
	}
	if(params.clickhandler && $.isFunction(params.clickhandler)) {
		self.notifier.on('click', function() {
			/*if(!self || !self.notifier) {
				var self = OC.Contacts;
				self.notifier = $(this);
			}*/
			clearTimeout(timer);
			self.notifier.off('click');
			params.clickhandler(self.notifier.data(dataid));
			self.notifier.removeData(dataid);
		});
	}
}

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
			})
		} else {
			self.selectGroup({element:$(this)});
		}
	});

	this.$groupListItemTemplate = listItemTmpl;
	this.categories = [];
}

GroupList.prototype.nameById = function(id) {
	return this.findById(id).contents().filter(function(){ return(this.nodeType == 3); }).text().trim()
}

GroupList.prototype.findById = function(id) {
	return this.$groupList.find('h3[data-id="' + id + '"]');
}

GroupList.prototype.isFavorite = function(contactid) {
	return this.inGroup(contactid, 'fav');
}

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
		contacts: $elem.data('contacts'),
	});
}

GroupList.prototype.inGroup = function(contactid, groupid) {
	var $groupelem = this.findById(groupid);
	var contacts = $groupelem.data('contacts');
	return (contacts.indexOf(contactid) !== -1);
}

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
}

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
						groupname: self.nameById(groupid),
					});
				}
			} else {
				if(typeof cb == 'function') {
					cb({status:'error', message:jsondata.data.message});
				}
			}
		});
	}
}

GroupList.prototype.removeFrom = function(contactid, groupid, cb) {
	console.log('GroupList.removeFrom', contactid, groupid);
	var $groupelem = this.findById(groupid);
	var contacts = $groupelem.data('contacts');
	var ids = [];
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
}

GroupList.prototype.removeFromAll = function(contactid, alsospecial) {
	var self = this;
	var selector = alsospecial ? 'h3' : 'h3[data-type="category"]';
	$.each(this.$groupList.find(selector), function(i, group) {
		self.removeFrom(contactid, $(this).data('id'));
	});
}

GroupList.prototype.categoriesChanged = function(newcategories) {
	console.log('GroupList.categoriesChanged, I should do something');
}

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
}

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
				contacts: contacts,
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
}

GroupList.prototype.editGroup = function(id) {
	var self = this;
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
				$input.next('.checked').remove()
				$input.remove()
			} else {
				$input.prop('disabled', false);
				OC.notify({message:response.message});
			}
		});
	}
	
	if(typeof id === 'undefined') {
		// Add new group
		var tmpl = this.$groupListItemTemplate;
		var $elem = (tmpl).octemplate({
			id: 'new',
			type: 'category',
			num: 0,
			name: '',
		});
		var $input = $('<input type="text" class="active" /><a class="action checked disabled" />');
		$elem.prepend($input).addClass('editing');
		$elem.data('contacts', []);
		this.$groupList.find('h3.group[data-type="category"]').first().before($elem);
		this.selectGroup({element:$elem});
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
				saveChanges($elem, $(this));
			} else if(keyCode === 27) {
				$elem.remove();
			}
		});
		$input.next('.checked').on('click keydown', function(event) {
			console.log('clicked', event);
			if(wrongKey(event)) {
				return;
			}
			saveChanges($elem, $input);
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
		throw { name: 'WrongParameterType', message: 'GroupList.editGroup only accept integers.'}
	}
}

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
					name: name,
				})
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
}

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
				? $.map(jsondata.data.sortorder.split(','), function(c) {return parseInt(c)})
				: [];
			console.log('sortorder', self.sortorder);
			// Favorites
			var contacts = $.map(jsondata.data.favorites, function(c) {return parseInt(c)});
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
				var contacts = $.map(category.contacts, function(c) {return parseInt(c)});
				var $elem = (tmpl).octemplate({
					id: category.id,
					type: 'category',
					num: contacts.length,
					name: category.name,
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
					+ 'title="' + t('contacts', 'Shared by {owner}', {owner:shared.userid}) + '" />'
				var $elem = (tmpl).octemplate({
					id: shared.id,
					type: 'shared',
					num: '', //jsondata.data.shared.length,
					name: shared.displayname,
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
						ids.push($(elem).data('id'))
					})
					self.sortorder = ids;
					$(document).trigger('status.groups.sorted', {
						sortorder: self.sortorder.join(','),
					});
				},
			});
			var $elem = self.findById(self.lastgroup);
			$elem.addClass('active');
			$(document).trigger('status.group.selected', {
				id: self.lastgroup,
				type: $elem.data('type'),
				contacts: $elem.data('contacts'),
			});
		} // TODO: else
		if(typeof cb === 'function') {
			cb();
		}
	});
}

OC.Contacts = OC.Contacts || {
	init:function(id) {
		if(oc_debug === true) {
			$(document).ajaxError(function(e, xhr, settings, exception) {
				// Don't try to get translation because it's likely a network error.
				OC.notify({
					message: 'error in: ' + settings.url + ', '+'error: ' + xhr.responseText,
				});
			});
		}
		//if(id) {
			this.currentid = parseInt(id);
			console.log('init, id:', id);
		//}
		// Holds an array of {id,name} maps
		this.scrollTimeoutMiliSecs = 100;
		this.isScrolling = false;
		this.cacheElements();
		this.Contacts = new OC.Contacts.ContactList(
			this.$contactList,
			this.$contactListItemTemplate,
			this.$contactFullTemplate,
			this.detailTemplates
		);
		this.Groups = new GroupList(this.$groupList, this.$groupListItemTemplate);
		OCCategories.changed = this.Groups.categoriesChanged;
		OCCategories.app = 'contacts';
		OCCategories.type = 'contact';
		this.bindEvents();
		this.$toggleAll.show();
		this.showActions(['addcontact']);

		// Wait 2 mins then check if contacts are indexed.
		setTimeout(function() {
			if(!is_indexed) {
				OC.notify({message:t('contacts', 'Indexing contacts'), timeout:20});
				$.post(OC.filePath('contacts', 'ajax', 'indexproperties.php'));
			} else {
				console.log('contacts are indexed.');
			}
		}, 10000);
	},
	loading:function(obj, state) {
		$(obj).toggleClass('loading', state);
	},
	/**
	 * Show/hide elements in the header
	 * @param act An array of actions to show based on class name e.g ['add', 'delete']
	 */
	hideActions:function() {
		this.showActions(false);
	},
	showActions:function(act) {
		this.$headeractions.children().hide();
		if(act && act.length > 0) {
			this.$headeractions.children('.'+act.join(',.')).show();
		}
	},
	showAction:function(act, show) {
		this.$headeractions.find('.' + act).toggle(show);
	},
	cacheElements: function() {
		var self = this;
		this.detailTemplates = {};
		// Load templates for contact details.
		// The weird double loading is because jquery apparently doesn't
		// create a searchable object from a script element.
		$.each($($('#contactDetailsTemplate').html()), function(idx, node) {
			if(node.nodeType === Node.ELEMENT_NODE && node.nodeName === 'DIV') {
				var $tmpl = $(node.innerHTML);
				self.detailTemplates[$tmpl.data('element')] = $(node.outerHTML);
			}
		});
		this.$groupListItemTemplate = $('#groupListItemTemplate');
		this.$contactListItemTemplate = $('#contactListItemTemplate');
		this.$contactFullTemplate = $('#contactFullTemplate');
		this.$contactDetailsTemplate = $('#contactDetailsTemplate');
		this.$rightContent = $('#rightcontent');
		this.$header = $('#contactsheader');
		this.$headeractions = this.$header.find('div.actions');
		this.$groupList = $('#grouplist');
		this.$contactList = $('#contactlist');
		this.$contactListHeader = $('#contactlistheader');
		this.$toggleAll = $('#toggle_all');
		this.$groups = this.$headeractions.find('.groups');
		this.$ninjahelp = $('#ninjahelp');
		this.$firstRun = $('#firstrun');
		this.$settings = $('#contacts-settings');
		this.$importFileInput = $('#import_fileupload');
		this.$importIntoSelect = $('#import_into');
	},
	// Build the select to add/remove from groups.
	buildGroupSelect: function() {
		// If a contact is open we know which categories it's in
		if(this.currentid) {
			var contact = this.Contacts.contacts[this.currentid];
			this.$groups.find('optgroup,option:not([value="-1"])').remove();
			var addopts = '', rmopts = '';
			$.each(this.Groups.categories, function(i, category) {
				if(contact.inGroup(category.name)) {
					rmopts += '<option value="' + category.id + '">' + category.name + '</option>';
				} else {
					addopts += '<option value="' + category.id + '">' + category.name + '</option>';
				}
			});
			if(addopts.length) {
				$(addopts).appendTo(this.$groups)
				.wrapAll('<optgroup data-action="add" label="' + t('contacts', 'Add to...') + '"/>');
			}
			if(rmopts.length) {
				$(rmopts).appendTo(this.$groups)
				.wrapAll('<optgroup data-action="remove" label="' + t('contacts', 'Remove from...') + '"/>');
			}
		} else if(this.Contacts.getSelectedContacts().length > 0) { // Otherwise add all categories to both add and remove
			this.$groups.find('optgroup,option:not([value="-1"])').remove();
			var addopts = '', rmopts = '';
			$.each(this.Groups.categories, function(i, category) {
				rmopts += '<option value="' + category.id + '">' + category.name + '</option>';
				addopts += '<option value="' + category.id + '">' + category.name + '</option>';
			});
			$(addopts).appendTo(this.$groups)
				.wrapAll('<optgroup data-action="add" label="' + t('contacts', 'Add to...') + '"/>');
			$(rmopts).appendTo(this.$groups)
				.wrapAll('<optgroup data-action="remove" label="' + t('contacts', 'Remove from...') + '"/>');
		} else {
			// 3rd option: No contact open, none checked, just show "Add group..."
			this.$groups.find('optgroup,option:not([value="-1"])').remove();
		}
		$('<option value="add">' + t('contacts', 'Add group...') + '</option>').appendTo(this.$groups);
		this.$groups.val(-1);
	},
	bindEvents: function() {
		var self = this;

		// Should fix Opera check for delayed delete.
		$(window).unload(function (){
			$(window).trigger('beforeunload');
		});

		// App specific events
		$(document).bind('status.contact.deleted', function(e, data) {
			var id = parseInt(data.id);
			console.log('contact', data.id, 'deleted');
			// update counts on group lists
			self.Groups.removeFromAll(data.id, true)
		});

		$(document).bind('status.contact.added', function(e, data) {
			self.currentid = parseInt(data.id);
			self.buildGroupSelect();
			self.showActions(['back', 'download', 'delete', 'groups', 'favorite']);
		});

		$(document).bind('status.contact.error', function(e, data) {
			OC.notify({message:data.message});
		});

		$(document).bind('status.contact.enabled', function(e, enabled) {
			console.log('status.contact.enabled', enabled)
			if(enabled) {
				self.showActions(['back', 'download', 'delete', 'groups', 'favorite']);
			} else {
				self.showActions(['back']);
			}
			if(self.Groups.isFavorite(self.currentid)) {
				self.$header.find('.favorite').switchClass('inactive', 'active');
			} else {
				self.$header.find('.favorite').switchClass('active', 'inactive');
			}
		});

		$(document).bind('status.contacts.loaded', function(e, result) {
			console.log('status.contacts.loaded', result);
			if(result.status !== true) {
				alert('Error loading contacts!');
			} else {
				self.numcontacts = result.numcontacts;
				self.loading(self.$rightContent, false);
				self.Groups.loadGroups(self.numcontacts, function() {
					self.loading($('#leftcontent'), false);
					console.log('Groups loaded, currentid', self.currentid);
					if(self.currentid) {
						self.openContact(self.currentid);
					}
				});
			}
		});

		$(document).bind('status.contact.currentlistitem', function(e, result) {
			//console.log('status.contact.currentlistitem', result, self.$rightContent.height());
			if(self.dontScroll !== true) {
				if(result.pos > self.$rightContent.height()) {
					self.$rightContent.scrollTop(result.pos - self.$rightContent.height() + result.height);
				}
				else if(result.pos < self.$rightContent.offset().top) {
					self.$rightContent.scrollTop(result.pos);
				}
			} else {
				setTimeout(function() {
					self.dontScroll = false;
				}, 100);
			}
			self.currentlistid = result.id
		});

		$(document).bind('status.nomorecontacts', function(e, result) {
			console.log('status.nomorecontacts', result);
			self.$contactList.hide();
			self.$firstRun.show();
			// TODO: Show a first-run page.
		});

		$(document).bind('status.visiblecontacts', function(e, result) {
			console.log('status.visiblecontacts', result);
			// TODO: To be decided.
		});

		// A contact id was in the request
		$(document).bind('request.loadcontact', function(e, result) {
			console.log('request.loadcontact', result);
			if(self.numcontacts) {
				self.openContact(result.id);
			} else {
				// Contacts are not loaded yet, try again.
				console.log('waiting for contacts to load');
				setTimeout(function() {
					$(document).trigger('request.loadcontact', {
						id: result.id,
					});
				}, 1000);
			}
		});

		$(document).bind('request.select.contactphoto.fromlocal', function(e, result) {
			console.log('request.select.contactphoto.fromlocal', result);
			$('#contactphoto_fileupload').trigger('click');
		});

		$(document).bind('request.select.contactphoto.fromcloud', function(e, result) {
			console.log('request.select.contactphoto.fromcloud', result);
			OC.dialogs.filepicker(t('contacts', 'Select photo'), function(path) {
				self.cloudPhotoSelected(self.currentid, path);
			}, false, 'image', true);
		});

		$(document).bind('request.edit.contactphoto', function(e, result) {
			console.log('request.edit.contactphoto', result);
			self.editCurrentPhoto(result.id);
		});

		$(document).bind('request.addressbook.activate', function(e, result) {
			console.log('request.addressbook.activate', result);
			self.Contacts.showFromAddressbook(result.id, result.activate);
		});

		$(document).bind('status.contact.removedfromgroup', function(e, result) {
			console.log('status.contact.removedfromgroup', result);
			if(self.currentgroup == result.groupid) {
				self.Contacts.hideContact(result.contactid);
				self.closeContact(result.contactid);
			}
		});

		$(document).bind('status.group.groupremoved', function(e, result) {
			console.log('status.group.groupremoved', result);
			if(parseInt(result.groupid) === parseInt(self.currentgroup)) {
				console.time('hiding');
				self.Contacts.showContacts([]);
				console.timeEnd('hiding');
				self.currentgroup = 'all';
			}
			$.each(result.contacts, function(idx, contactid) {
				var contact = self.Contacts.findById(contactid);
				console.log('contactid', contactid, contact);
				
				self.Contacts.findById(contactid).removeFromGroup(result.groupname);
			});
		});

		$(document).bind('status.group.contactadded', function(e, result) {
			console.log('status.group.contactadded', result);
			self.Contacts.contacts[parseInt(result.contactid)].addToGroup(result.groupname);
		});

		// Group sorted, save the sort order
		$(document).bind('status.groups.sorted', function(e, result) {
			console.log('status.groups.sorted', result);
			$.post(OC.filePath('contacts', 'ajax', 'setpreference.php'), {'key':'groupsort', 'value':result.sortorder}, function(jsondata) {
				if(jsondata.status !== 'success') {
					OC.notify({message: jsondata ? jsondata.data.message : t('contacts', 'Network or server error. Please inform administrator.')});
				}
			});
		});
		// Group selected, only show contacts from that group
		$(document).bind('status.group.selected', function(e, result) {
			console.log('status.group.selected', result);
			self.currentgroup = result.id;
			// Close any open contact.
			if(self.currentid) {
				var id = self.currentid;
				self.closeContact(id);
				self.jumpToContact(id);
			}
			self.$contactList.show();
			self.$toggleAll.show();
			self.showActions(['addcontact']);
			if(result.type === 'category' ||  result.type === 'fav') {
				self.Contacts.showContacts(result.contacts);
			} else if(result.type === 'shared') {
				self.Contacts.showFromAddressbook(self.currentgroup, true, true);
			} else {
				self.Contacts.showContacts(self.currentgroup);
			}
			$.post(OC.filePath('contacts', 'ajax', 'setpreference.php'), {'key':'lastgroup', 'value':self.currentgroup}, function(jsondata) {
				if(!jsondata || jsondata.status !== 'success') {
					OC.notify({message: (jsondata && jsondata.data) ? jsondata.data.message : t('contacts', 'Network or server error. Please inform administrator.')});
				}
			});
			self.$rightContent.scrollTop(0);
		});
		// mark items whose title was hid under the top edge as read
		/*this.$rightContent.scroll(function() {
			// prevent too many scroll requests;
			if(!self.isScrolling) {
				self.isScrolling = true;
				var num = self.$contactList.find('tr').length;
				//console.log('num', num);
				var offset = self.$contactList.find('tr:eq(' + (num-20) + ')').offset().top;
				if(offset < self.$rightContent.height()) {
					console.log('load more');
					self.Contacts.loadContacts(num, function() {
						self.isScrolling = false;
					});
				} else {
					setTimeout(function() {
						self.isScrolling = false;
					}, self.scrollTimeoutMiliSecs);
				}
				//console.log('scroll, unseen:', offset, self.$rightContent.height());
			}
		});*/
		this.$settings.find('.settings').on('click keydown',function(event) {
			if(wrongKey(event)) {
				return;
			}
			var bodyListener = function(e) {
				if(self.$settings.find($(e.target)).length == 0) {
					self.$settings.switchClass('open', '');
				}
			}
			if(self.$settings.hasClass('open')) {
				self.$settings.switchClass('open', '');
				$('body').unbind('click', bodyListener);
			} else {
				self.$settings.switchClass('', 'open');
				$('body').bind('click', bodyListener);
			}
		});
		$('#contactphoto_fileupload').on('change', function() {
			self.uploadPhoto(this.files);
		});

		$('#groupactions > .addgroup').on('click keydown',function(event) {
			if(wrongKey(event)) {
				return;
			}
			self.Groups.editGroup();
			//self.addGroup();
		});

		this.$ninjahelp.find('.close').on('click keydown',function(event) {
			if(wrongKey(event)) {
				return;
			}
			self.$ninjahelp.hide();
		});

		this.$toggleAll.on('change', function() {
			var isChecked = $(this).is(':checked');
			self.setAllChecked(isChecked);
			if(self.$groups.find('option').length === 1) {
				self.buildGroupSelect();
			}
			if(isChecked) {
				self.showActions(['addcontact', 'groups', 'delete']);
			} else {
				self.showActions(['addcontact']);
			}
		});

		this.$contactList.on('change', 'input:checkbox', function(event) {
			if($(this).is(':checked')) {
				if(self.$groups.find('option').length === 1) {
					self.buildGroupSelect();
				}
				self.showActions(['addcontact', 'groups', 'delete']);
			} else if(self.Contacts.getSelectedContacts().length === 0) {
				self.showActions(['addcontact']);
			}
		});

		this.$groups.on('change', function() {
			var $opt = $(this).find('option:selected');
			var action = $opt.parent().data('action');
			var ids, groupName, groupId, buildnow = false;

			// If a contact is open the action is only applied to that,
			// otherwise on all selected items.
			if(self.currentid) {
				ids = [self.currentid,];
				buildnow = true
			} else {
				ids = self.Contacts.getSelectedContacts();
			}

			self.setAllChecked(false);
			self.$toggleAll.prop('checked', false);
			if(!self.currentid) {
				self.showActions(['addcontact']);
			}
			
			if($opt.val() === 'add') { // Add new group
				action = 'add';
				console.log('add group...');
				self.$groups.val(-1);
				self.addGroup(function(response) {
					if(response.status === 'success') {
						groupId = response.id;
						groupName = response.name;
						self.Groups.addTo(ids, groupId, function(result) {
							if(result.status === 'success') {
								$.each(ids, function(idx, id) {
									// Delay each contact to not trigger too many ajax calls
									// at a time.
									setTimeout(function() {
										self.Contacts.contacts[id].addToGroup(groupName);
										// I don't think this is used...
										if(buildnow) {
											self.buildGroupSelect();
										}
										$(document).trigger('status.contact.addedtogroup', {
											contactid: id,
											groupid: groupId,
											groupname: groupName,
										});
									}, 1000);
								});
							} else {
								// TODO: Use message return from Groups object.
								OC.notify({message:t('contacts', t('contacts', 'Error adding to group.'))});
							}
						});
					} else {
						OC.notify({message: response.message});
					}
				});
				return;
			}
			
			groupName = $opt.text(), groupId = $opt.val();

			console.log('trut', groupName, groupId);
			if(action === 'add') {
				self.Groups.addTo(ids, $opt.val(), function(result) {
					console.log('after add', result);
					if(result.status === 'success') {
						$.each(result.ids, function(idx, id) {
							// Delay each contact to not trigger too many ajax calls
							// at a time.
							setTimeout(function() {
								console.log('adding', id, 'to', groupName);
								self.Contacts.contacts[id].addToGroup(groupName);
								// I don't think this is used...
								if(buildnow) {
									self.buildGroupSelect();
								}
								$(document).trigger('status.contact.addedtogroup', {
									contactid: id,
									groupid: groupId,
									groupname: groupName,
								});
							}, 1000);
						});
					} else {
						var msg = result.message ? result.message : t('contacts', 'Error adding to group.');
						OC.notify({message:msg});
					}
				});
				if(!buildnow) {
					self.$groups.val(-1).hide().find('optgroup,option:not([value="-1"])').remove();
				}
			} else if(action === 'remove') {
				self.Groups.removeFrom(ids, $opt.val(), function(result) {
					console.log('after remove', result);
					if(result.status === 'success') {
						var groupname = $opt.text(), groupid = $opt.val();
						$.each(result.ids, function(idx, id) {
							self.Contacts.contacts[id].removeFromGroup(groupname);
							if(buildnow) {
								self.buildGroupSelect();
							}
							// If a group is selected the contact has to be removed from the list
							$(document).trigger('status.contact.removedfromgroup', {
								contactid: id,
								groupid: groupId,
								groupname: groupName,
							});
						});
					} else {
						var msg = result.message ? result.message : t('contacts', 'Error removing from group.');
						OC.notify({message:msg});
					}
				});
				if(!buildnow) {
					self.$groups.val(-1).hide().find('optgroup,option:not([value="-1"])').remove();
				}
			} // else something's wrong ;)
			self.setAllChecked(false);
		});

		// Contact list. Either open a contact or perform an action (mailto etc.)
		this.$contactList.on('click', 'tr', function(event) {
			if($(event.target).is('input')) {
				return;
			}
			if(event.ctrlKey || event.metaKey) {
				event.stopPropagation();
				event.preventDefault();
				console.log('select', event);
				self.dontScroll = true;
				self.Contacts.select($(this).data('id'), true);
				return;
			}
			if($(event.target).is('a.mailto')) {
				var mailto = 'mailto:' + $(this).find('.email').text().trim();
				console.log('mailto', mailto);
				try {
					window.location.href=mailto;
				} catch(e) {
					alert(t('contacts', 'There was an error opening a mail composer.'));
				}
				return;
			}
			self.openContact($(this).data('id'));
		});
		
		$('.addcontact').on('click keydown', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('add');
			self.$contactList.hide();
			self.$toggleAll.hide();
			$(this).hide();
			self.currentid = 'new';
			self.tmpcontact = self.Contacts.addContact();
			self.$rightContent.prepend(self.tmpcontact);
			self.showActions(['back']);
		});

		this.$settings.find('h3').on('click keydown', function(event) {
			if(wrongKey(event)) {
				return;
			}
			if($(this).next('ul').is(':visible')) {
				$(this).next('ul').slideUp();
				return;
			}
			console.log('settings');
			var $list = $(this).next('ul');
			if($(this).data('id') === 'addressbooks') {
				console.log('addressbooks');
				
				if(!self.$addressbookTmpl) {
					self.$addressbookTmpl = $('#addressbookTemplate');
				}

				$list.empty();
				$.each(self.Contacts.addressbooks, function(id, book) {
					var $li = self.$addressbookTmpl.octemplate({
						id: id, 
						permissions: book.permissions,
						displayname: book.displayname,
					});

					$list.append($li);
				});
				$list.find('a.action').tipsy();
				$list.find('a.action.delete').on('click keypress', function() {
					$('.tipsy').remove();
					var id = parseInt($(this).parents('li').first().data('id'));
					console.log('delete', id);
					var $li = $(this).parents('li').first();
					$.ajax({
						type:'POST',
						url:OC.filePath('contacts', 'ajax', 'addressbook/delete.php'), 
						data:{ id: id },
						success:function(jsondata) {
							console.log(jsondata);
							if(jsondata.status == 'success') {
								self.Contacts.unsetAddressbook(id);
								$li.remove();
								OC.notify({
									message:t('contacts','Deleting done. Click here to cancel reloading.'),
									timeout:5,
									timeouthandler:function() {
										console.log('reloading');
										window.location.href = OC.linkTo('contacts', 'index.php');
									},
									clickhandler:function() {
										console.log('reloading cancelled');
										OC.notify({cancel:true});
									}
								});
							} else {
								OC.notify({message:jsondata.data.message});
							}
						},
						error:function(jqXHR, textStatus, errorThrown) {
							OC.notify({message:textStatus + ': ' + errorThrown});
							id = false;
						},
					});
				});
				$list.find('a.action.globe').on('click keypress', function() {
					var id = parseInt($(this).parents('li').first().data('id'));
					var book = self.Contacts.addressbooks[id];
					var uri = (book.owner === oc_current_user ) ? book.uri : book.uri + '_shared_by_' + book.owner;
					var link = totalurl+'/'+encodeURIComponent(oc_current_user)+'/'+encodeURIComponent(uri);
					var $dropdown = $('<div id="dropdown" class="drop"><input type="text" value="' + link + '" /></div>');
					$dropdown.appendTo($(this).parents('li').first());
					var $input = $dropdown.find('input');
					$input.focus().get(0).select();
					$input.on('blur', function() {
						$dropdown.hide('blind', function() {
							$dropdown.remove();
						});
					});
				});
				OC.Share.loadIcons('addressbook');
			} else if($(this).data('id') === 'import') {
				console.log('import');
				$('.import-upload').show();
				$('.import-select').hide();

				var addAddressbookCallback = function(select, name) {
					var id;
					$.ajax({
						type:'POST',
						async:false,
						url:OC.filePath('contacts', 'ajax', 'addressbook/add.php'), 
						data:{ name: name },
						success:function(jsondata) {
							console.log(jsondata);
							if(jsondata.status == 'success') {
								self.Contacts.setAddressbook(jsondata.data.addressbook);
								id = jsondata.data.addressbook.id
							} else {
								OC.notify({message:jsondata.data.message});
							}
						},
						error:function(jqXHR, textStatus, errorThrown) {
							OC.notify({message:textStatus + ': ' + errorThrown});
							id = false;
						},
					});
					return id;
				}

				self.$importIntoSelect.empty();
				$.each(self.Contacts.addressbooks, function(id, book) {
					self.$importIntoSelect.append('<option value="' + id + '">' + book.displayname + '</option>');
				});
				self.$importIntoSelect.multiSelect({
					createCallback:addAddressbookCallback,
					singleSelect: true,
					createText:String(t('contacts', 'Add address book')),
					minWidth: 120,
				});

			}
			$(this).parents('ul').first().find('ul:visible').slideUp();
			$list.toggle('slow');
		});

		this.$header.on('click keydown', '.back', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('back');
			self.closeContact(self.currentid);
			self.$toggleAll.show();
		});

		this.$header.on('click keydown', '.delete', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('delete');
			if(self.currentid) {
				console.assert(utils.isUInt(self.currentid), 'self.currentid is not an integer');
				self.Contacts.delayedDelete(self.currentid);
			} else {
				self.Contacts.delayedDelete(self.Contacts.getSelectedContacts());
			}
			self.showActions(['addcontact']);
		});

		this.$header.on('click keydown', '.download', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('download');
			if(self.currentid) {
				document.location.href = OC.linkTo('contacts', 'export.php') + '?contactid=' + self.currentid;
			} else {
				console.log('currentid is not set');
			}
		});

		this.$header.on('click keydown', '.favorite', function(event) {
			if(wrongKey(event)) {
				return;
			}
			if(!utils.isUInt(self.currentid)) {
				return;
			}
			var state = self.Groups.isFavorite(self.currentid);
			console.log('Favorite?', this, state);
			self.Groups.setAsFavorite(self.currentid, !state, function(jsondata) {
				if(jsondata.status === 'success') {
					if(state) {
						self.$header.find('.favorite').switchClass('active', 'inactive');
					} else {
						self.$header.find('.favorite').switchClass('inactive', 'active');
					}
				} else {
					OC.notify({message:t('contacts', jsondata.data.message)});
				}
			});
		});

		this.$contactList.on('mouseenter', 'td.email', function(event) {
			if($(this).text().trim().length > 3) {
				$(this).find('.mailto').css('display', 'inline-block'); //.fadeIn(100);
			}
		});
		this.$contactList.on('mouseleave', 'td.email', function(event) {
			$(this).find('.mailto').fadeOut(100);
		});

		// Import using jquery.fileupload
		$(function() {
			var uploadingFiles = {}, numfiles = 0, uploadedfiles = 0, retries = 0;
			var aid, importError = false;
			var $progressbar = $('#import-progress');
			var $status = $('#import-status-text');

			var waitForImport = function() {
				if(numfiles == 0 && uploadedfiles == 0) {
					$progressbar.progressbar('value',100);
					if(!importError) {
						OC.notify({
							message:t('contacts','Import done. Click here to cancel reloading.'),
							//timeout:5,
							timeouthandler:function() {
								console.log('reloading');
								window.location.href = OC.linkTo('contacts', 'index.php');
							},
							clickhandler:function() {
								console.log('reloading cancelled');
								OC.notify({cancel:true});
							}
						});
					}
					retries = aid = 0;
					$progressbar.fadeOut();
					setTimeout(function() {
						$status.fadeOut('slow');
						$('.import-upload').show();
					}, 3000);
				} else {
					setTimeout(function() {
						waitForImport();
					}, 1000);
				}
			};
			var doImport = function(file, aid, cb) {
				$.post(OC.filePath('contacts', '', 'import.php'), { id: aid, file: file, fstype: 'OC_FilesystemView' },
					function(jsondata) {
						if(jsondata.status != 'success') {
							importError = true;
							OC.notify({message:jsondata.data.message});
						}
						if(typeof cb == 'function') {
							cb(jsondata);
						}
				});
				return false;
			};

			var importFiles = function(aid, uploadingFiles) {
				console.log('importFiles', aid, uploadingFiles);
				if(numfiles != uploadedfiles) {
					OC.notify({message:t('contacts', 'Not all files uploaded. Retrying...')});
					retries += 1;
					if(retries > 3) {
						numfiles = uploadedfiles = retries = aid = 0;
						uploadingFiles = {};
						$progressbar.fadeOut();
						OC.dialogs.alert(t('contacts', 'Something went wrong with the upload, please retry.'), t('contacts', 'Error'));
						return;
					}
					setTimeout(function() { // Just to let any uploads finish
						importFiles(aid, uploadingFiles);
					}, 1000);
				}
				$progressbar.progressbar('value', 50);
				var todo = uploadedfiles;
				$.each(uploadingFiles, function(fileName, data) {
					$status.text(t('contacts', 'Importing from {filename}...', {filename:fileName})).fadeIn();
					doImport(fileName, aid, function(response) {
						if(response.status === 'success') {
							$status.text(t('contacts', '{success} imported, {failed} failed.', 
								{success:response.data.imported, failed:response.data.failed})).fadeIn();
						}
						delete uploadingFiles[fileName];
						numfiles -= 1; uploadedfiles -= 1;
						$progressbar.progressbar('value',50+(50/(todo-uploadedfiles)));
					});
				})
				//$status.text(t('contacts', 'Importing...')).fadeIn();
				waitForImport();
			};

			// Start the actual import.
			$('.doImport').on('click keypress', function(event) {
				if(wrongKey(event)) {
					return;
				}
				aid = $(this).prev('select').val();
				$('.import-select').hide();
				importFiles(aid, uploadingFiles);
			});

			$('#import_fileupload').fileupload({
				acceptFileTypes:  /^text\/(directory|vcard|x-vcard)$/i,
				add: function(e, data) {
					var files = data.files;
					var totalSize=0;
					if(files) {
						numfiles += files.length; uploadedfiles = 0;
						for(var i=0;i<files.length;i++) {
							if(files[i].size ==0 && files[i].type== '') {
								OC.dialogs.alert(t('files', 'Unable to upload your file as it is a directory or has 0 bytes'), t('files', 'Upload Error'));
								return;
							}
							totalSize+=files[i].size;
						}
					}
					if(totalSize>$('#max_upload').val()) {
						OC.dialogs.alert(t('contacts','The file you are trying to upload exceed the maximum size for file uploads on this server.'), t('contacts','Upload too large'));
						numfiles = uploadedfiles = retries = aid = 0;
						uploadingFiles = {};
						return;
					} else {
						if($.support.xhrFileUpload) {
							$.each(files, function(i, file) {
								var fileName = file.name;
								console.log('file.name', file.name);
								var jqXHR =  $('#import_fileupload').fileupload('send', 
									{
									files: file,
									formData: function(form) {
										var formArray = form.serializeArray();
										formArray['aid'] = aid;
										return formArray;
									}})
									.success(function(response, textStatus, jqXHR) {
										if(response.status == 'success') {
											// import the file
											uploadedfiles += 1;
										} else {
											OC.notify({message:response.data.message});
										}
										return false;
									})
									.error(function(jqXHR, textStatus, errorThrown) {
										console.log(textStatus);
										OC.notify({message:errorThrown + ': ' + textStatus,});
									});
								uploadingFiles[fileName] = jqXHR;
							});
						} else {
							data.submit().success(function(data, status) {
								response = jQuery.parseJSON(data[0].body.innerText);
								if(response[0] != undefined && response[0].status == 'success') {
									var file=response[0];
									delete uploadingFiles[file.name];
									$('tr').filterAttr('data-file',file.name).data('mime',file.mime);
									var size = $('tr').filterAttr('data-file',file.name).find('td.filesize').text();
									if(size==t('files','Pending')){
										$('tr').filterAttr('data-file',file.name).find('td.filesize').text(file.size);
									}
									FileList.loadingDone(file.name);
								} else {
									OC.notify({message:response.data.message});
								}
							});
						}
					}
				},
				fail: function(e, data) {
					console.log('fail');
					OC.notify({message:data.errorThrown + ': ' + data.textStatus});
					// TODO: Remove file from upload queue.
				},
				progressall: function(e, data) {
					var progress = (data.loaded/data.total)*50;
					$progressbar.progressbar('value',progress);
				},
				start: function(e, data) {
					$progressbar.progressbar({value:0});
					$progressbar.fadeIn();
					if(data.dataType != 'iframe ') {
						$('#upload input.stop').show();
					}
				},
				stop: function(e, data) {
					console.log('stop, data', data);
					// stop only gets fired once so we collect uploaded items here.
					$('.import-upload').hide();
					$('.import-select').show();

					if(data.dataType != 'iframe ') {
						$('#upload input.stop').hide();
					}
				}
			})
		});
		
		$(document).on('keypress', function(event) {
			if(event.target.nodeName.toUpperCase() != 'BODY') {
				return;
			}
			var keyCode = Math.max(event.keyCode, event.which);
			// TODO: This should go in separate method
			console.log(event, keyCode + ' ' + event.target.nodeName);
			/**
			* To add:
			* Shift-a: add addressbook
			* u (85): hide/show leftcontent
			* f (70): add field
			*/
			switch(keyCode) {
				case 13: // Enter?
					console.log('Enter?');
					if(!self.currentid && self.currentlistid) {
						self.openContact(self.currentlistid);
					}
					break;
				case 27: // Esc
					if(self.$ninjahelp.is(':visible')) {
						self.$ninjahelp.hide();
					} else if(self.currentid) {
						self.closeContact(self.currentid);
					}
					break;
				case 46: // Delete
					if(event.shiftKey) {
						self.Contacts.delayedDelete(self.currentid);
					}
					break;
				case 40: // down
				case 74: // j
					console.log('next');
					if(!self.currentid && self.currentlistid) {
						self.Contacts.contacts[self.currentlistid].next();
					}
					break;
				case 65: // a
					if(event.shiftKey) {
						console.log('add group?');
						break;
					}
					self.addContact();
					break;
				case 38: // up
				case 75: // k
					console.log('previous');
					if(!self.currentid && self.currentlistid) {
						self.Contacts.contacts[self.currentlistid].prev();
					}
					break;
				case 34: // PageDown
				case 78: // n
					console.log('page down')
					break;
				case 79: // o
					console.log('open contact?');
					break;
				case 33: // PageUp
				case 80: // p
					// prev addressbook
					//OC.Contacts.Contacts.previousAddressbook();
					break;
				case 82: // r
					console.log('refresh - what?');
					break;
				case 63: // ? German.
					if(event.shiftKey) {
						self.$ninjahelp.toggle('fast');
					}
					break;
				case 171: // ? Danish
				case 191: // ? Standard qwerty
					self.$ninjahelp.toggle('fast').position({my: "center",at: "center",of: "#content"});
					break;
			}

		});

		$('#content > [title]').tipsy(); // find all with a title attribute and tipsy them
	},
	addGroup: function(cb) {
		var self = this;
		$('body').append('<div id="add_group_dialog"></div>');
		if(!this.$addGroupTmpl) {
			this.$addGroupTmpl = $('#addGroupTemplate');
		}
		var $dlg = this.$addGroupTmpl.octemplate();
		$('#add_group_dialog').html($dlg).dialog({
			modal: true,
			closeOnEscape: true,
			title:  t('contacts', 'Add group'),
			height: 'auto', width: 'auto',
			buttons: {
				'Ok':function() {
					self.Groups.addGroup(
						{name:$dlg.find('input:text').val()},
						function(response) {
							if(typeof cb === 'function') {
								cb(response);
							} else {
								if(response.status !== 'success') {
									OC.notify({message: response.message});
								}
							}
						});
					$(this).dialog('close');
				},
				'Cancel':function() { 
					$(this).dialog('close'); 
					return false;
				}
			},
			close: function(event, ui) {
				$(this).dialog('destroy').remove();
				$('#add_group_dialog').remove();
			},
			open: function(event, ui) {
				$dlg.find('input').focus();
			},
		});
	},
	setAllChecked: function(checked) {
		var selector = checked ? 'input:checkbox:visible:not(checked)' : 'input:checkbox:visible:checked';
		$.each(self.$contactList.find(selector), function() {
			$(this).prop('checked', checked);
		});
	},
	jumpToContact: function(id) {
		this.$rightContent.scrollTop(this.Contacts.contactPos(id)+10);
	},
	closeContact: function(id) {
		if(typeof this.currentid === 'number') {
			if(this.Contacts.findById(id).close()) {
				this.$contactList.show();
				this.jumpToContact(id);
			}
		} else if(this.currentid === 'new') {
			this.tmpcontact.remove();
			this.$contactList.show();
		}
		delete this.currentid;
		this.showActions(['addcontact']);
		this.$groups.find('optgroup,option:not([value="-1"])').remove();
	},
	openContact: function(id) {
		console.log('Contacts.openContact', id);
		if(this.currentid) {
			this.closeContact(this.currentid);
		}
		this.currentid = parseInt(id);
		this.setAllChecked(false);
		this.$contactList.hide();
		this.$toggleAll.hide();
		var $contactelem = this.Contacts.showContact(this.currentid);
		this.$rightContent.prepend($contactelem);
		this.buildGroupSelect();
	},
	update: function() {
		console.log('update');
	},
	uploadPhoto:function(filelist) {
		var self = this;
		if(!filelist) {
			OC.notify({message:t('contacts','No files selected for upload.')});
			return;
		}
		var file = filelist[0];
		var target = $('#file_upload_target');
		var form = $('#file_upload_form');
		var totalSize=0;
		if(file.size > $('#max_upload').val()){
			OC.notify({
				message:t(
					'contacts',
					'The file you are trying to upload exceed the maximum size for file uploads on this server.'),
			});
			return;
		} else {
			target.load(function() {
				var response=jQuery.parseJSON(target.contents().text());
				if(response != undefined && response.status == 'success') {
					console.log('response', response);
					self.editPhoto(self.currentid, response.data.tmp);
					//alert('File: ' + file.tmp + ' ' + file.name + ' ' + file.mime);
				} else {
					OC.notify({message:response.data.message});
				}
			});
			form.submit();
		}
	},
	cloudPhotoSelected:function(id, path) {
		var self = this;
		console.log('cloudPhotoSelected, id', id)
		$.getJSON(OC.filePath('contacts', 'ajax', 'oc_photo.php'),
				  {path: path, id: id},function(jsondata) {
			if(jsondata.status == 'success') {
				//alert(jsondata.data.page);
				self.editPhoto(jsondata.data.id, jsondata.data.tmp)
				$('#edit_photo_dialog_img').html(jsondata.data.page);
			}
			else{
				OC.notify({message: jsondata.data.message});
			}
		});
	},
	editCurrentPhoto:function(id) {
		var self = this;
		$.getJSON(OC.filePath('contacts', 'ajax', 'currentphoto.php'),
				  {id: id}, function(jsondata) {
			if(jsondata.status == 'success') {
				//alert(jsondata.data.page);
				self.editPhoto(jsondata.data.id, jsondata.data.tmp)
				$('#edit_photo_dialog_img').html(jsondata.data.page);
			}
			else{
				OC.notify({message: jsondata.data.message});
			}
		});
	},
	editPhoto:function(id, tmpkey) {
		console.log('editPhoto', id, tmpkey)
		$('.tipsy').remove();
		// Simple event handler, called from onChange and onSelect
		// event handlers, as per the Jcrop invocation above
		var showCoords = function(c) {
			$('#x1').val(c.x);
			$('#y1').val(c.y);
			$('#x2').val(c.x2);
			$('#y2').val(c.y2);
			$('#w').val(c.w);
			$('#h').val(c.h);
		};

		var clearCoords = function() {
			$('#coords input').val('');
		};

		var self = this;
		if(!this.$cropBoxTmpl) {
			this.$cropBoxTmpl = $('#cropBoxTemplate');
		}
		$('body').append('<div id="edit_photo_dialog"></div>');
		var $dlg = this.$cropBoxTmpl.octemplate({id: id, tmpkey: tmpkey});

		var cropphoto = new Image();
		$(cropphoto).load(function () {
			$(this).attr('id', 'cropbox');
			$(this).prependTo($dlg).fadeIn();
			$(this).Jcrop({
				onChange:	showCoords,
				onSelect:	showCoords,
				onRelease:	clearCoords,
				maxSize:	[399, 399],
				bgColor:	'black',
				bgOpacity:	.4,
				boxWidth: 	400,
				boxHeight:	400,
				setSelect:	[ 100, 130, 50, 50 ]//,
				//aspectRatio: 0.8
			});
			$('#edit_photo_dialog').html($dlg).dialog({
							modal: true,
							closeOnEscape: true,
							title:  t('contacts', 'Edit profile picture'),
							height: 'auto', width: 'auto',
							buttons: {
								'Ok':function() {
									self.savePhoto($(this));
									$(this).dialog('close');
								},
								'Cancel':function() { $(this).dialog('close'); }
							},
							close: function(event, ui) {
								$(this).dialog('destroy').remove();
								$('#edit_photo_dialog').remove();
							},
							open: function(event, ui) {
								// Jcrop maybe?
							}
						});
		}).error(function () {
			OC.notify({message:t('contacts','Error loading profile picture.')});
		}).attr('src', OC.linkTo('contacts', 'tmpphoto.php')+'?tmpkey='+tmpkey);
	},
	savePhoto:function($dlg) {
		var form = $dlg.find('#cropform');
		q = form.serialize();
		console.log('savePhoto', q);
		$.post(OC.filePath('contacts', 'ajax', 'savecrop.php'), q, function(response) {
			var jsondata = $.parseJSON(response);
			console.log('savePhoto, jsondata', typeof jsondata);
			if(jsondata && jsondata.status === 'success') {
				// load cropped photo.
				$(document).trigger('status.contact.photoupdated', {
					id: jsondata.data.id,
				});
			} else {
				if(!jsondata) {
					OC.notify({message:t('contacts', 'Network or server error. Please inform administrator.')});
				} else {
					OC.notify({message: jsondata.data.message});
				}
			}
		});
	},
	// NOTE: Deprecated
	addAddressbook:function(data, cb) {
		$.ajax({
			type:'POST',
			async:false,
			url:OC.filePath('contacts', 'ajax', 'addressbook/add.php'), 
			data:{ name: data.name, description: data.description },
			success:function(jsondata) {
				if(jsondata.status == 'success') {
					if(typeof cb === 'function') {
						cb({
							status:'success',
							addressbook: jsondata.data.addressbook,
						});
					}
				} else {
					if(typeof cb === 'function') {
						cb({status:'error', message:jsondata.data.message});
					}
				}
		}});
	},
	// NOTE: Deprecated
	selectAddressbook:function(cb) {
		var self = this;
		var jqxhr = $.get(OC.filePath('contacts', 'templates', 'selectaddressbook.html'), function(data) {
			$('body').append('<div id="addressbook_dialog"></div>');
			var $dlg = $('#addressbook_dialog').html(data).octemplate({
				nameplaceholder: t('contacts', 'Enter name'),
				descplaceholder: t('contacts', 'Enter description'),
			}).dialog({
				modal: true, height: 'auto', width: 'auto',
				title:  t('contacts', 'Select addressbook'),
				buttons: {
					'Ok':function() {
						aid = $(this).find('input:checked').val();
						if(aid == 'new') {
							var displayname = $(this).find('input.name').val();
							var description = $(this).find('input.desc').val();
							if(!displayname.trim()) {
								OC.dialogs.alert(t('contacts', 'The address book name cannot be empty.'), t('contacts', 'Error'));
								return false;
							}
							console.log('ID, name and desc', aid, displayname, description);
							if(typeof cb === 'function') {
								// TODO: Create addressbook
								var data = {name:displayname, description:description};
								self.addAddressbook(data, function(data) {
									if(data.status === 'success') {
										cb({
											status:'success',
											addressbook:data.addressbook,
										});
									} else {
										cb({status:'error'});
									}
								});
							}
							$(this).dialog('close');
						} else {
							console.log('aid ' + aid);
							if(typeof cb === 'function') {
								cb({
									status:'success',
									addressbook:self.Contacts.addressbooks[parseInt(aid)],
								});
							}
							$(this).dialog('close');
						}
					},
					'Cancel':function() {
						$(this).dialog('close');
					}
				},
				close: function(event, ui) {
					$(this).dialog('destroy').remove();
					$('#addressbook_dialog').remove();
				},
				open: function(event, ui) {
					console.log('open', $(this));
					var $lastrow = $(this).find('tr.new');
					$.each(self.Contacts.addressbooks, function(i, book) {
						console.log('book', i, book);
						if(book.owner === OC.currentUser
								|| (book.permissions & OC.PERMISSION_UPDATE
								|| book.permissions & OC.PERMISSION_CREATE
								|| book.permissions & OC.PERMISSION_DELETE)) {
							var row = '<tr><td><input id="book_{id}" name="book" type="radio" value="{id}"</td>'
								+ '<td><label for="book_{id}">{displayname}</label></td>'
								+ '<td>{description}</td></tr>'
							var $row = $(row).octemplate({
									id:book.id,
									displayname:book.displayname,
									description:book.description
								});
							$lastrow.before($row);
						}
					});
					$(this).find('input[type="radio"]').first().prop('checked', true);
					$lastrow.find('input.name,input.desc').on('focus', function(e) {
						$lastrow.find('input[type="radio"]').prop('checked', true);
					});
				},
			});
		}).error(function() {
			OC.notify({message: t('contacts', 'Network or server error. Please inform administrator.')});
		});
	},
};

(function( $ ) {

	/**
	* Object Template
	* Inspired by micro templating done by e.g. underscore.js
	*/
	var Template = {
		init: function(options, elem) {
			// Mix in the passed in options with the default options
			this.options = $.extend({},this.options,options);

			// Save the element reference, both as a jQuery
			// reference and a normal reference
			this.elem  = elem;
			this.$elem = $(elem);

			var _html = this._build(this.options);
			//console.log('html', this.$elem.html());
			return $(_html);
		},
		// From stackoverflow.com/questions/1408289/best-way-to-do-variable-interpolation-in-javascript
		_build: function(o){
			var data = this.$elem.attr('type') === 'text/template'
				? this.$elem.html() : this.$elem.get(0).outerHTML;
			return data.replace(/{([^{}]*)}/g,
				function (a, b) {
					var r = o[b];
					return typeof r === 'string' || typeof r === 'number' ? r : a;
				}
			);
		},
		options: {
		},
	};

	$.fn.octemplate = function(options) {
		if ( this.length ) {
			var _template = Object.create(Template);
			return _template.init(options, this);
		}
	};

})( jQuery );

$(document).ready(function() {

	OC.Contacts.init(id);

});
