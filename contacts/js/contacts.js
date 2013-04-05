OC.Contacts = OC.Contacts || {};


(function(window, $, OC) {
	'use strict';
	/**
	* An item which binds the appropriate html and event handlers
	* @param parent the parent ContactList
	* @param id The integer contact id.
	* @param metadata An metadata object containing and 'owner' string variable and an integer 'permissions' variable.
	* @param data the data used to populate the contact
	* @param listtemplate the jquery object used to render the contact list item
	* @param fulltemplate the jquery object used to render the entire contact
	* @param detailtemplates A map of jquery objects used to render the contact parts e.g. EMAIL, TEL etc.
	*/
	var Contact = function(parent, id, metadata, data, listtemplate, dragtemplate, fulltemplate, detailtemplates) {
		//console.log('contact:', id, metadata, data); //parent, id, data, listtemplate, fulltemplate);
		this.parent = parent,
			this.storage = parent.storage,
			this.id = id,
			this.metadata = metadata,
			this.data = data,
			this.$dragTemplate = dragtemplate,
			this.$listTemplate = listtemplate,
			this.$fullTemplate = fulltemplate;
			this.detailTemplates = detailtemplates;
		this.undoQueue = [];
		this.multi_properties = ['EMAIL', 'TEL', 'IMPP', 'ADR', 'URL'];
	};

	Contact.prototype.metaData = function() {
		return {
			contactid: this.id,
			addressbookid: this.metadata.parent,
			backend: this.metadata.backend
		}
	};

	Contact.prototype.getDisplayName = function() {
		return this.getPreferredValue('FN');
	};

	Contact.prototype.getId = function() {
		return this.id;
	};

	Contact.prototype.getParent = function() {
		return this.metadata.parent;
	};

	Contact.prototype.getBackend = function() {
		return this.metadata.backend;
	};

	Contact.prototype.merge = function(mergees) {
		console.log('Contact.merge, mergees', mergees);
		if(!mergees instanceof Array && !mergees instanceof Contact) {
			throw new TypeError('BadArgument: Contact.merge() only takes Contacts');
		} else {
			if(mergees instanceof Contact) {
				mergees = [mergees];
			}
		}

		// For multi_properties
		var addIfNotExists = function(name, newproperty) {
			// If the property isn't set at all just add it and return.
			if(!self.data[name]) {
				self.data[name] = [newproperty];
				return;
			}
			var found = false;
			$.each(self.data[name], function(idx, property) {
				if(name === 'ADR') {
					// Do a simple string comparison
					if(property.value.join(';').toLowerCase() === newproperty.value.join(';').toLowerCase()) {
						found = true;
						return false; // break loop
					}
				} else {
					if(property.value.toLowerCase() === newproperty.value.toLowerCase()) {
						found = true;
						return false; // break loop
					}
				}
			});
			if(found) {
				return;
			}
			// Not found, so adding it.
			self.data[name].push(newproperty);
		}

		var self = this;
		$.each(mergees, function(idx, mergee) {
			console.log('Contact.merge, mergee', mergee);
			if(!mergee instanceof Contact) {
				throw new TypeError('BadArgument: Contact.merge() only takes Contacts');
			}
			if(mergee === self) {
				throw new Error('BadArgument: Why should I merge with myself?');
			}
			$.each(mergee.data, function(name, properties) {
				console.log('property', name, properties);
				if(self.multi_properties.indexOf(name) === -1) {
					console.log('non-multi', name, properties);
					if(self.data[name] && self.data[name].length > 0) {
						// If the property exists don't touch it.
						return true; // continue
					} else {
						// Otherwise add it.
						self.data[name] = properties;
					}
				} else {
					$.each(properties, function(idx, property) {
						addIfNotExists(name, property);
					});
				}
			});
			console.log('Merged', self.data);
		});
		return true;
	};

	Contact.prototype.showActions = function(act) {
		this.$footer.children().hide();
		if(act && act.length > 0) {
			this.$footer.children('.'+act.join(',.')).show();
		}
	};

	Contact.prototype.setAsSaving = function(obj, state) {
		if(!obj) {
			return;
		}
		$(obj).prop('disabled', state);
		$(obj).toggleClass('loading', state);
		/*if(state) {
			$(obj).addClass('loading');
		} else {
			$(obj).removeClass('loading');
		}*/
	};

	Contact.prototype.pushToUndo = function(params) {
		// Check if the same property has been changed before
		// and update it's checksum if so.
		if(typeof params.oldchecksum !== 'undefined') {
			$.each(this.undoQueue, function(idx, item) {
				if(item.checksum === params.oldchecksum) {
					item.checksum = params.newchecksum;
					if(params.action === 'delete') {
						item.action = 'delete';
					}
					return false; // Break loop
				}
			});
		}
		this.undoQueue.push({
			action:params.action, 
			name: params.name,
			checksum: params.newchecksum,
			newvalue: params.newvalue,
			oldvalue: params.oldvalue
		});
		console.log('undoQueue', this.undoQueue);
	}
	
	Contact.prototype.addProperty = function($option, name) {
		console.log('Contact.addProperty', name)
		var $elem;
		switch(name) {
			case 'NICKNAME':
			case 'TITLE':
			case 'ORG':
			case 'BDAY':
			case 'NOTE':
				this.$fullelem.find('[data-element="' + name.toLowerCase() + '"]').addClass('new').show();
				$option.prop('disabled', true);
				break;
			case 'TEL':
			case 'URL':
			case 'EMAIL':
				var $elem = this.renderStandardProperty(name.toLowerCase());
				var $list = this.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				$list.append($elem);
				$elem.find('input.value').addClass('new');
				break;
			case 'ADR':
				var $elem = this.renderAddressProperty();
				var $list = this.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				$list.append($elem);
				$elem.find('.display').trigger('click');
				$elem.find('input.value').addClass('new');
				break;
			case 'IMPP':
				var $elem = this.renderIMProperty();
				var $list = this.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				$list.append($elem);
				$elem.find('input.value').addClass('new');
				break;
		}

		if($elem) {
			// If there's already a property of this type enable setting as preferred.
			if(this.multi_properties.indexOf(name) !== -1 && this.data[name] && this.data[name].length > 0) {
				var selector = 'li[data-element="' + name.toLowerCase() + '"]';
				$.each(this.$fullelem.find(selector), function(idx, elem) {
					$(elem).find('input.parameter[value="PREF"]').show();
				});
			} else if(this.multi_properties.indexOf(name) !== -1) {
				$elem.find('input.parameter[value="PREF"]').hide();
			}
			$elem.find('select.type[name="parameters[TYPE][]"]')
				.combobox({
					singleclick: true,
					classes: ['propertytype', 'float', 'label'],
				});
		}
	};

	Contact.prototype.deleteProperty = function(params) {
		var obj = params.obj;
		if(!this.enabled) {
			return;
		}
		var element = this.propertyTypeFor(obj);
		var $container = this.propertyContainerFor(obj);
		console.log('Contact.deleteProperty, element', element, $container);
		var params = {
			name: element,
			backend: this.metadata.backend,
			addressbookid: this.metadata.parent,
			id: this.id
		};
		if(this.multi_properties.indexOf(element) !== -1) {
			params['checksum'] = this.checksumFor(obj);
			if(params['checksum'] === 'new' && $.trim(this.valueFor(obj)) === '') {
				// If there's only one property of this type enable setting as preferred.
				if(this.data[element].length === 1) {
					var selector = 'li[data-element="' + element.toLowerCase() + '"]';
					this.$fullelem.find(selector).find('input.parameter[value="PREF"]').hide();
				}
				$container.remove();
				return;
			}
		}
		this.setAsSaving(obj, true);
		var self = this;
		$.when(this.storage.deleteProperty(this.metadata.backend, this.metadata.parent, this.id, params))
			.then(function(response) {
			if(!response.error) {
				// TODO: Test if removing from internal data structure works
				if(self.multi_properties.indexOf(element) !== -1) {
					// First find out if an existing element by looking for checksum
					var checksum = self.checksumFor(obj);
					self.pushToUndo({
						action:'delete', 
						name: element,
						oldchecksum: self.checksumFor(obj),
						newvalue: self.valueFor(obj)
					});
					if(checksum) {
						for(var i in self.data[element]) {
							if(self.data[element][i].checksum === checksum) {
								// Found it
								self.data[element].splice(self.data[element].indexOf(self.data[element][i]), 1);
								break;
							}
						}
					}
					// If there's only one property of this type enable setting as preferred.
					if(self.data[element].length === 1) {
						var selector = 'li[data-element="' + element.toLowerCase() + '"]';
						self.$fullelem.find(selector).find('input.parameter[value="PREF"]').hide();
					}
					$container.remove();
				} else {
					self.pushToUndo({
						action:'delete', 
						name: element,
						newvalue: $container.find('input.value').val()
					});
					self.setAsSaving(obj, false);
					self.$fullelem.find('[data-element="' + element.toLowerCase() + '"]').hide();
					$container.find('input.value').val('');
					self.$addMenu.find('option[value="' + element.toUpperCase() + '"]').prop('disabled', false);
				}
				$(document).trigger('status.contact.updated', {
					property: element,
					contact: self
				});
				return true;
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				self.setAsSaving(obj, false);
				return false;
			}
		})
		.fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ', ' + error;
			console.log( "Request Failed: " + err);
			$(document).trigger('status.contact.error', {
				message: t('contacts', 'Failed deleting property: {error}', {error:err})
			});
		});
;
	};

	/**
	 * @brief Save all properties. Used for merging contacts.
	 * If this is a new contact it will first be saved to the datastore and a
	 * new datastructure will be added to the object.
	 */
	Contact.prototype.saveAll = function(cb) {
		console.log('Contact.saveAll');
		if(!this.id) {
			var self = this;
			this.add({isnew:true}, function(response) {
				if(response.error) {
					console.warn('No response object');
					return false;
				}
				self.saveAll();
			});
			return;
		}
		var self = this;
		this.setAsSaving(this.$fullelem, true);
		var data = JSON.stringify(this.data);
		console.log('stringified', data);
		$.when(this.storage.saveAllProperties(this.metadata.backend, this.metadata.parent, this.id, data))
			.then(function(response) {
			if(!response.error) {
				self.data = response.data.data;
				self.metadata = response.data.metadata;
				if(typeof cb === 'function') {
					cb({error:false});
				}
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				if(typeof cb === 'function') {
					cb({error:true, message:response.message});
				}
			}
			self.setAsSaving(self.$fullelem, false);
		});
	}

	/**
	 * @brief Act on change of a property.
	 * If this is a new contact it will first be saved to the datastore and a
	 * new datastructure will be added to the object.
	 * If the obj argument is not provided 'name' and 'value' MUST be provided
	 * and this is only allowed for single elements like N, FN, CATEGORIES.
	 * @param obj. The form form field that has changed.
	 * @param name. The optional name of the element.
	 * @param value. The optional value.
	 */
	Contact.prototype.saveProperty = function(params) {
		console.log('Contact.saveProperty', params);
		if(!this.id) {
			var self = this;
			this.add({isnew:true}, function(response) {
				if(!response || response.status === 'error') {
					console.warn('No response object');
					return false;
				}
				self.saveProperty(params);
				self.showActions(['close', 'add', 'export', 'delete']);
			});
			return;
		}
		var obj = null;
		var element = null;
		var args = [];
		if(params.obj) {
			obj = params.obj;
			args = this.argumentsFor(obj);
			args['parameters'] = this.parametersFor(obj);
			element = this.propertyTypeFor(obj);
		} else {
			args = params;
			element = params.name;
			var value = utils.isArray(params.value)
				? $.param(params.value)
				: encodeURIComponent(params.value);
		}
		console.log('args', args);
		var self = this;
		this.setAsSaving(obj, true);
		$.when(this.storage.saveProperty(this.metadata.backend, this.metadata.parent, this.id, args))
			.then(function(response) {
			if(!response.error) {
				if(!self.data[element]) {
					self.data[element] = [];
				}
				if(self.multi_properties.indexOf(element) !== -1) {
					// First find out if an existing element by looking for checksum
					var checksum = self.checksumFor(obj);
					var value = self.valueFor(obj);
					var parameters = self.parametersFor(obj);
					if(checksum && checksum !== 'new') {
						self.pushToUndo({
							action:'save', 
							name: element,
							newchecksum: response.data.checksum,
							oldchecksum: checksum,
							newvalue: value,
							oldvalue: obj.defaultValue
						});
						$.each(self.data[element], function(i, el) {
							if(el.checksum === checksum) {
								self.data[element][i] = {
									name: element,
									value: value,
									parameters: parameters,
									checksum: response.data.checksum
								};
								return false;
							}
						});
					} else {
						$(obj).removeClass('new');
						self.pushToUndo({
							action:'add', 
							name: element,
							newchecksum: response.data.checksum,
							newvalue: value,
						});
						self.data[element].push({
							name: element,
							value: value,
							parameters: parameters,
							checksum: response.data.checksum,
						});
					}
					self.propertyContainerFor(obj).data('checksum', response.data.checksum);
				} else {
					// Save value and parameters internally
					var value = obj ? self.valueFor(obj) : params.value;
					self.pushToUndo({
						action: ((obj && obj.defaultValue) || self.data[element].length) ? 'save' : 'add', // FIXME
						name: element,
						newvalue: value,
					});
					switch(element) {
						case 'CATEGORIES':
							// We deal with this in addToGroup()
							break;
						case 'BDAY':
							// reverse order again.
							value = $.datepicker.formatDate('yy-mm-dd', $.datepicker.parseDate('dd-mm-yy', value));
							self.data[element][0] = {
								name: element,
								value: value,
								parameters: self.parametersFor(obj),
								checksum: response.data.checksum
							};
							break;
						case 'FN':
							if(!self.data.FN || !self.data.FN.length) {
								self.data.FN = [{name:'FN', value:'', parameters:[]}];
							}
							self.data.FN[0]['value'] = value;
							var nempty = true;
							if(!self.data.N) {
								// TODO: Maybe add a method for constructing new elements?
								self.data.N = [{name:'N',value:['', '', '', '', ''],parameters:[]}];
							}
							$.each(self.data.N[0]['value'], function(idx, val) {
								if(val) {
									nempty = false;
									return false;
								}
							});
							if(nempty) {
								self.data.N[0]['value'] = ['', '', '', '', ''];
								var nvalue = value.split(' ');
								// Very basic western style parsing. I'm not gonna implement
								// https://github.com/android/platform_packages_providers_contactsprovider/blob/master/src/com/android/providers/contacts/NameSplitter.java ;)
								self.data.N[0]['value'][0] = nvalue.length > 2 && nvalue.slice(nvalue.length-1).toString() || nvalue[1] || '';
								self.data.N[0]['value'][1] = nvalue[0] || '';
								self.data.N[0]['value'][2] = nvalue.length > 2 && nvalue.slice(1, nvalue.length-1).join(' ') || '';
								setTimeout(function() {
									self.saveProperty({name:'N', value:self.data.N[0].value.join(';')});
									setTimeout(function() {
										self.$fullelem.find('.fullname').next('.action.edit').trigger('click');
										OC.notify({message:t('contacts', 'Is this correct?')});
									}, 1000);
								}
								, 500);
							}
							break;
						case 'N':
							if(!utils.isArray(value)) {
								value = value.split(';');
								// Then it is auto-generated from FN.
								var $nelems = self.$fullelem.find('.n.editor input');
								$.each(value, function(idx, val) {
									self.$fullelem.find('#n_' + idx).val(val);
								});
							}
							var $fullname = self.$fullelem.find('.fullname'), fullname = '';
							var update_fn = false;
							if(!self.data.FN) {
								self.data.FN = [{name:'N', value:'', parameters:[]}];
							}
							if(self.data.FN[0]['value'] === '') {
								self.data.FN[0]['value'] = value[1] + ' ' + value[0];
								$fullname.val(self.data.FN[0]['value']);
								update_fn = true;
							} else if($fullname.val() == value[1] + ' ') {
								console.log('change', value);
								self.data.FN[0]['value'] = value[1] + ' ' + value[0];
								$fullname.val(self.data.FN[0]['value']);
								update_fn = true;
							} else if($fullname.val() == ' ' + value[0]) {
								self.data.FN[0]['value'] = value[1] + ' ' + value[0];
								$fullname.val(self.data.FN[0]['value']);
								update_fn = true;
							}
							if(update_fn) {
								setTimeout(function() {
									self.saveProperty({name:'FN', value:self.data.FN[0]['value']});
								}, 1000);
							}
						case 'NICKNAME':
						case 'ORG':
						case 'TITLE':
						case 'NOTE':
							self.data[element][0] = {
								name: element,
								value: value,
								parameters: self.parametersFor(obj),
								checksum: response.data.checksum
							};
							break;
						default:
							break;
					}
				}
				self.setAsSaving(obj, false);
				$(document).trigger('status.contact.updated', {
					property: element,
					contact: self
				});
				return true;
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				self.setAsSaving(obj, false);
				return false;
			}
		});
	};

	/**
	 * Hide contact list element.
	 */
	Contact.prototype.hide = function() {
		this.getListItemElement().hide();
	};

	/**
	 * Remove any open contact from the DOM.
	 */
	Contact.prototype.close = function() {
		console.log('Contact.close', this);
		if(this.$fullelem) {
			this.$fullelem.remove();
			return true;
		} else {
			return false;
		}
	};

	/**
	 * Remove any open contact from the DOM and detach it's list
	 * element from the DOM.
	 * @returns The contact object.
	 */
	Contact.prototype.detach = function() {
		if(this.$fullelem) {
			this.$fullelem.remove();
		}
		if(this.$listelem) {
			this.$listelem.detach();
			return this;
		}
	};

	/**
	 * Set a contacts list element as (un)checked
	 * @returns The contact object.
	 */
	Contact.prototype.setChecked = function(checked) {
		if(this.$listelem) {
			this.$listelem.find('input:checkbox').prop('checked', checked);
			return this;
		}
	};

	/**
	 * Set a contact to en/disabled depending on its permissions.
	 * @param boolean enabled
	 */
	Contact.prototype.setEnabled = function(enabled) {
		if(enabled) {
			this.$fullelem.find('#addproperty').show();
		} else {
			this.$fullelem.find('#addproperty').hide();
		}
		this.enabled = enabled;
		this.$fullelem.find('.value,.action,.parameter').each(function () {
			$(this).prop('disabled', !enabled);
		});
		$(document).trigger('status.contact.enabled', enabled);
	};

	/**
	 * Add a contact to data store.
	 * @params params. An object which can contain the optional properties:
	 *		aid: The id of the addressbook to add the contact to. Per default it will be added to the first.
	 *		fn: The formatted name of the contact.
	 * @param cb Optional callback function which
	 * @returns The callback gets an object as argument with a variable 'status' of either 'success'
	 * or 'error'. On success the 'data' property of that object contains the contact id as 'id', the
	 * addressbook id as 'aid' and the contact data structure as 'details'.
	 * TODO: Use Storage for adding and make sure to get all metadata.
	 */
	Contact.prototype.add = function(params, cb) {
		var self = this;
		$.when(this.storage.addContact(this.metadata.backend, this.metadata.parent))
			.then(function(response) {
			if(!response.error) {
				self.id = String(response.data.metadata.id);
				self.metadata = response.data.metadata;
				self.data = response.data.data;
				self.$groupSelect.multiselect('enable');
				// Add contact to current group
				if(self.groupprops && self.groupprops.currentgroup.id !== 'all'
					&& self.groupprops.currentgroup.id !== 'fav') {
					if(!self.data.CATEGORIES) {
						self.addToGroup(self.groupprops.currentgroup.name);
						$(document).trigger('request.contact.addtogroup', {
							id: self.id,
							groupid: self.groupprops.currentgroup.id
						});
						self.$groupSelect.find('option[value="' + self.groupprops.currentgroup.id + '"]')
							.attr('selected', 'selected');
						self.$groupSelect.multiselect('refresh');
					}
				}
				$(document).trigger('status.contact.added', {
					id: self.id,
					contact: self
				});
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				return false;
			}
			if(typeof cb == 'function') {
				cb(response);
			}
		});
	};
	/**
	 * Delete contact from data store and remove it from the DOM
	 * @param cb Optional callback function which
	 * @returns An object with a variable 'status' of either success
	 *	or 'error'
	 */
	Contact.prototype.destroy = function(cb) {
		var self = this;
		$.when(this.storage.deleteContact(
			this.metadata.backend,
			this.metadata.parent,
			this.id)
		).then(function(response) {
		//$.post(OC.filePath('contacts', 'ajax', 'contact/delete.php'),
		//	   {id: this.id}, function(response) {
			if(!response.error) {
				if(self.$listelem) {
					self.$listelem.remove();
				}
				if(self.$fullelem) {
					self.$fullelem.remove();
				}
			}
			if(typeof cb == 'function') {
				if(response.error) {
					cb(response);
				} else {
					cb({id:self.id});
				}
			}
		});
	};

	Contact.prototype.argumentsFor = function(obj) {
		var args = {};
		var ptype = this.propertyTypeFor(obj);
		args['name'] = ptype;

		if(this.multi_properties.indexOf(ptype) !== -1) {
			args['checksum'] = this.checksumFor(obj);
		}

		if($(obj).hasClass('propertycontainer')) {
			if($(obj).is('select[data-element="categories"]')) {
				args['value'] = [];
				$.each($(obj).find(':selected'), function(idx, e) {
					args['value'].push($(e).text());
				});
			} else {
				args['value'] = $(obj).val();
			}
		} else {
			var $elements = this.propertyContainerFor(obj)
				.find('input.value,select.value,textarea.value');
			if($elements.length > 1) {
				args['value'] = [];
				$.each($elements, function(idx, e) {
					args['value'].push($(e).val());
				});
			} else {
				args['value'] = $elements.val();
			}
		}
		return args;
	};

	Contact.prototype.queryStringFor = function(obj) {
		var q = 'id=' + this.id;
		var ptype = this.propertyTypeFor(obj);
		q += '&name=' + ptype;

		if(this.multi_properties.indexOf(ptype) !== -1) {
			q += '&checksum=' + this.checksumFor(obj);
		}

		if($(obj).hasClass('propertycontainer')) {
			if($(obj).is('select[data-element="categories"]')) {
				$.each($(obj).find(':selected'), function(idx, e) {
					q += '&value=' + encodeURIComponent($(e).text());
				});
			} else {
				q += '&value=' + encodeURIComponent($(obj).val());
			}
		} else {
			var $elements = this.propertyContainerFor(obj)
				.find('input.value,select.value,textarea.value,.parameter');
			if($elements.length > 1) {
				q += '&' + $elements.serialize();
			} else {
				q += '&value=' + encodeURIComponent($elements.val());
			}
		}
		return q;
	};

	Contact.prototype.propertyContainerFor = function(obj) {
		return $(obj).hasClass('propertycontainer')
			? $(obj)
			: $(obj).parents('.propertycontainer').first();
	};

	Contact.prototype.checksumFor = function(obj) {
		return this.propertyContainerFor(obj).data('checksum');
	};

	Contact.prototype.valueFor = function(obj) {
		var $container = this.propertyContainerFor(obj);
		console.assert($container.length > 0, 'Couldn\'t find container for ' + $(obj));
		return $container.is('input.value')
			? $container.val()
			: (function() {
				var $elem = $container.find('textarea.value,input.value:not(:checkbox)');
				console.assert($elem.length > 0, 'Couldn\'t find value for ' + $container.data('element'));
				if($elem.length === 1) {
					return $elem.val();
				} else if($elem.length > 1) {
					var retval = [];
					$.each($elem, function(idx, e) {
						retval[parseInt($(e).attr('name').substr(6,1))] = $(e).val();
					});
					return retval;
				}
			})();
	};

	Contact.prototype.parametersFor = function(obj, asText) {
		var parameters = [];
		$.each(this.propertyContainerFor(obj)
			.find('select.parameter,input:checkbox:checked.parameter,textarea'), // Why do I look for textarea?
			   function(i, elem) {
			var $elem = $(elem);
			var paramname = $elem.data('parameter');
			if(!parameters[paramname]) {
				parameters[paramname] = [];
			}
			if($elem.is(':checkbox')) {
				if(asText) {
					parameters[paramname].push($elem.attr('title'));
				} else {
					parameters[paramname].push($elem.attr('value'));
				}
			} else if($elem.is('select')) {
				$.each($elem.find(':selected'), function(idx, e) {
					if(asText) {
						parameters[paramname].push($(e).text());
					} else {
						parameters[paramname].push($(e).val());
					}
				});
			}
		});
		return parameters;
	};

	Contact.prototype.propertyTypeFor = function(obj) {
		var ptype = this.propertyContainerFor(obj).data('element');
		return ptype ? ptype.toUpperCase() : null;
	};

	/**
	 * Render an element item to be shown during drag.
	 * @return A jquery object
	 */
	Contact.prototype.renderDragItem = function() {
		if(typeof this.$dragelem === 'undefined') {
			this.$dragelem = this.$dragTemplate.octemplate({
				id: this.id,
				name: this.getPreferredValue('FN', '')
			});
		}
		return this.$dragelem;
	}

	/**
	 * Render the list item
	 * @return A jquery object to be inserted in the DOM
	 */
	Contact.prototype.renderListItem = function(isnew) {
		this.$listelem = this.$listTemplate.octemplate({
			id: this.id,
			parent: this.metadata.parent,
			backend: this.metadata.backend,
			name: this.getPreferredValue('FN', ''),
			email: this.getPreferredValue('EMAIL', ''),
			tel: this.getPreferredValue('TEL', ''),
			adr: this.getPreferredValue('ADR', []).clean('').join(', '),
			categories: this.getPreferredValue('CATEGORIES', [])
				.clean('').join(' / ')
		});
		if(this.metadata.owner !== OC.currentUser
				&& !(this.metadata.permissions & OC.PERMISSION_UPDATE
				|| this.metadata.permissions & OC.PERMISSION_DELETE)) {
			this.$listelem.find('input:checkbox').prop('disabled', true).css('opacity', '0');
		}
		return this.$listelem;
	};

	/**
	 * Render the full contact
	 * @return A jquery object to be inserted in the DOM
	 */
	Contact.prototype.renderContact = function(groupprops) {
		var self = this;
		this.groupprops = groupprops;
		
		var buildGroupSelect = function(availableGroups) {
			//this.$groupSelect.find('option').remove();
			$.each(availableGroups, function(idx, group) {
				var $option = $('<option value="' + group.id + '">' + group.name + '</option>');
				if(self.inGroup(group.name)) {
					$option.attr('selected', 'selected');
				}
				self.$groupSelect.append($option);
			});
			self.$groupSelect.multiselect({
				header: false,
				selectedList: 3,
				noneSelectedText: self.$groupSelect.attr('title'),
				selectedText: t('contacts', '# groups')
			});
			self.$groupSelect.bind('multiselectclick', function(event, ui) {
				var action = ui.checked ? 'addtogroup' : 'removefromgroup';
				console.assert(typeof self.id === 'string', 'ID is not a string')
				$(document).trigger('request.contact.' + action, {
					id: self.id,
					groupid: parseInt(ui.value)
				});
				if(ui.checked) {
					self.addToGroup(ui.text);
				} else {
					self.removeFromGroup(ui.text);
				}
			});
			if(!self.id) {
				self.$groupSelect.multiselect('disable');
			}
		};
		
		var buildAddressBookSelect = function(availableAddressBooks) {
			console.log('address books', availableAddressBooks.length, availableAddressBooks);
			/* TODO:
			 * - Check address books permissions.
			 * - Add method to change address book.
			 */
			$.each(availableAddressBooks, function(idx, addressBook) {
				console.log('addressBook', idx, addressBook);
				var $option = $('<option data-backend="'
					+ addressBook.backend + '" value="' + addressBook.id + '">'
					+ addressBook.displayname + '(' + addressBook.backend + ')</option>');
				if(self.metadata.parent === addressBook.id
					&& self.metadata.backend === addressBook.backend) {
					$option.attr('selected', 'selected');
				}
				self.$addressBookSelect.append($option);
			});
			self.$addressBookSelect.multiselect({
				header: false,
				selectedList: 3,
				noneSelectedText: self.$addressBookSelect.attr('title'),
				selectedText: t('contacts', '# groups')
			});
			if(self.id) {
				self.$addressBookSelect.multiselect('disable');
			}
		};

		var n = this.getPreferredValue('N', ['', '', '', '', '']);
		//console.log('Contact.renderContact', this.data);
		var values = this.data
			? {
				id: this.id,
				favorite:groupprops.favorite ? 'active' : '',
				name: this.getPreferredValue('FN', ''),
				n0: n[0]||'', n1: n[1]||'', n2: n[2]||'', n3: n[3]||'', n4: n[4]||'',
				nickname: this.getPreferredValue('NICKNAME', ''),
				title: this.getPreferredValue('TITLE', ''),
				org: this.getPreferredValue('ORG', []).clean('').join(', '), // TODO Add parts if more than one.
				bday: this.getPreferredValue('BDAY', '').length >= 10
					? $.datepicker.formatDate('dd-mm-yy',
						$.datepicker.parseDate('yy-mm-dd',
							this.getPreferredValue('BDAY', '').substring(0, 10)))
					: '',
				note: this.getPreferredValue('NOTE', '')
				}
			: {id:'', favorite:'', name:'', nickname:'', title:'', org:'', bday:'', note:'', n0:'', n1:'', n2:'', n3:'', n4:''};
		this.$fullelem = this.$fullTemplate.octemplate(values).data('contactobject', this);

		this.$footer = this.$fullelem.find('footer');

		this.$fullelem.find('.tooltipped.rightwards.onfocus').tipsy({trigger: 'focus', gravity: 'w'});
		this.$fullelem.on('submit', function() {
			return false;
		});
		
		this.$groupSelect = this.$fullelem.find('#contactgroups');
		buildGroupSelect(groupprops.groups);
		
		if(Object.keys(this.parent.addressbooks).length > 1) {
			this.$addressBookSelect = this.$fullelem.find('#contactaddressbooks');
			buildAddressBookSelect(this.parent.addressbooks);
		}

		this.$addMenu = this.$fullelem.find('#addproperty');
		this.$addMenu.on('change', function(event) {
			//console.log('add', $(this).val());
			var $opt = $(this).find('option:selected');
			self.addProperty($opt, $(this).val());
			$(this).val('');
		});
		var $fullname = this.$fullelem.find('.fullname');
		this.$fullelem.find('.singleproperties').on('mouseenter', function() {
			$fullname.next('.edit').css('opacity', '1');
		}).on('mouseleave', function() {
			$fullname.next('.edit').css('opacity', '0');
		});
		$fullname.next('.edit').on('click keydown', function(event) {
			//console.log('edit name', event);
			$('.tipsy').remove();
			if(wrongKey(event)) {
				return;
			}
			$(this).css('opacity', '0');
			var $editor = $(this).next('.n.editor').first();
			var bodyListener = function(e) {
				if($editor.find($(e.target)).length == 0) {
					$editor.toggle('blind');
					$('body').unbind('click', bodyListener);
				}
			};
			$editor.toggle('blind', function() {
				$('body').bind('click', bodyListener);
			});
		});

		this.$fullelem.on('click keydown', '.delete', function(event) {
			$('.tipsy').remove();
			if(wrongKey(event)) {
				return;
			}
			self.deleteProperty({obj:event.target});
		});

		this.$footer.on('click keydown', 'button', function(event) {
			$('.tipsy').remove();
			if(wrongKey(event)) {
				return;
			}
			if($(this).is('.close') || $(this).is('.cancel')) {
				$(document).trigger('request.contact.close', {
					id: self.id
				});
			} else if($(this).is('.export')) {
				$(document).trigger('request.contact.export', self.metaData());
			} else if($(this).is('.delete')) {
				$(document).trigger('request.contact.delete', self.metaData());
			}
			return false;
		});
		this.$fullelem.on('keypress', '.value,.parameter', function(event) {
			if(event.keyCode === 13 && $(this).is('input')) {
				$(this).trigger('change');
				// Prevent a second save on blur.
				this.defaultValue = this.value;
				return false;
			} else if(event.keyCode === 27) {
				$(document).trigger('request.contact.close', {
					id: self.id
				});
			}
		});

		this.$fullelem.on('change', '.value,.parameter', function(event) {
			if($(this).hasClass('value') && this.value === this.defaultValue) {
				return;
			}
			console.log('change', this.defaultValue, this.value);
			this.defaultValue = this.value;
			self.saveProperty({obj:event.target});
		});

		this.$fullelem.find('[data-element="bday"]')
			.find('input').datepicker({
				dateFormat : 'dd-mm-yy'
		});
		this.$fullelem.find('.favorite').on('click', function () {
			var state = $(this).hasClass('active');
			if(!self.data) {
				return;
			}
			if(state) {
				$(this).switchClass('active', 'inactive');
			} else {
				$(this).switchClass('inactive', 'active');
			}
			$(document).trigger('request.contact.setasfavorite', {
				id: self.id,
				state: !state
			});
		});
		this.loadPhoto();
		if(!this.data) {
			// A new contact
			this.setEnabled(true);
			this.showActions(['cancel']);
			return this.$fullelem;
		}
		// Loop thru all single occurrence values. If not set hide the
		// element, if set disable the add menu entry.
		$.each(values, function(name, value) {
			if(typeof value === 'undefined') {
				return true; //continue
			}
			value = value.toString();
			if(self.multi_properties.indexOf(value.toUpperCase()) === -1) {
				if(!value.length) {
					self.$fullelem.find('[data-element="' + name + '"]').hide();
				} else {
					self.$addMenu.find('option[value="' + name.toUpperCase() + '"]').prop('disabled', true);
				}
			}
		});
		$.each(this.multi_properties, function(idx, name) {
			if(self.data[name]) {
				var $list = self.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				for(var p in self.data[name]) {
					if(typeof self.data[name][p] === 'object') {
						var property = self.data[name][p];
						//console.log(name, p, property);
						var $property = null;
						switch(name) {
							case 'TEL':
							case 'URL':
							case 'EMAIL':
								$property = self.renderStandardProperty(name.toLowerCase(), property);
								if(self.data[name].length === 1) {
									$property.find('input:checkbox[value="PREF"]').hide();
								}
								break;
							case 'ADR':
								$property = self.renderAddressProperty(idx, property);
								break;
							case 'IMPP':
								$property = self.renderIMProperty(property);
								if(self.data[name].length === 1) {
									$property.find('input:checkbox[value="PREF"]').hide();
								}
								break;
						}
						if(!$property) {
							continue;
						}
						//console.log('$property', $property);
						var meta = [];
						if(property.label) {
							if(!property.parameters['TYPE']) {
								property.parameters['TYPE'] = [];
							}
							property.parameters['TYPE'].push(property.label);
							meta.push(property.label);
						}
						for(var param in property.parameters) {
							//console.log('param', param);
							if(param.toUpperCase() == 'PREF') {
								var $cb = $property.find('input[type="checkbox"]');
								$cb.attr('checked', 'checked');
								meta.push($cb.attr('title'));
							}
							else if(param.toUpperCase() == 'TYPE') {
								for(var etype in property.parameters[param]) {
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
											meta.push($(this).text());
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
						var $meta = $property.find('.meta');
						if($meta.length) {
							$meta.html(meta.join('/'));
						}
						if(self.metadata.owner === OC.currentUser
								|| self.metadata.permissions & OC.PERMISSION_UPDATE
								|| self.metadata.permissions & OC.PERMISSION_DELETE) {
							$property.find('select.type[name="parameters[TYPE][]"]')
								.combobox({
									singleclick: true,
									classes: ['propertytype', 'float', 'label']
								});
						}
						$list.append($property);
					}
				}
			}
		});
		if(this.metadata.owner !== OC.currentUser
			&& !(this.metadata.permissions & OC.PERMISSION_UPDATE
				|| this.metadata.permissions & OC.PERMISSION_DELETE)) {
			this.setEnabled(false);
			this.showActions(['close', 'export']);
		} else {
			this.setEnabled(true);
			this.showActions(['close', 'add', 'export', 'delete']);
		}
		return this.$fullelem;
	};

	Contact.prototype.isEditable = function() {
		return ((this.metadata.owner === OC.currentUser)
			|| (this.metadata.permissions & OC.PERMISSION_UPDATE
				|| this.metadata.permissions & OC.PERMISSION_DELETE));
	};

	/**
	 * Render a simple property. Used for EMAIL and TEL.
	 * @return A jquery object to be injected in the DOM
	 */
	Contact.prototype.renderStandardProperty = function(name, property) {
		if(!this.detailTemplates[name]) {
			console.error('No template for', name);
			return;
		}
		var values = property
			? { value: property.value, checksum: property.checksum }
			: { value: '', checksum: 'new' };
		return this.detailTemplates[name].octemplate(values);
	};

	/**
	 * Render an ADR (address) property.
	 * @return A jquery object to be injected in the DOM
	 */
	Contact.prototype.renderAddressProperty = function(idx, property) {
		if(!this.detailTemplates['adr']) {
			console.warn('No template for adr', this.detailTemplates);
			return;
		}
		if(typeof idx === 'undefined') {
			if(this.data && this.data.ADR && this.data.ADR.length > 0) {
				idx = this.data.ADR.length - 1;
			} else {
				idx = 0;
			}
		}
		var values = property ? {
				value: property.value.clean('').join(', '),
				checksum: property.checksum,
				adr0: property.value[0] || '',
				adr1: property.value[1] || '',
				adr2: property.value[2] || '',
				adr3: property.value[3] || '',
				adr4: property.value[4] || '',
				adr5: property.value[5] || '',
				adr6: property.value[6] || '',
				idx: idx
			}
			: {value:'', checksum:'new', adr0:'', adr1:'', adr2:'', adr3:'', adr4:'', adr5:'', adr6:'', idx: idx};
		var $elem = this.detailTemplates['adr'].octemplate(values);
		var self = this;
		$elem.find('.tooltipped.downwards:not(.onfocus)').tipsy({gravity: 'n'});
		$elem.find('.tooltipped.rightwards.onfocus').tipsy({trigger: 'focus', gravity: 'w'});
		$elem.find('.display').on('click', function() {
			$(this).next('.listactions').hide();
			var $editor = $(this).siblings('.adr.editor').first();
			var $viewer = $(this);
			var bodyListener = function(e) {
				if($editor.find($(e.target)).length == 0) {
					$editor.toggle('blind');
					$viewer.slideDown(400, function() {
						var input = $editor.find('input').first();
						var val = self.valueFor(input);
						var params = self.parametersFor(input, true);
						$(this).find('.meta').html(params['TYPE'].join('/'));
						$(this).find('.adr').html(self.valueFor($editor.find('input').first()).clean('').join(', '));
						$(this).next('.listactions').css('display', 'inline-block');
						$('body').unbind('click', bodyListener);
					});
				}
			};
			$viewer.slideUp();
			$editor.toggle('blind', function() {
				$('body').bind('click', bodyListener);
			});
		});
		$elem.find('.value.city')
			.autocomplete({
				source: function( request, response ) {
					$.ajax({
						url: "http://ws.geonames.org/searchJSON",
						dataType: "jsonp",
						data: {
							featureClass: "P",
							style: "full",
							maxRows: 12,
							lang: $elem.data('lang'),
							name_startsWith: request.term
						},
						success: function( data ) {
							response( $.map( data.geonames, function( item ) {
								return {
									label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
									value: item.name,
									country: item.countryName
								};
							}));
						}
					});
				},
				minLength: 2,
				select: function( event, ui ) {
					if(ui.item && $.trim($elem.find('.value.country').val()).length == 0) {
						$elem.find('.value.country').val(ui.item.country);
					}
				}
			});
		$elem.find('.value.country')
			.autocomplete({
				source: function( request, response ) {
					$.ajax({
						url: "http://ws.geonames.org/searchJSON",
						dataType: "jsonp",
						data: {
							/*featureClass: "A",*/
							featureCode: "PCLI",
							/*countryBias: "true",*/
							/*style: "full",*/
							lang: lang,
							maxRows: 12,
							name_startsWith: request.term
						},
						success: function( data ) {
							response( $.map( data.geonames, function( item ) {
								return {
									label: item.name,
									value: item.name
								};
							}));
						}
					});
				},
				minLength: 2
			});
		return $elem;
	};

	/**
	 * Render an IMPP (Instant Messaging) property.
	 * @return A jquery object to be injected in the DOM
	 */
	Contact.prototype.renderIMProperty = function(property) {
		if(!this.detailTemplates['impp']) {
			console.warn('No template for impp', this.detailTemplates);
			return;
		}
		var values = property ? {
			value: property.value,
			checksum: property.checksum
		} : {value: '', checksum: 'new'};
		return this.detailTemplates['impp'].octemplate(values);
	};

	/**
	 * Set a thumbnail for the contact if a PHOTO property exists
	 */
	Contact.prototype.setThumbnail = function(refresh) {
		if(!this.data.thumbnail) {
			return;
		}
		var $elem = this.getListItemElement().find('td.name');
		if(!$elem.hasClass('thumbnail') && !refresh) {
			return;
		}
		$elem.removeClass('thumbnail');
		$elem.css('background-image', 'url(data:image/png;base64,' + this.data.thumbnail + ')');
	}

	/**
	 * Render the PHOTO property.
	 */
	Contact.prototype.loadPhoto = function(dontloadhandlers) {
		var self = this;
		var id = this.id || 'new',
			backend = this.metadata.backend,
			parent = this.metadata.parent,
			src;

		if(!this.$photowrapper) {
			this.$photowrapper = this.$fullelem.find('#photowrapper');
		}

		var finishLoad = function(image) {
			$(image).addClass('contactphoto');
			self.$photowrapper.css({width: image.width + 10, height: image.height + 10});
			self.$photowrapper.removeClass('loading').removeClass('wait');
			$(image).insertAfter($phototools).fadeIn();
		};

		this.$photowrapper.addClass('loading').addClass('wait');
		var $phototools = this.$fullelem.find('#phototools');
		if(this.getPreferredValue('PHOTO', null) === null) {
			$.when(this.storage.getDefaultPhoto())
				.then(function(image) {
					$('img.contactphoto').detach();
					finishLoad(image);
				});
		} else {
			$.when(this.storage.getContactPhoto(backend, parent, id))
				.then(function(image) {
					$('img.contactphoto').remove();
					finishLoad(image);
				})
				.fail(function(defaultImage) {
					$('img.contactphoto').remove();
					finishLoad(defaultImage);
				});
		}

		if(!dontloadhandlers && this.isEditable()) {
			this.$photowrapper.on('mouseenter', function(event) {
				if($(event.target).is('.favorite') || !self.data) {
					return;
				}
				$phototools.slideDown(200);
			}).on('mouseleave', function() {
				$phototools.slideUp(200);
			});
			$phototools.hover( function () {
				$(this).removeClass('transparent');
			}, function () {
				$(this).addClass('transparent');
			});
			$phototools.find('li a').tipsy();

			$phototools.find('.edit').on('click', function() {
				$(document).trigger('request.edit.contactphoto', self.metaData());
			});
			$phototools.find('.cloud').on('click', function() {
				$(document).trigger('request.select.contactphoto.fromcloud', self.metaData());
			});
			$phototools.find('.upload').on('click', function() {
				$(document).trigger('request.select.contactphoto.fromlocal', self.metaData());
			});
			if(this.data && this.data.PHOTO) {
				$phototools.find('.delete').show();
				$phototools.find('.edit').show();
			} else {
				$phototools.find('.delete').hide();
				$phototools.find('.edit').hide();
			}
			$(document).bind('status.contact.photoupdated', function(e, result) {
				self.loadPhoto(true);
				var refreshstr = '&refresh='+Math.random();
				// TODO: Use setThumbnail
				self.getListItemElement().find('td.name')
					.css('background', 'url(' + OC.filePath('', '', 'remote.php')
						+'/contactthumbnail?backend='+backend+'&parent='+parent+'id='+id+refreshstr + ')');
			});
		}
	};

	/**
	 * Get the jquery element associated with this object
	 */
	Contact.prototype.getListItemElement = function() {
		if(!this.$listelem) {
			this.renderListItem();
		}
		return this.$listelem;
	};

	/**
	 * Get the preferred value for a property.
	 * If a preferred value is not found the first one will be returned.
	 * @param string name The name of the property like EMAIL, TEL or ADR.
	 * @param def A default value to return if nothing is found.
	 */
	Contact.prototype.getPreferredValue = function(name, def) {
		var pref = def, found = false;
		if(this.data && this.data[name]) {
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
	};

	/**
	 * Returns true/false depending on the contact being in the
	 * specified group.
	 * @param String name The group name (not case-sensitive)
	 * @returns Boolean
	 */
	Contact.prototype.inGroup = function(name) {
		var categories = this.getPreferredValue('CATEGORIES', []);
		var found = false;

		$.each(categories, function(idx, category) {
			if(name.toLowerCase() == $.trim(category).toLowerCase()) {
				found = true
				return false;
			}
		});

		return found;
	};

	/**
	 * Add this contact to a group
	 * @param String name The group name
	 */
	Contact.prototype.addToGroup = function(name) {
		console.log('addToGroup', name);
		if(!this.data.CATEGORIES) {
			this.data.CATEGORIES = [{value:[name]}];
		} else {
			if(this.inGroup(name)) {
				return;
			}
			this.data.CATEGORIES[0].value.push(name);
			if(this.$listelem) {
				this.$listelem.find('td.categories')
					.text(this.getPreferredValue('CATEGORIES', []).clean('').join(' / '));
			}
		}
		this.saveProperty({name:'CATEGORIES', value:this.data.CATEGORIES[0].value.join(',') });
	};

	/**
	 * Remove this contact to a group
	 * @param String name The group name
	 */
	Contact.prototype.removeFromGroup = function(name) {
		console.log('removeFromGroup', name);
		if(!this.data.CATEGORIES) {
			return;
		} else {
			var found = false;
			var categories = [];
			$.each(this.data.CATEGORIES[0].value, function(idx, category) {
				if(name.toLowerCase() === category.toLowerCase()) {
					found = true;
				} else {
					categories.push(category);
				}
			});
			if(!found) {
				return;
			}
			this.data.CATEGORIES[0].value = categories;
			//this.data.CATEGORIES[0].value.splice(this.data.CATEGORIES[0].value.indexOf(name), 1);
			if(this.$listelem) {
				this.$listelem.find('td.categories')
					.text(categories.join(' / '));
			}
		}
		this.saveProperty({name:'CATEGORIES', value:this.data.CATEGORIES[0].value.join(',') });
	};

	Contact.prototype.setCurrent = function(on) {
		if(on) {
			this.$listelem.addClass('active');
		} else {
			this.$listelem.removeClass('active');
		}
		$(document).trigger('status.contact.currentlistitem', {
			id: this.id,
			pos: Math.round(this.$listelem.position().top),
			height: Math.round(this.$listelem.height())
		});
	};

	Contact.prototype.next = function() {
		// This used to work..?
		//var $next = this.$listelem.next('tr:visible');
		var $next = this.$listelem.nextAll('tr').filter(':visible').first();
		if($next.length > 0) {
			this.$listelem.removeClass('active');
			$next.addClass('active');
			$(document).trigger('status.contact.currentlistitem', {
				id: String($next.data('id')),
				pos: Math.round($next.position().top),
				height: Math.round($next.height())
			});
		}
	};

	Contact.prototype.prev = function() {
		//var $prev = this.$listelem.prev('tr:visible');
		var $prev = this.$listelem.prevAll('tr').filter(':visible').first();
		if($prev.length > 0) {
			this.$listelem.removeClass('active');
			$prev.addClass('active');
			$(document).trigger('status.contact.currentlistitem', {
				id: String($prev.data('id')),
				pos: Math.round($prev.position().top),
				height: Math.round($prev.height())
			});
		}
	};

	var ContactList = function(
			storage,
			contactlist,
			contactlistitemtemplate,
			contactdragitemtemplate,
			contactfulltemplate,
			contactdetailtemplates
		) {
		//console.log('ContactList', contactlist, contactlistitemtemplate, contactfulltemplate, contactdetailtemplates);
		var self = this;
		this.length = 0;
		this.contacts = {};
		this.addressbooks = {};
		this.deletionQueue = [];
		this.storage = storage;
		this.$contactList = contactlist;
		this.$contactDragItemTemplate = contactdragitemtemplate;
		this.$contactListItemTemplate = contactlistitemtemplate;
		this.$contactFullTemplate = contactfulltemplate;
		this.contactDetailTemplates = contactdetailtemplates;
		this.$contactList.scrollTop(0);
		this.getAddressBooks();
		$(document).bind('status.contact.added', function(e, data) {
			self.length += 1;
			self.contacts[String(data.id)] = data.contact;
			self.insertContact(data.contact.renderListItem(true));
		});

		$(document).bind('status.contact.updated', function(e, data) {
			if(['FN', 'EMAIL', 'TEL', 'ADR', 'CATEGORIES'].indexOf(data.property) !== -1) {
				data.contact.getListItemElement().remove();
				self.insertContact(self.contacts[String(data.contact.id)].renderListItem(true));
			}
		});
	};

	/**
	 * Get the number of contacts in the list
	 * @return integer
	 */
	ContactList.prototype.count = function() {
		return Object.keys(this.contacts.contacts).length
	}

	/**
	* Show/hide contacts belonging to an addressbook.
	* @param int aid. Addressbook id.
	* @param boolean show. Whether to show or hide.
	* @param boolean hideothers. Used when showing shared addressbook as a group.
	*/
	ContactList.prototype.showFromAddressbook = function(aid, show, hideothers) {
		console.log('ContactList.showFromAddressbook', aid, show);
		aid = String(aid);
		for(var contact in this.contacts) {
			if(this.contacts[contact].metadata.parent === aid) {
				this.contacts[contact].getListItemElement().toggle(show);
			} else if(hideothers) {
				this.contacts[contact].getListItemElement().hide();
			}
		}
	};

	/**
	* Show/hide contacts belonging to shared addressbooks.
	* @param boolean show. Whether to show or hide.
	*/
	ContactList.prototype.showSharedAddressbooks = function(show) {
		console.log('ContactList.showSharedAddressbooks', show);
		for(var contact in this.contacts) {
			if(this.contacts[contact].metadata.owner !== OC.currentUser) {
				if(show) {
					this.contacts[contact].getListItemElement().show();
				} else {
					this.contacts[contact].getListItemElement().hide();
				}
			}
		}
	};

	/**
	* Show contacts in list
	* @param Array contacts. A list of contact ids.
	*/
	ContactList.prototype.showContacts = function(contacts) {
		console.log('showContacts', contacts);
		var self = this;
		if(contacts.length === 0) {
			// ~5 times faster
			$('tr:visible.contact').hide();
			return;
		}
		if(contacts === 'all') {
			// ~2 times faster
			var $elems = $('tr.contact:not(:visible)');
			$elems.show();
			$.each($elems, function(idx, elem) {
				try {
					var id = $(elem).data('id');
					self.contacts[id].setThumbnail();
				} catch(e) {
					console.warn('Failed getting id from', $elem, e);
				}
			});
			return;
		}
		for(var id in this.contacts) {
			var contact = this.findById(id);
			if(contact === null) {
				continue;
			}
			if(contacts.indexOf(String(id)) === -1) {
				contact.getListItemElement().hide();
			} else {
				contact.getListItemElement().show();
				contact.setThumbnail();
			}
		}
	};

	ContactList.prototype.contactPos = function(id) {
		var contact = this.findById(id);
		if(contact === null) {
			return 0;
		}
		
		var $elem = contact.getListItemElement();
		var pos = $elem.offset().top - this.$contactList.offset().top + this.$contactList.scrollTop();
		return pos;
	};

	ContactList.prototype.hideContact = function(id) {
		var contact = this.findById(id);
		if(contact === null) {
			return false;
		}
		contact.hide();
	};

	ContactList.prototype.closeContact = function(id) {
		var contact = this.findById(id);
		if(contact === null) {
			return false;
		}
		contact.close();
	};

	/**
	* Returns a Contact object by searching for its id
	* @param id the id of the node
	* @return the Contact object or undefined if not found.
	* FIXME: If continious loading is reintroduced this will have
	* to load the requested contact if not in list.
	*/
	ContactList.prototype.findById = function(id) {
		if(!id) {
			console.warn('ContactList.findById: id missing');
			console.trace();
			return false;
		}
		id = String(id);
		if(typeof this.contacts[id] === 'undefined') {
			console.warn('Could not find contact with id', id);
			console.trace();
			return null;
		}
		return this.contacts[String(id)];
	};

	/**
	 * @param object data An object or array of objects containing contact identification
	 * {
	 * 	contactid: '1234',
	 * 	addressbookid: '4321',
	 * 	backend: 'database'
	 * }
	 */
	ContactList.prototype.delayedDelete = function(data) {
		console.log('delayedDelete, data:', typeof data, data);
		var self = this;
		if(!utils.isArray(data)) {
			this.currentContact = null;
			//self.$contactList.show();
			if(data instanceof Contact) {
				this.deletionQueue.push(data);
			} else {
				var contact = this.findById(data.contactid);
				this.deletionQueue.push(contact);
			}
		} else if(utils.isArray(data)) {
			$.each(data, function(idx, contact) {
				console.log('delayedDelete, meta:', contact);
				self.deletionQueue.push(contact);
			});
			//$.extend(this.deletionQueue, data);
		} else {
			throw { name: 'WrongParameterType', message: 'ContactList.delayedDelete only accept objects or arrays.'};
		}
		console.log('delayedDelete, deletionQueue', this.deletionQueue);
		$.each(this.deletionQueue, function(idx, contact) {
			console.log('delayedDelete', contact);
			contact.detach().setChecked(false);
		});
		console.log('deletionQueue', this.deletionQueue);
		if(!window.onbeforeunload) {
			window.onbeforeunload = function(e) {
				e = e || window.event;
				var warn = t('contacts', 'Some contacts are marked for deletion, but not deleted yet. Please wait for them to be deleted.');
				if (e) {
					e.returnValue = String(warn);
				}
				return warn;
			};
		}
		if(this.$contactList.find('tr:visible').length === 0) {
			$(document).trigger('status.visiblecontacts');
		}
		OC.notify({
			message:t('contacts','Click to undo deletion of {num} contacts', {num: self.deletionQueue.length}),
			//timeout:5,
			timeouthandler:function() {
				console.log('timeout');
				// Don't fire all deletes at once
				self.deletionTimer = setInterval(function() {
					self.deleteContacts();
				}, 500);
			},
			clickhandler:function() {
				console.log('clickhandler');
				$.each(self.deletionQueue, function(idx, contact) {
					self.insertContact(contact.getListItemElement());
				});
				OC.notify({cancel:true});
				OC.notify({message:t('contacts', 'Cancelled deletion of {num}', {num: self.deletionQueue.length})});
				self.deletionQueue = [];
				window.onbeforeunload = null;
			}
		});
	};

	/**
	* Delete contacts in the queue
	*/
	ContactList.prototype.deleteContacts = function() {
		var self = this;
		console.log('ContactList.deleteContacts, deletionQueue', this.deletionQueue);
		if(typeof this.deletionTimer === 'undefined') {
			console.log('No deletion timer!');
			window.onbeforeunload = null;
			return;
		}

		var contact = this.deletionQueue.shift();
		if(typeof contact === 'undefined') {
			clearInterval(this.deletionTimer);
			delete this.deletionTimer;
			window.onbeforeunload = null;
			return;
		}

		// Let contact remove itself.
		var id = contact.getId();
		contact.destroy(function(response) {
			console.log('deleteContact', response, self.length);
			if(!response.error) {
				delete self.contacts[id];
				$(document).trigger('status.contact.deleted', {
					id: id
				});
				self.length -= 1;
				if(self.length === 0) {
					$(document).trigger('status.nomorecontacts');
				}
			} else {
				OC.notify({message:response.message});
			}
		});
	};

	/**
	* Opens the contact with this id in edit mode
	* @param id the id of the contact
	* @returns A jquery object to be inserted in the DOM.
	*/
	ContactList.prototype.showContact = function(id, props) {
		var contact = this.findById(id);
		if(contact === null) {
			return false;
		}
		this.currentContact = id;
		console.log('Contacts.showContact', id, contact, this.contacts);
		return contact.renderContact(props);
	};

	/**
	 * Insert a rendered contact list item into the list
	 * @param contact jQuery object.
	 */
	ContactList.prototype.insertContact = function($contact) {
		$contact.find('td.name').draggable({
			distance: 10,
			revert: 'invalid',
			//containment: '#content',
			helper: function (e,ui) {
				return $(this).clone().appendTo('body').css('zIndex', 5).show();
			},
			opacity: 0.8,
			scope: 'contacts'
		});
		var name = $contact.find('.nametext').text().toLowerCase();
		var added = false;
		this.$contactList.find('tr').each(function() {
			if ($(this).find('.nametext').text().toLowerCase().localeCompare(name) > 0) {
				$(this).before($contact);
				added = true;
				return false;
			}
		});
		if(!added) {
			this.$contactList.append($contact);
		}
		$contact.show();
		return $contact;
	};

	/**
	* Add contact
	* @param object props
	*/
	ContactList.prototype.addContact = function(props) {
		var addressBook = this.addressbooks[Object.keys(this.addressbooks)[0]]
		var metadata = {
			parent: addressBook.id,
			backend: addressBook.backend,
			permissions: addressBook.permissions,
			owner: addressBook.owner
		};
		var contact = new Contact(
			this,
			null,
			metadata,
			null,
			this.$contactListItemTemplate,
			this.$contactDragItemTemplate,
			this.$contactFullTemplate,
			this.contactDetailTemplates
		);
		if(utils.isUInt(this.currentContact)) {
			console.assert(typeof this.currentContact == 'number', 'this.currentContact is not a number');
			this.contacts[this.currentContact].close();
		}
		return contact.renderContact(props);
	};

	/**
	 * Get contacts selected in list
	 *
	 * @returns array of contact ids.
	 */
	ContactList.prototype.getSelectedContacts = function() {
		var contacts = [];

		var self = this;
		$.each(this.$contactList.find('tr > td > input:checkbox:visible:checked'), function(a, b) {
			var id = String($(b).parents('tr').first().data('id'));
			contacts.push(self.contacts[id]);
		});
		return contacts;
	};

	ContactList.prototype.setCurrent = function(id, deselect_other) {
		console.log('ContactList.setCurrent', id);
		if(!id) {
			return;
		}
		var self = this;
		if(deselect_other === true) {
			$.each(this.contacts, function(contact) {
				self.contacts[contact].setCurrent(false);
			});
		}
		this.contacts[String(id)].setCurrent(true);
	};

	// Should only be neccesary with progressive loading, but it's damn fast, so... ;)
	ContactList.prototype.doSort = function() {
		var self = this;
		var rows = this.$contactList.find('tr').get();

		rows.sort(function(a, b) {
			return $(a).find('td.name').text().toUpperCase().localeCompare($(b).find('td.name').text().toUpperCase());
		});

		// TODO: Test if I couldn't just append rows.
		var items = [];
		$.each(rows, function(index, row) {
			items.push(row);
			if(items.length === 100) {
				self.$contactList.append(items);
				items = [];
			}
		});
		if(items.length > 0) {
			self.$contactList.append(items);
		}
	};

	/**
	* Save addressbook data
	* @param int id
	*/
	ContactList.prototype.unsetAddressbook = function(id) {
		delete this.addressbooks[id];
	};

	/**
	* Save addressbook data
	* @param object book
	*/
	ContactList.prototype.setAddressbook = function(book) {
		console.log('setAddressbook', book.id, this.addressbooks);
		var id = String(book.id);
		this.addressbooks[id] = book;
	};

	/**
	* Load contacts
	* @param int offset
	*/
	ContactList.prototype.getAddressBooks = function() {
		var self = this;
		$.when(this.storage.getAddressBooksForUser()).then(function(response) {
			console.log('ContactList.getAddressBooks', response);
			if(!response.error) {
				var num = response.data.addressbooks.length;
				$.each(response.data.addressbooks, function(idx, addressBook) {
					self.setAddressbook(addressBook);
					self.loadContacts(
						addressBook['backend'],
						addressBook['id'],
						function(cbresponse) {
							console.log('loaded', idx, cbresponse);
							num -= 1;
							if(num === 0) {
								if(self.length > 0) {
									setTimeout(function() {
										self.doSort(); // TODO: Test this
										self.setCurrent(self.$contactList.find('tr:visible').first().data('id'), false);
									}
									, 2000);
								}
								$(document).trigger('status.contacts.loaded', {
									status: true,
									numcontacts: self.length
								});
								if(self.length === 0) {
									$(document).trigger('status.nomorecontacts');
								}
							}
							if(cbresponse.error) {
								$(document).trigger('status.contact.error', {
									message:
										t('contacts', 'Failed loading contacts from {addressbook}: {error}',
											{addressbook:addressBook['displayname'], error:err})
								});
							}
						});
				});
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				return false;
			}
		})
		.fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ', ' + error;
			console.log( "Request Failed: " + err);
			$(document).trigger('status.contact.error', {
				message: t('contacts', 'Failed loading address books: {error}', {error:err})
			});
		});
	};

	/**
	* Load contacts
	* @param int offset
	*/
	ContactList.prototype.loadContacts = function(backend, addressBookId, cb) {
		var self = this;
		$.when(this.storage.getContacts(backend, addressBookId)).then(function(response) {
			console.log('ContactList.loadContacts', response);
			if(!response.error) {
				var items = [];
				$.each(response.data.contacts, function(c, contact) {
					var id = String(contact.metadata.id);
					contact.metadata.backend = backend;
					self.contacts[id]
						= new Contact(
							self,
							id,
							contact.metadata,
							contact.data,
							self.$contactListItemTemplate,
							self.$contactDragItemTemplate,
							self.$contactFullTemplate,
							self.contactDetailTemplates
						);
					self.length +=1;
					var $item = self.contacts[id].renderListItem();
					items.push($item.get(0));
					$item.find('td.name').draggable({
						cursor: 'move',
						distance: 10,
						revert: 'invalid',
						helper: function (e,ui) {
							return self.contacts[id].renderDragItem().appendTo('body');
						},
						opacity: 1,
						scope: 'contacts'
					});
					if(items.length === 100) {
						self.$contactList.append(items);
						items = [];
					}
				});
				if(items.length > 0) {
					self.$contactList.append(items);
				}
				cb({error:false});
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				cb({
					error:true,
					message: response.message
				});
			}
		})
		.fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ', ' + error;
			console.log( "Request Failed: " + err);
			cb({error: true, message: err});
		});
	};

	OC.Contacts.ContactList = ContactList;

})(window, jQuery, OC);
