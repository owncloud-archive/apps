function AssertException(message) { this.message = message; }
AssertException.prototype.toString = function () {
	return 'AssertException: ' + this.message;
}

// Usage: assert(obj != null, 'Object is null');
function assert(exp, message) {
	if (!exp) {
		throw new AssertException(message);
	}
}


if (typeof Object.create !== 'function') {
	Object.create = function (o) {
		function F() {}
		F.prototype = o;
		return new F();
	};
}

Array.prototype.clean = function(deleteValue) {
	for (var i = 0; i < this.length; i++) {
		if (this[i] == deleteValue) {         
			this.splice(i, 1);
			i--;
		}
	}
	return this;
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
	this.$groupListItemTemplate = listItemTmpl;
	this.categories = [];
}

GroupList.prototype.findById = function(id) {
	return this.$groupList.find('h3[data-id="' + id + '"]');
}

GroupList.prototype.addTo = function(contactid, groupid, cb) {
	var $groupelem = this.findById(groupid);
	var contacts = $groupelem.data('contacts');
	if(contacts.indexOf(String(contactid)) === -1) {
		$.post(OC.filePath('contacts', 'ajax', 'categories/addto.php'), {contactid: contactid, categoryid: groupid},function(jsondata) {
			if(!jsondata) {
				OC.notify({message:t('contacts', 'Network or server error. Please inform administrator.')});
				if(typeof cb === 'function') {
					cb('error');
				}
				return;
			}
			if(jsondata.status === 'success') {
				contacts.push(String(contactid));
				$groupelem.data('contacts', contacts);
				$groupelem.find('.numcontacts').text(contacts.length);
				if(typeof cb === 'function') {
					cb('success');
				}
			} else {
				OC.notify({message:jsondata.data.message});
				if(typeof cb == 'function') {
					cb('error');
				}
			}
		});
	} else {
		OC.notify({message:t('contacts', 'Contact is already in this group.')});
	}
}

GroupList.prototype.removeFrom = function(contactid, groupid, cb) {
	console.log('GroupList.removeFrom', contactid, groupid);
	var $groupelem = this.findById(groupid);
	var contacts = $groupelem.data('contacts');
	// If the contact is in the category remove it from internal list.
	if(!contacts) {
		return;
	}
	if(contacts.indexOf(String(contactid)) !== -1) {
		$.post(OC.filePath('contacts', 'ajax', 'categories/removefrom.php'), {contactid: contactid, categoryid: groupid},function(jsondata) {
			if(!jsondata) {
				OC.notify({message:t('contacts', 'Network or server error.')});
				if(typeof cb === 'function') {
					cb('error');
				}
				return;
			}
			if(jsondata.status === 'success') {
				contacts.splice(contacts.indexOf(String(contactid)), 1);
				//console.log('contacts', contacts, contacts.indexOf(id), contacts.indexOf(String(id)));
				$groupelem.data('contacts', contacts);
				$groupelem.find('.numcontacts').text(contacts.length);
				if(typeof cb === 'function') {
					cb('success');
				}
			} else {
				OC.notify({message:jsondata.data.message});
				if(typeof cb == 'function') {
					cb('error');
				}
			}
		});
	} else {
		console.log('Contact not in this group.', $groupelem);
		OC.notify({message:t('contacts', 'Contact not in this group.')});
	}
}

GroupList.prototype.removeFromAll = function(contactid, alsospecial) {
	var self = this;
	var selector = alsospecial ? 'h3' : 'h3[data-type="category"]';
	$.each(this.$groupList.find(selector), function(i, group) {
		self.removeFrom(contactid, $(this).data('id'));
	});
}

GroupList.prototype.loadGroups = function(numcontacts, cb) {
	var self = this;
	var $groupList = this.$groupList;
	var tmpl = this.$groupListItemTemplate;
	
	tmpl.octemplate({id: 'all', type: 'all', num: numcontacts, name: t('contacts', 'All')}).appendTo($groupList);
	tmpl.octemplate({id: 'fav', type: 'fav', num: '', name: t('contacts', 'Favorites')}).appendTo($groupList);
	$.getJSON(OC.filePath('contacts', 'ajax', 'categories/list.php'), {}, function(jsondata) {
		if (jsondata && jsondata.status == 'success') {
			$.each(jsondata.data.categories, function(c, category) {
				var $elem = (tmpl).octemplate({
					id: category.id, 
					type: 'category', 
					num: category.contacts.length,
					name: category.name,
				})
				self.categories.push({id: category.id, name: category.name});
				$elem.data('contacts', category.contacts)
				$elem.data('name', category.name)
				$elem.data('id', category.id)
				$elem.appendTo($groupList);
			});
		}
		if(typeof cb === 'function') {
			cb();
		}
	});
}

