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

OC.Contacts = OC.Contacts || {
	init:function() {
		this.ENTER_KEY = 13;
		this.scrollTimeoutMiliSecs = 100;
		this.isScrolling = false;
		this.cacheElements();
		this.Contacts = new OC.Contacts.ContactList(
			this.$contactList, 
			this.$contactListItemTemplate, 
			this.$contactFullTemplate,
			this.detailTemplates
		);
		this.bindEvents();
	},
	/**
	 * Arguments:
	 * message: The text message to show.
	 * timeout: The timeout in seconds before the notification disappears. Default 10.
	 * timeouthandler: A function to run on timeout.
	 * clickhandler: A function to run on click. If a timeouthandler is given it will be cancelled.
	 * data: An object that will be passed as argument to the timeouthandler and clickhandler functions.
	 * cancel: If set cancel all ongoing timer events and hide the notification.
	 */
	notify:function(params) {
		var self = this;
		if(!self.notifier) {
			self.notifier = $('#notification');
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
			if(!self || !self.notifier) {
				var self = OC.Contacts;
				self.notifier = $('#notification');
			}
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
				if(!self || !self.notifier) {
					var self = OC.Contacts;
					self.notifier = $(this);
				}
				clearTimeout(timer);
				self.notifier.off('click');
				params.clickhandler(self.notifier.data(dataid));
				self.notifier.removeData(dataid);
			});
		}
	},
	loading:function(obj, state) {
		if(state) {
			$(obj).addClass('loading');
		} else {
			$(obj).removeClass('loading');
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
		this.$groupList = $('#grouplist');
		this.$contactList = $('#contactlist');
		this.$contactListHeader = $('#contactlistheader');
		this.$toggleAll = $('#toggle_all');
	},
	bindEvents: function() {
		var self = this;
		this.$toggleAll.on('change', function() {
			self.Contacts.toggleAll(this, self.$contactList.find('input:checkbox'));
		});
		this.$contactList.on('change', 'input:checkbox', function(event) {
			var id = parseInt($(this).parents('tr').first().data('id'));
			self.Contacts.selectedContacts.push(id);
			console.log('selected', id);
		});
		$(document).bind('status.contact.deleted', function(e, data) {
			var id = parseInt(data.id);
			console.log('contact', data.id, 'deleted');
			// update counts on group list
			self.$groupList.find('h3').each(function(i, group) {
				if($(this).data('type') === 'all') {
					$(this).find('.numcontacts').text(parseInt($(this).find('.numcontacts').text()-1));
				} else if($(this).data('type') === 'category') {
					var contacts = $(this).data('contacts');
					console.log('contacts', contacts, contacts.indexOf(id), contacts.indexOf(String(id)));
					if(contacts.indexOf(String(id)) !== -1) {
						contacts.splice(contacts.indexOf(String(id)), 1);
						console.log('contacts', contacts, contacts.indexOf(id), contacts.indexOf(String(id)));
						$(this).data('contacts', contacts);
						$(this).find('.numcontacts').text(contacts.length);
					}
				}
			});
		});
		$(document).bind('status.contact.enabled', function(e, enabled) {
			if(enabled) {
				self.$header.find('.delete').show();
			} else {
				self.$header.find('.delete').hide();
			}
		});
		$(document).bind('status.contacts.loaded', function(e, result) {
			console.log('status.contacts.loaded', result);
			if(result.status !== true) {
				alert('Error loading contacts!');
			}
			self.numcontacts = result.numcontacts;
			self.loadGroups();
			self.$rightContent.removeClass('loading');
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
		this.$groupList.on('click', 'h3', function() {
			console.log('Group click', $(this).data('id'), $(this).data('type'));
			delete self.currentid;
			self.$groupList.find('h3').removeClass('active');
			self.$contactList.show();
			self.$header.find('.list').show();
			self.$header.find('.single').hide();
			$('#contact').remove();
			$(this).addClass('active');
			if($(this).data('type') === 'category') {
				self.Contacts.showContacts($(this).data('contacts'));
			} else {
				self.Contacts.showContacts($(this).data('id'));
			}
		});
		this.$contactList.on('click', 'tr', function(event) {
			if($(event.target).is('input')) {
				return;
			}
			if($(event.target).is('a.mailto')) {
				console.log('mailto', $(this).find('.email').text().trim());
				window.location.href='mailto:' + $(this).find('.email').text().trim();
				return;
			}
			self.currentid = $(this).data('id');
			console.log('Contact click', self.currentid);
			self.$contactList.hide();
			self.$header.find('.list').hide();
			self.$header.find('.single').show();
			self.$rightContent.prepend(self.Contacts.showContact(self.currentid));
		});
		this.$header.find('.back').on('click keydown', function() {
			console.log('back');
			var listelem = self.Contacts.contacts[parseInt(self.currentid)].detach().remove();
			self.$contactList.show();
		});
		this.$header.find('.add').on('click keydown', function() {
			console.log('add');
			self.$contactList.hide();
			self.$rightContent.prepend(self.Contacts.addContact());
		});
		this.$header.find('.delete').on('click keydown', function() {
			console.log('delete');
			if(self.currentid) {
				self.Contacts.delayedDeleteContact(self.currentid);
			} else {
				console.log('currentid is not set');
			}
		});
		this.$header.find('.settings').on('click keydown', function() {
			try {
				//ninjahelp.hide();
				OC.appSettings({appid:'contacts', loadJS:true, cache:false});
			} catch(e) {
				console.log('error:', e.message);
			}
		});
		this.$contactList.on('mouseenter', 'td.email', function(event) {
			if($(this).text().trim().length > 3) {
				$(this).find('.mailto').fadeIn(100);
			}
		});
		this.$contactList.on('mouseleave', 'td.email', function(event) {
			$(this).find('.mailto').fadeOut(100);
		});
		$('[title]').tipsy(); // find all with a title attribute and tipsy them
	},
	update: function() {
		console.log('update');
	},
	loadGroups: function() {
		var self = this;
		var groupList = this.$groupList;
		var tmpl = this.$groupListItemTemplate;

		tmpl.octemplate({id: 'all', type: 'all', num: this.numcontacts, name: t('contacts', 'All')}).appendTo(groupList);
		tmpl.octemplate({id: 'fav', type: 'fav', num: '', name: t('contacts', 'Favorites')}).appendTo(groupList);
		$.getJSON(OC.filePath('contacts', 'ajax', 'categories/list.php'), {}, function(jsondata) {
			if (jsondata && jsondata.status == 'success') {
				self.categories = [];
				$.each(jsondata.data.categories, function(c, category) {
					var $elem = (tmpl).octemplate({
						id: category.id, 
						type: 'category', 
						num: category.contacts.length,
						name: category.name,
					})
					self.categories.push({id: category.id, name: category.name});
					$elem.data('contacts', category.contacts)
					$elem.appendTo(groupList);
				});
			}
		});
	},
};
	

(function( $ ) {
	/**
	* An item which binds the appropriate html and event handlers
	* @param parent the parent Contacts list
	* @param data the data used to populate the contact
	* @param template the jquery object used to render the contact
	*/
	var Contact = function(parent, id, access, data, listtemplate, fulltemplate, detailtemplates) {
		//console.log('contact:', id, access); //parent, id, data, listtemplate, fulltemplate);
		this.parent = parent, 
			this.id = id, 
			this.access = access,
			this.data = data, 
			this.$listTemplate = listtemplate,
			this.$fullTemplate = fulltemplate;
			this.detailTemplates = detailtemplates;
		var self = this;
		this.multi_properties = ['EMAIL', 'TEL', 'IMPP', 'ADR', 'URL'];
	}
	
	/**
	 * Act on change
	 * @param event
	 */
	Contact.prototype.save = function(self, obj) {
		OC.Contacts.loading(obj, true);
		var container = $(obj).hasClass('propertycontainer') 
			? obj : self.propertyContainerFor(obj);
		var element = container.data('element').toUpperCase();
		console.log('change', obj, element, container, container
			.find('input.value,select.value,textarea.value'));//.serializeArray());
		var q = container.find('input.value,select.value,textarea.value').serialize();
		if(q == '' || q == undefined) {
			$(document).trigger('status.contact', {
				status: 'error', 
				message: t('contacts', 'Couldn\'t serialize elements.'), 
			});
			OC.Contacts.loading(obj, false);
			return false;
		}
		q = q + '&id=' + self.id + '&name=' + element;
		if(self.multi_properties.indexOf(element) !== -1) {
			q = q + '&checksum=' + container.data('checksum');
		}
		console.log(q);
		OC.Contacts.loading(obj, false);
	}
	
	/**
	 * Remove any open contact from the DOM and detach it's list
	 * element from the DOM.
	 */
	Contact.prototype.detach = function() {
		if(this.$fullelem) {
			this.$fullelem.remove();
		}
		if(this.$listelem) {
			return this.$listelem.detach();
		}
	}
	
	/**
	 * Set a contact to en/disabled depending on its permissions.
	 * @param boolean enabled
	 */
	Contact.prototype.setEnabled = function(enabled) {
		console.log('setEnabled', enabled);
		if(enabled) {
			this.$fullelem.find('#addproperty').show();
		} else {
			this.$fullelem.find('#addproperty').hide();
		}
		this.enabled = enabled;
		this.$fullelem.find('.value,.action').each(function () {
			console.log($(this));
			$(this).prop('disabled', !enabled);
		});
		$(document).trigger('status.contact.enabled', false);
	}
		
	/**
	 * Delete contact from data store and remove it from the DOM
	 */
	Contact.prototype.destroy = function(cb) {
		var self = this;
		$.post(OC.filePath('contacts', 'ajax', 'contact/delete.php'), 
			   {id: this.id}, function(jsondata) {
			if(jsondata && jsondata.status === 'success') {
				if(self.$listelem) {
					self.$listelem.remove();
				}
				if(self.$fullelem) {
					self.$fullelem.remove();
				}
			}
			if(typeof cb == 'function') {
				cb({
					status: jsondata ? jsondata.status : 'error', 
					message: (jsondata && jsondata.data) ? jsondata.data.message : '',
				});
			}
		});
	}
	
	Contact.prototype.propertyContainerFor = function(obj) {
		return $(obj).parents('.propertycontainer').first();
	}

	/**
	 * Render the list item
	 * @return A jquery object to be inserted in the DOM
	 */
	Contact.prototype.renderListItem = function() {
		this.$listelem = this.$listTemplate.octemplate({
			id: this.id,
			name: this.getPreferredValue('FN', ''),
			email: this.getPreferredValue('EMAIL', ''),
			tel: this.getPreferredValue('TEL', ''),
			adr: this.getPreferredValue('ADR', []).clean('').join(', '),
			categories: this.getPreferredValue('CATEGORIES', [])
				.clean('').join(' / '),
		});
		return this.$listelem;
	}

	/**
	 * Render the full contact
	 * @return A jquery object to be inserted in the DOM
	 */
	Contact.prototype.renderContact = function() {
		var self = this;
		console.log('renderContact', this.data);
		var values = this.data
			? {
				id: this.id,
				name: this.getPreferredValue('FN', ''),
				nickname: this.getPreferredValue('NICKNAME', ''),
				title: this.getPreferredValue('TITLE', ''),
				org: this.getPreferredValue('ORG', []).clean('').join(', '), // TODO Add parts if more than one.
				bday: this.getPreferredValue('BDAY', '').length >= 10 
					? $.datepicker.formatDate('dd-mm-yy', 
						$.datepicker.parseDate('yy-mm-dd', 
							this.getPreferredValue('BDAY', '').substring(0, 10)))
					: '',
				}
			: {id: '', name: '', nickname: '', title: '', org: '', bday: ''};
		this.$fullelem = this.$fullTemplate.octemplate(values).data('contactobject', this);
		this.$fullelem.on('change', '#addproperty', function(event) {
			console.log('add', $(this).val());
			$(this).val('')
		});
		this.$fullelem.on('change', '.value', function(event) {
			console.log('change', event);
			self.save(self, event.target);
		});
		this.$fullelem.find('form').on('submit', function(event) {
			console.log('submit', event);
			return false;
		});
		this.$fullelem.find('[data-element="bday"]')
			.find('input').datepicker({
				dateFormat : 'dd-mm-yy'
		});
		if(!this.data) {
			// A new contact
			this.setEnabled(true);
			return this.$fullelem;
		}
		for(var value in values) {
			console.log(value);
			if(!values[value].length) {
				this.$fullelem.find('[data-element="' + value + '"]').hide();
			}
		}
		$.each(this.multi_properties, function(idx, name) {
			if(self.data[name]) {
				var $list = self.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				for(var p in self.data[name]) {
					if(typeof self.data[name][p] === 'object') {
						var property = self.data[name][p];
						console.log(name, p, property);
						$property = null;
						switch(name) {
							case 'TEL':
							case 'URL':
							case 'EMAIL':
								$property = self.renderStandardProperty(name.toLowerCase(), property);
								break;
							case 'ADR':
								$property = self.renderAddressProperty(property);
								break;
							case 'IMPP':
								$property = self.renderIMProperty(property);
								break;
						}
						if(!$property) {
							continue;
						}
						//console.log('$property', $property);
						if(property.label) {
							if(!property.parameters['TYPE']) {
								property.parameters['TYPE'] = [];
							}
							property.parameters['TYPE'].push(property.label);
						}
						for(var param in property.parameters) {
							//console.log('param', param);
							if(param.toUpperCase() == 'PREF') {
								$property.find('input[type="checkbox"]').attr('checked', 'checked')
							}
							else if(param.toUpperCase() == 'TYPE') {
								for(etype in property.parameters[param]) {
									var found = false;
									var et = property.parameters[param][etype];
									if(typeof et !== 'string') {
										continue;
									}
									//console.log('et', et);
									if(et.toUpperCase() === 'INTERNET') {
										continue;
									}
									$property.find('select.type option').each(function() {
										if($(this).val().toUpperCase() === et.toUpperCase()) {
											$(this).attr('selected', 'selected');
											found = true;
										}
									});
									if(!found) {
										$property.find('select.type option:last-child').after('<option value="'+et+'" selected="selected">'+et+'</option>');
									}
								}
							}
							else if(param.toUpperCase() == 'X-SERVICE-TYPE') {
								//console.log('setting', $property.find('select.impp'), 'to', property.parameters[param].toLowerCase());
								$property.find('select.impp').val(property.parameters[param].toLowerCase());
							}
						}
						$property.find('select.type[name="parameters[TYPE][]"]')
							.combobox({
								singleclick: true,
								classes: ['propertytype', 'float', 'label'],
							});
						$list.append($property);
					}
				}
			}
		});
		if(this.access.owner !== OC.currentUser 
			&& !(this.access.permissions & OC.PERMISSION_UPDATE
				|| this.access.permissions & OC.PERMISSION_DELETE)) {
			this.setEnabled(false);
		}
		return this.$fullelem;
	}

	/**
	 * Render a simple property. Used for EMAIL and TEL.
	 * @return A jquery object to be injected in the DOM
	 */
	Contact.prototype.renderStandardProperty = function(name, property) {
		if(!this.detailTemplates[name]) {
			console.log('No template for', name);
			return;
		}
		var values = { value: property.value, checksum: property.checksum };
		$elem = this.detailTemplates[name].octemplate(values);
		return $elem;
	}

	/**
	 * Render an ADR (address) property.
	 * @return A jquery object to be injected in the DOM
	 */
	Contact.prototype.renderAddressProperty = function(property) {
		if(!this.detailTemplates['adr']) {
			console.log('No template for adr', this.detailTemplates);
			return;
		}
		var values = { 
			value: property.value.clean('').join(', '), 
			checksum: property.checksum,
			adr0: property.value[0] || '',
			adr1: property.value[1] || '',
			adr2: property.value[2] || '',
			adr3: property.value[3] || '',
			adr4: property.value[4] || '',
			adr5: property.value[5] || '',
		};
		$elem = this.detailTemplates['adr'].octemplate(values);
		return $elem;
	}

	/**
	 * Render an IMPP (Instant Messaging) property.
	 * @return A jquery object to be injected in the DOM
	 */
	Contact.prototype.renderIMProperty = function(property) {
		if(!this.detailTemplates['impp']) {
			console.log('No template for impp', this.detailTemplates);
			return;
		}
		var values = { 
			value: property.value,
			checksum: property.checksum,
		};
		$elem = this.detailTemplates['impp'].octemplate(values);
		return $elem;
	}
	/**
	 * Get the jquery element associated with this object
	 */
	Contact.prototype.getListItemElement = function() {
		if(!this.$listelem) {
			this.renderListItem();
		}
		return this.$listelem;
	}
	
	/**
	 * Get the preferred value for a property.
	 * If a preferred value is not found the first one will be returned.
	 * @param string name The name of the property like EMAIL, TEL or ADR.
	 * @param def A default value to return if nothing is found.
	 */
	Contact.prototype.getPreferredValue = function(name, def) {
		var pref = def, found = false;
		if(this.data[name]) {
			var props = this.data[name];
			//console.log('props', props);
			$.each(props, function( i, prop ) {
				//console.log('prop:', i, prop);
				if(i === 0) { // Choose first to start with
					pref = prop.value;
				}
				for(var param in prop.parameters) {
					if(param.toUpperCase() == 'PREF') {
						found = true; //
						break;
					}
				}
				if(found) {
					return false; // break out of loop
				}
			});
		}
		return pref;
	}
	
	var ContactList = function(contactlist, contactlistitemtemplate, contactfulltemplate, contactdetailtemplates) {
		//console.log('ContactList', contactlist, contactlistitemtemplate, contactfulltemplate, contactdetailtemplates);
		var self = this;
		this.contacts = {};
		this.deletionQueue = [];
		this.selectedContacts = [];
		this.$contactList = contactlist;
		this.$contactListItemTemplate = contactlistitemtemplate;
		this.$contactFullTemplate = contactfulltemplate;
		this.contactDetailTemplates = contactdetailtemplates;
		this.$contactList.scrollTop(0);
		this.loadContacts(0);
		
	}
	
	/**
	* Show contacts in list
	* @param Array contacts. A list of contact ids.
	*/
	ContactList.prototype.showContacts = function(contacts) {
		for(var contact in this.contacts) {
			if(contacts === 'all') {
				this.contacts[contact].getListItemElement().show();
			} else {
				contact = parseInt(contact);
				if(contacts.indexOf(String(contact)) === -1) {
					this.contacts[contact].getListItemElement().hide();
				} else {
					this.contacts[contact].getListItemElement().show();
				}
			}
		}
	}
	
	/**
	* Jumps to an element in the contact list
	* FIXME: Use cached contact element.
	* @param number the number of the item starting with 0
	*/
	ContactList.prototype.jumpToElemenId = function(id) {
		$elem = $('tr.contact_item[data-id="' + id + '"]');
		this.$contactList.scrollTop(
			$elem.offset().top - this.$contactList.offset().top 
				+ this.$contactList.scrollTop());
	};
	
	/**
	* Returns a Contact object by searching for its id
	* @param id the id of the node
	* @return the Contact object or undefined if not found.
	* FIXME: If continious loading is reintroduced this will have
	* to load the requested contact.
	*/
	ContactList.prototype.findById = function(id) {
		return this.contacts[parseInt(id)];
	};

	ContactList.prototype.warnNotDeleted = function(e) {
		e = e || window.event;
		var warn = t('contacts', 'Some contacts are marked for deletion, but not deleted yet. Please wait for them to be deleted.');
		if (e) {
			e.returnValue = String(warn);
		}
		if(OC.Contacts.Contacts.deletionQueue.length > 0) {
			setTimeout(OC.Contacts.Contacts.deleteFilesInQueue, 1);
		}
		return warn;
	}
		
	ContactList.prototype.delayedDeleteContact = function(id) {
		var self = this;
		var listelem = this.contacts[parseInt(id)].detach();
		self.$contactList.show();
		this.deletionQueue.push(parseInt(id));
		console.log('deletionQueue', this.deletionQueue, listelem);
		if(!window.onbeforeunload) {
			window.onbeforeunload = this.warnNotDeleted;
		}
		// TODO: Check if there are anymore contacts, otherwise show intro.
		OC.Contacts.notify({
			data:listelem,
			message:t('contacts','Click to undo deletion of "') + listelem.find('td.name').text() + '"',
			//timeout:5,
			timeouthandler:function(listelem) {
				console.log('timeout', listelem);
				self.deleteContact(listelem.data('id'), true);
			},
			clickhandler:function(listelem) {
				console.log('clickhandler', listelem);
				self.insertContact(listelem);
				OC.Contacts.notify({message:t('contacts', 'Cancelled deletion of: "') + listelem.find('td.name').text() + '"'});
				window.onbeforeunload = null;
			}
		});
	}

	/**
	* Delete a contact with this id
	* @param id the id of the contact
	*/
	ContactList.prototype.deleteContact = function(id, removeFromQueue) {
		var self = this;
		var id = parseInt(id);
		console.log('deletionQueue', this.deletionQueue);
		var updateQueue = function(id, remove) {
			if(removeFromQueue) {
				OC.Contacts.Contacts.deletionQueue.splice(OC.Contacts.Contacts.deletionQueue.indexOf(parseInt(id)), 1);
			}
			if(OC.Contacts.Contacts.deletionQueue.length == 0) {
				window.onbeforeunload = null;
			}
		}

		if(OC.Contacts.Contacts.deletionQueue.indexOf(parseInt(id)) == -1 && removeFromQueue) {
			console.log('returning');
			updateQueue(id, removeFromQueue);
			if(typeof cb == 'function') {
				window.onbeforeunload = null;
			}
			return;
		}
		
		this.contacts[id].destroy(function(response) {
			console.log('deleteContact', response);
			if(response.status === 'success') {
				delete self.contacts[parseInt(id)];
				updateQueue(id, removeFromQueue);
				self.$contactList.show();
				window.onbeforeunload = null;
				$(document).trigger('status.contact.deleted', {
					id: id,
				});
			} else {
				OC.Contacts.notify({message:response.message});
			}
		});
	}
	
	/**
	* Opens the contact with this id in edit mode
	* @param id the id of the contact
	*/
	ContactList.prototype.showContact = function(id) {
		console.log('Contacts.showContact', id, this.contacts[parseInt(id)], this.contacts)
		return this.contacts[parseInt(id)].renderContact();
	};

	/**
	* Toggle all checkboxes
	*/
	ContactList.prototype.toggleAll = function(toggler, togglees) {
		var isChecked = $(toggler).is(':checked');
		console.log('toggleAll', isChecked, self);
		$.each(togglees, function( i, item ) {
			item.checked = isChecked;
		});
	};

	/**
	 * Insert a rendered contact list item into the list
	 * @param contact jQuery object.
	 */
	ContactList.prototype.insertContact = function(contact) {
		//console.log('insertContact', contact);
		var name = contact.find('td.name').text().toLowerCase();
		var added = false
		this.$contactList.find('tr').each(function() {
			if ($(this).find('td.name').text().toLowerCase().localeCompare(name) > 0) {
				$(this).before(contact);
				added = true;
				return false;
			}
		});
		if(!added) {
			this.$contactList.append(contact);
		}
		//this.contacts[id] = contact;
		return contact;
	}
	
	/**
	* Add contact
	* @param int offset
	*/
	ContactList.prototype.addContact = function() {
		var contact = new Contact(
			this, 
			null,
			null,
			null, 
			this.$contactListItemTemplate, 
			this.$contactFullTemplate,
			this.contactDetailTemplates
		);
		return contact.renderContact();
	}
	/**
	* Load contacts
	* @param int offset
	*/
	ContactList.prototype.loadContacts = function(offset, cb) {
		var self = this;
		// Should the actual ajax call be in the controller?
		$.getJSON(OC.filePath('contacts', 'ajax', 'contact/list.php'), {offset: offset}, function(jsondata) {
			if (jsondata && jsondata.status == 'success') {
				console.log('addressbooks', jsondata.data.addressbooks);
				self.addressbooks = {};
				$.each(jsondata.data.addressbooks, function(i, book) {
					self.addressbooks[parseInt(book.id)] = {owner: book.userid, permissions: parseInt(book.permissions)};
				});
				$.each(jsondata.data.contacts, function(c, contact) {
					self.contacts[parseInt(contact.id)] 
						= new Contact(
							self, 
							contact.id,
							self.addressbooks[parseInt(contact.aid)],
							contact.data, 
							self.$contactListItemTemplate, 
							self.$contactFullTemplate,
							self.contactDetailTemplates
						);
					var item = self.contacts[parseInt(contact.id)].renderListItem()
					//self.$contactList.append(item);
					self.insertContact(item);
				});
				$(document).trigger('status.contacts.loaded', {
					status: true, 
					numcontacts: jsondata.data.contacts.length 
				});
			}
			if(typeof cb === 'function') {
				cb();
			}
		});
	}
	OC.Contacts.ContactList = ContactList;


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

	OC.Contacts.init();

});