OC.Contacts = OC.Contacts || {
	init:function(id) {
		if(id) {
			this.currentid = parseInt(id);
			console.log('init, id:', id);
		}
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
		this.bindEvents();
		this.$toggleAll.show();
		this.showActions(['add', 'delete']);
	},
	loading:function(obj, state) {
		if(state) {
			$(obj).addClass('loading');
		} else {
			$(obj).removeClass('loading');
		}
	},
	/**
	 * Show/hide elements in the header
	 * @param act An array of actions to show based on class name e.g ['add', 'delete']
	 */
	showActions:function(act) {
		this.$headeractions.children().hide();
		this.$headeractions.children('.'+act.join(',.')).show();
	},
	showAction:function(act, show) {
		if(show) {
			this.$headeractions.find('.' + act).show();
		} else {
			this.$headeractions.find('.' + act).hide();
		}
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

	},
	// Build the select to add/remove from groups.
	// TODO: Maybe move to Groups object.
	buildGroupSelect: function() {
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
		} else {
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
		}
		$('<option value="add">' + t('contacts', 'Add group...') + '</option>').appendTo(this.$groups);
		this.$groups.val(-1);
	},
	bindEvents: function() {
		var self = this;
		
		// App specific events
		$(document).bind('status.contact.deleted', function(e, data) {
			var id = parseInt(data.id);
			console.log('contact', data.id, 'deleted');
			// update counts on group lists
			self.Groups.removeFromAll(data.id, true)
		});
		$(document).bind('status.contact.error', function(e, data) {
			OC.notify({message:data.message});
		});
		$(document).bind('status.contact.enabled', function(e, enabled) {
			console.log('status.contact.enabled', enabled)
			if(enabled) {
				self.showActions(['back', 'add', 'download', 'delete', 'groups']);
			} else {
				self.showActions(['back']);
			}
		});
		$(document).bind('status.contacts.loaded', function(e, result) {
			console.log('status.contacts.loaded', result);
			if(result.status !== true) {
				alert('Error loading contacts!');
			} else {
				self.numcontacts = result.numcontacts;
				self.$rightContent.removeClass('loading');
// 				var $firstelem = self.$contactList.find('tr:first-child');
// 				self.currentlistid = $firstelem.data('id');
// 				console.log('first element', self.currentlistid, $firstelem);
// 				$firstelem.addClass('active');
				self.Groups.loadGroups(self.numcontacts, function() {
					$('#leftcontent').removeClass('loading');
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
		$(document).bind('status.contact.removedfromgroup', function(e, result) {
			console.log('status.contact.removedfromgroup', result);
			if(self.currentgroup == result.groupid) {
				self.closeContact(result.contactid);
			}
		});
		$(document).bind('status.nomorecontacts', function(e, result) {
			console.log('status.nomorecontacts', result);
			self.$contactList = $('#contactlist').hide();
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
		this.$ninjahelp.find('.close').on('click keydown',function(event) {
			if(wrongKey(event)) {
				return;
			}
			self.$ninjahelp.hide();
		});
		this.$toggleAll.on('change', function() {
			var isChecked = self.Contacts.toggleAll(this, self.$contactList.find('input:checkbox:visible'));
			if(self.$groups.find('option').length === 1) {
				self.buildGroupSelect();
			}
			self.showAction('groups', isChecked);
		});
		this.$contactList.on('change', 'input:checkbox', function(event) {
			if($(this).is(':checked')) {
				if(self.$groups.find('option').length === 1) {
					self.buildGroupSelect();
				}
				self.showAction('groups', true);
			} else if(self.Contacts.getSelectedContacts().length === 0) {
				self.showAction('groups', false);
			}
		});
		this.$groups.on('change', function() {
			var $opt = $(this).find('option:selected');
			var action = $opt.parent().data('action');
			var ids, buildnow = false;
			
			if($opt.val() === 'add') {
				console.log('add group...');
				self.$groups.val(-1);
				return;
			}
			
			// If a contact is open the action is only applied to that,
			// otherwise on all selected items.
			if(self.currentid) {
				ids = [self.currentid,];
				buildnow = true
			} else {
				ids = self.Contacts.getSelectedContacts();
			}
			console.log('ids', ids);
			if(action === 'add') {
				$.each(ids, function(i, id) {
					console.log('id', id);
					self.Groups.addTo(id, $opt.val(), function(result) {
						console.log('after add', result);
						if(result === 'success') {
							console.log('typeof', typeof parseInt(id), id);
							self.Contacts.contacts[id].addToGroup($opt.text());
							if(buildnow) {
								self.buildGroupSelect();
							}
							$(document).trigger('status.contact.addedtogroup', {
								contactid: id,
								groupid: $opt.val(),
								groupname: $opt.text(),
							});
						} else {
							OC.notify({message:t('contacts', t('contacts', 'Error adding to group.'))});
						}
					});
				});
				if(!buildnow) {
					self.$groups.val(-1).hide().find('optgroup,option:not([value="-1"])').remove();
				}
			} else if(action === 'remove') {
				$.each(ids, function(i, id) {
					self.Groups.removeFrom(id, $opt.val(), function(result) {
						console.log('after remove', result);
						if(result === 'success') {
							self.Contacts.contacts[id].removeFromGroup($opt.text());
							if(buildnow) {
								self.buildGroupSelect();
							}
							// If a group is selected the contact has to be removed from the list
							$(document).trigger('status.contact.removedfromgroup', {
								contactid: id,
								groupid: $opt.val(),
								groupname: $opt.text(),
							});
						} else {
							OC.notify({message:t('contacts', t('contacts', 'Error removing from group.'))});
						}
					});
				});
				if(!buildnow) {
					self.$groups.val(-1).hide().find('optgroup,option:not([value="-1"])').remove();
				}
			} // else something's wrong ;)
			$.each(self.$contactList.find('input:checkbox:checked'), function() {
				console.log('unchecking', $(this));
				$(this).prop('checked', false);
			});
			console.log('groups', $opt.parent().data('action'), $opt.val(), $opt.text());
		});
		// Group selected, only show contacts from that group
		this.$groupList.on('click', 'h3', function() {
			self.currentgroup = $(this).data('id');
			console.log('Group click', $(this).data('id'), $(this).data('type'));
			// Close any open contact.
			if(self.currentid) {
				var id = self.currentid;
				self.closeContact(id);
				self.Contacts.jumpToContact(id);
			}
			self.$groupList.find('h3').removeClass('active');
			self.$contactList.show();
			self.$toggleAll.show();
			self.showActions(['add', 'delete']);
			$(this).addClass('active');
			if($(this).data('type') === 'category') {
				self.Contacts.showContacts($(this).data('contacts'));
			} else {
				self.Contacts.showContacts($(this).data('id'));
			}
			self.$rightContent.scrollTop(0);
		});
		// Contact list. Either open a contact or perform an action (mailto etc.)
		this.$contactList.on('click', 'tr', function(event) {
			if($(event.target).is('input')) {
				return;
			}
			if(event.ctrlKey) {
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
		this.$header.on('click keydown', '.back', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('back');
			self.closeContact(self.currentid);
			self.$toggleAll.show();
			self.showActions(['add', 'delete']);
		});
		this.$header.on('click keydown', '.add', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('add');
			self.$contactList.hide();
			$(this).hide();
			self.$rightContent.prepend(self.Contacts.addContact());
			self.showActions(['back']);
		});
		this.$header.on('click keydown', '.delete', function(event) {
			if(wrongKey(event)) {
				return;
			}
			console.log('delete');
			if(self.currentid) {
				self.Contacts.delayedDeleteContact(self.currentid);
				self.showActions(['add', 'delete']);
			} else {
				console.log('currentid is not set');
			}
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
		this.$header.on('click keydown', '.settings', function(event) {
			if(wrongKey(event)) {
				return;
			}
			try {
				//ninjahelp.hide();
				OC.appSettings({appid:'contacts', loadJS:true, cache:false});
			} catch(e) {
				console.log('error:', e.message);
			}
		});
		this.$contactList.on('mouseenter', 'td.email', function(event) {
			if($(this).text().trim().length > 3) {
				$(this).find('.mailto').css('display', 'inline-block'); //.fadeIn(100);
			}
		});
		this.$contactList.on('mouseleave', 'td.email', function(event) {
			$(this).find('.mailto').fadeOut(100);
		});

		$(document).on('keyup', function(event) {
			if(event.target.nodeName.toUpperCase() != 'BODY') {
				return;
			}
			console.log(event.which + ' ' + event.target.nodeName);
			/**
			* To add:
			* Shift-a: add addressbook
			* u (85): hide/show leftcontent
			* f (70): add field
			*/
			switch(event.which) {
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
						self.Contacts.delayedDeleteContact(self.currentid);
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
					self.$ninjahelp.toggle('fast');
					break;
			}

		});
		
		$('[title]').tipsy(); // find all with a title attribute and tipsy them
	},
	jumpToContact: function(id) {
		this.$rightContent.scrollTop(this.Contacts.contactPos(id));
	},
	closeContact: function(id) {
		if(this.currentid) {
			delete this.currentid;
			this.Contacts.findById(id).close();
			this.$contactList.show();
			this.jumpToContact(id);
		}
		this.$groups.find('optgroup,option:not([value="-1"])').remove();
	},
	openContact: function(id) {
		this.currentid = parseInt(id);
		this.$contactList.hide();
		this.$toggleAll.hide();
		var $contactelem = this.Contacts.showContact(this.currentid);
		this.$rightContent.prepend($contactelem);
		this.buildGroupSelect();
	},
	update: function() {
		console.log('update');
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
								$('#name_dialog').remove();
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
			return this.$elem.html().replace(/{([^{}]*)}/g,
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
