OC.Contacts = OC.Contacts || {};


(function($) {

	/**
	* An item which binds the appropriate html and event handlers
	* @param parent the parent ContactList
	* @param id The integer contact id.
	* @param access An access object containing and 'owner' string variable and an integer 'permissions' variable.
	* @param data the data used to populate the contact
	* @param listtemplate the jquery object used to render the contact list item
	* @param fulltemplate the jquery object used to render the entire contact
	* @param detailtemplates A map of jquery objects used to render the contact parts e.g. EMAIL, TEL etc.
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
	}

	Contact.prototype.addProperty = function($option, name) {
		console.log('Contact.addProperty', name)
		switch(name) {
			case 'NICKNAME':
			case 'TITLE':
			case 'ORG':
			case 'BDAY':
				this.$fullelem.find('[data-element="' + name.toLowerCase() + '"]').show();
				$option.prop('disabled', true);
				break;
			case 'TEL':
			case 'URL':
			case 'EMAIL':
				$elem = this.renderStandardProperty(name.toLowerCase());
				var $list = this.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				$list.append($elem);
				break;
			case 'ADR':
				$elem = this.renderAddressProperty();
				var $list = this.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				$list.append($elem);
				break;
			case 'IMPP':
				$elem = this.renderIMProperty();
				var $list = this.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				$list.append($elem);
				break;
		}
	}

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
			id: this.id
		};
		if(this.multi_properties.indexOf(element) !== -1) {
			params['checksum'] = this.checksumFor(obj);
		}
		this.setAsSaving(obj, true);
		var self = this;
		$.post(OC.filePath('contacts', 'ajax', 'contact/deleteproperty.php'), params, function(jsondata) {
			if(!jsondata) {
				$(document).trigger('status.contact.error', {
					status: 'error',
					message: t('contacts', 'Network or server error. Please inform administrator.'),
				});
				self.setAsSaving(obj, false);
				return false;
			}
			if(jsondata.status == 'success') {
				// TODO: Test if removing from internal data structure works
				if(self.multi_properties.indexOf(element) !== -1) {
					// First find out if an existing element by looking for checksum
					var checksum = self.checksumFor(obj);
					if(checksum) {
						for(var i in self.data[element]) {
							if(self.data[element][i].checksum === checksum) {
								// Found it
								delete self.data[element][i];
								break;
							}
						}
					}
					$container.remove();
				} else {
					self.setAsSaving(obj, false);
					self.$fullelem.find('[data-element="' + element.toLowerCase() + '"]').hide();
					$container.find('input.value').val('');
					self.$addMenu.find('option[value="' + element.toUpperCase() + '"]').prop('disabled', false);
				}
				return true;
			} else {
				$(document).trigger('status.contact.error', {
					status: 'error',
					message: jsondata.data.message,
				});
				self.setAsSaving(obj, false);
				return false;
			}
		},'json');
	}

	/**
	 * @brief Act on change of a property.
	 * If this is a new contact it will first be saved to the datastore and a
	 * new datastructure will be added to the object. FIXME: Not implemented yet.
	 * If the obj argument is not provided 'name' and 'value' MUST be provided
	 * and this is only allowed for single elements like N, FN, CATEGORIES.
	 * @param obj. The form form field that has changed.
	 * @param name. The optional name of the element.
	 * @param value. The optional value.
	 */
	Contact.prototype.saveProperty = function(params) {
		console.log('Contact.saveProperty', params);
		var obj = null;
		var element = null;
		var q = 'id=' + this.id + '&';
		if(params.obj) {
			obj = params.obj;
			q = this.queryStringFor(obj);
			element = this.propertyTypeFor(obj);
		} else {
			element = params.name;
			q += 'value=' + encodeURIComponent(params.value) + '&name=' + element;
		}
		console.log('q', q);
		var self = this;
		this.setAsSaving(obj, true);
		$.post(OC.filePath('contacts', 'ajax', 'contact/saveproperty.php'), q, function(jsondata){
			if(!jsondata) {
				$(document).trigger('status.contact.error', {
					status: 'error',
					message: t('contacts', 'Network or server error. Please inform administrator.'),
				});
				self.setAsSaving(obj, false);
				return false;
			}
			if(jsondata.status == 'success') {
				console.log(self.data[element]);
				if(!self.data[element]) {
					self.data[element] = [];
				}
				if(self.multi_properties.indexOf(element) !== -1) {
					// First find out if an existing element by looking for checksum
					var checksum = self.checksumFor(obj);
					if(checksum) {
						for(var i in self.data[element]) {
							if(self.data[element][i].checksum === checksum) {
								self.data[element][i] = {
									name: element,
									value: self.valueFor(obj),
									parameters: self.parametersFor(obj),
									checksum: jsondata.data.checksum,
								}
								break;
							}
						}
					} else {
						self.data[element].push({
							name: element,
							value: self.valueFor(obj),
							parameters: self.parametersFor(obj),
							checksum: jsondata.data.checksum,
						});
					}
					self.propertyContainerFor(obj).data('checksum', jsondata.data.checksum);
				} else {
					// Save value and parameters internally
					var value = self.valueFor(obj);
					switch(element) {
						case 'CATEGORIES':
							// We deal with this in addToGroup()
							break;
						case 'FN':
							var nempty = true;
							if(!self.data.N) {
								self.data.N = [];
							}
							for(var i in self.data.N[0]['value']) {
								if(self.data.N[0]['value'][i] != '') {
									nempty = false;
									break;
								}
							}
							if(nempty) {
								self.N[0]['value'] = [value, '', '', '', ''];
								setTimeout(function() {
									// TODO: Hint to user to check if name is properly formatted
									self.saveProperty({name:'N', value:this.data.N[0].value.join(';')})}
								, 500);
							}
						case 'NICKNAME':
						case 'BDAY':
						case 'ORG':
						case 'TITLE':
							self.data[element][0] = {
								name: element,
								value: value,
								parameters: self.parametersFor(obj),
								checksum: jsondata.data.checksum,
							};
							break;
						default:
							break;
					}
				}
				self.setAsSaving(obj, false);
				return true;
			} else {
				$(document).trigger('status.contact.error', {
					status: 'error',
					message: jsondata.data.message,
				});
				self.setAsSaving(obj, false);
				return false;
			}
		},'json');
	}

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
	}

	/**
	 * Remove any open contact from the DOM and detach it's list
	 * element from the DOM.
	 * @returns The list item jquery object.
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
			$(this).prop('disabled', !enabled);
		});
		$(document).trigger('status.contact.enabled', enabled);
	}

	/**
	 * Delete contact from data store and remove it from the DOM
	 * @param cb Optional callback function which
	 * @returns An object with a variable 'status' of either success
	 * 	or 'error'
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
				var retval = {status: jsondata ? jsondata.status : 'error'};
				if(jsondata) {
					if(jsondata.status === 'success') {
						retval['id'] = jsondata.id;
					} else {
						retval['message'] = jsondata.message;
					}
				} else {
					retval['message'] = t('contacts', 'There was an unknown error when trying to delete this contact');
					retval['id'] = self.id;
				}
				cb(retval);
			}
		});
	}

	Contact.prototype.queryStringFor = function(obj) {
		var q = 'id=' + this.id;
		var ptype = this.propertyTypeFor(obj);
		q += '&name=' + ptype;

		if(this.multi_properties.indexOf(ptype) !== -1) {
			q += '&checksum=' + this.checksumFor(obj);
		}

		if($(obj).hasClass('propertycontainer')) {
			q += '&value=' + encodeURIComponent($(obj).val());
		} else {
			q += '&' + this.propertyContainerFor(obj)
				.find('input.value,select.value,textarea.value,.parameter').serialize();
		}
		return q;
	}

	Contact.prototype.propertyContainerFor = function(obj) {
		return $(obj).hasClass('.propertycontainer')
			? $(obj)
			: $(obj).parents('.propertycontainer').first();
	}

	Contact.prototype.checksumFor = function(obj) {
		return this.propertyContainerFor(obj).data('checksum');
	}

	Contact.prototype.valueFor = function(obj) {
		return this.propertyContainerFor(obj).find('input.value').val();
	}

	Contact.prototype.parametersFor = function(obj) {
		var parameters = [];
		$.each(this.propertyContainerFor(obj).find('select.parameter,input:checkbox:checked.parameter,textarea'), function(i, elem) {
			var $elem = $(elem);
			var paramname = $elem.data('parameter');
			if(!parameters[paramname]) {
				parameters[paramname] = [];
			}
			parameters[paramname].push($elem.val());
		});
		console.log('Contact.parametersFor', parameters);
		return parameters;
	}

	Contact.prototype.propertyTypeFor = function(obj) {
		var ptype = this.propertyContainerFor(obj).data('element');
		return ptype ? ptype.toUpperCase() : null;
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
		if(this.access.owner !== OC.currentUser
				&& !(this.access.permissions & OC.PERMISSION_UPDATE
				|| this.access.permissions & OC.PERMISSION_DELETE)) {
			this.$listelem.find('input:checkbox').prop('disabled', true).css('opacity', '0');
		}
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
		this.$addMenu = this.$fullelem.find('#addproperty');
		this.$addMenu.on('change', function(event) {
			console.log('add', $(this).val());
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
			console.log('edit name', event);
			$('.tipsy').remove();
			if(wrongKey(event)) {
				return;
			}
		});
		var $singleelements = this.$fullelem.find('dd.propertycontainer');
		$singleelements.find('.action').css('opacity', '0');
		$singleelements.on('mouseenter', function() {
			$(this).find('.action').css('opacity', '1');
		}).on('mouseleave', function() {
			$(this).find('.action').css('opacity', '0');
		});
		this.$fullelem.on('click keydown', '.delete', function(event) {
			console.log('delete', event);
			$('.tipsy').remove();
			if(wrongKey(event)) {
				return;
			}
			self.deleteProperty({obj:event.target});
		});
		this.$fullelem.on('change', '.value,.parameter', function(event) {
			console.log('change', event);
			self.saveProperty({obj:event.target});
		});
		this.$fullelem.on('click', '.favorite', function(event) {
			if(typeof self.is_favorite === 'undefined') {
				console.log('Favorite state not set yet.');
				self.is_favorite == false;
			}
			console.log('favorite', event);
			$(this).addClass('wait');
			$(document).trigger('request.setasfavorite', {
				id: self.id,
				state: !self.is_favorite,
			});
		});
		$(document).bind('status.contact.favoritestate', function(e, result) {
			console.log('status.contact.favoritestate', result);
			if(parseInt(result.id) !== parseInt(self.id)) {
				console.log(result.id, 'is not me:', self.id);
				return;
			}
			var $favstar = self.$fullelem.find('.favorite');
			$favstar.removeClass('wait');
			if(result.status === 'success') {
				self.is_favorite = result.state;
				$favstar.toggleClass('inactive', !result.state).toggleClass('active', result.state);
				/*if(result.state === true) {
					$favstar.removeClass('inactive').addClass('active');
				} else {
					$favstar.removeClass('active').addClass('inactive');
				}*/
			} else {
				// TODO:...
			}
		});
		this.$fullelem.find('form').on('submit', function(event) {
			console.log('submit', this, event);
			return false;
		});
		this.$fullelem.find('[data-element="bday"]')
			.find('input').datepicker({
				dateFormat : 'dd-mm-yy'
		});
		this.loadPhoto();
		if(!this.data) {
			// A new contact
			this.setEnabled(true);
			return this.$fullelem;
		}
		// Loop thru all single occurrence values. If not set hide the
		// element, if set disable the add menu entry.
		for(var value in values) {
			if(this.multi_properties.indexOf(value.toUpperCase()) === -1) {
				if(!values[value].length) {
					console.log('hiding', value);
					this.$fullelem.find('[data-element="' + value + '"]').hide();
				} else {
					this.$addMenu.find('option[value="' + value.toUpperCase() + '"]').prop('disabled', true);
				}
			}
		}
		$.each(this.multi_properties, function(idx, name) {
			if(self.data[name]) {
				var $list = self.$fullelem.find('ul.' + name.toLowerCase());
				$list.show();
				for(var p in self.data[name]) {
					if(typeof self.data[name][p] === 'object') {
						var property = self.data[name][p];
						//console.log(name, p, property);
						$property = null;
						switch(name) {
							case 'TEL':
							case 'URL':
							case 'EMAIL':
								$property = self.renderStandardProperty(name.toLowerCase(), property);
								if(self.data[name].length >= 1) {
									$property.find('input:checkbox[value="PREF"]').hide();
								}
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
						if(self.access.owner === OC.currentUser
								|| self.access.permissions & OC.PERMISSION_UPDATE
								|| self.access.permissions & OC.PERMISSION_DELETE) {
							$property.find('select.type[name="parameters[TYPE][]"]')
								.combobox({
									singleclick: true,
									classes: ['propertytype', 'float', 'label'],
								});
							$property.on('mouseenter', function() {
								$(this).find('.listactions').css('opacity', '1');
							}).on('mouseleave', function() {
								$(this).find('.listactions').css('opacity', '0');
							});
						}
						$list.append($property);
					}
				}
			}
		});
		if(this.access.owner !== OC.currentUser
			&& !(this.access.permissions & OC.PERMISSION_UPDATE
				|| this.access.permissions & OC.PERMISSION_DELETE)) {
			this.setEnabled(false);
		} else {
			this.setEnabled(true);
		}
		return this.$fullelem;
	}

	Contact.prototype.isEditable = function() {
		return ((this.access.owner === OC.currentUser)
			|| (this.access.permissions & OC.PERMISSION_UPDATE
				|| this.access.permissions & OC.PERMISSION_DELETE));
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
		var values = property
			? { value: property.value, checksum: property.checksum }
			: { value: '', checksum: 'new' };
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
		var values = property ? {
				value: property.value.clean('').join(', '),
				checksum: property.checksum,
				adr0: property.value[0] || '',
				adr1: property.value[1] || '',
				adr2: property.value[2] || '',
				adr3: property.value[3] || '',
				adr4: property.value[4] || '',
				adr5: property.value[5] || '',
			}
			: {value: '', checksum: 'new', adr0: '', adr1: '', adr2: '', adr3: '', adr4: '', adr5: ''};
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
		var values = property ? {
			value: property.value,
			checksum: property.checksum,
		} : {value: '', checksum: 'new'};
		$elem = this.detailTemplates['impp'].octemplate(values);
		return $elem;
	}

	/**
	 * Render the PHOTO property.
	 */
	Contact.prototype.loadPhoto = function(dontloadhandlers) {
		var self = this;
		var refreshstr = '&refresh='+Math.random();
		this.$photowrapper = this.$fullelem.find('#photowrapper');
		this.$photowrapper.addClass('loading').addClass('wait');
		var $phototools = this.$fullelem.find('#phototools');
		console.log('photowrapper', this.$photowrapper.length);
		delete this.photo;
		$('img.contactphoto').remove()
		this.photo = new Image();
		$(this.photo).load(function () {
			$(this).addClass('contactphoto');
			self.$photowrapper.css('width', $(this).get(0).width + 10);
			self.$photowrapper.removeClass('loading').removeClass('wait');
			$(this).insertAfter($phototools).fadeIn();
		}).error(function () {
			OC.notify({message:t('contacts','Error loading profile picture.')});
		}).attr('src', OC.linkTo('contacts', 'photo.php')+'?id='+self.id+refreshstr);

		if(!dontloadhandlers && this.isEditable()) {
			this.$photowrapper.on('mouseenter', function() {
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
				console.log('TODO: edit photo');
				$(document).trigger('request.edit.contactphoto', {
					id: self.id,
				});
			});
			$phototools.find('.cloud').on('click', function() {
				console.log('select photo from cloud');
				$(document).trigger('request.select.contactphoto.fromcloud', {
					id: self.id,
				});
			});
			$phototools.find('.upload').on('click', function() {
				console.log('select photo from local');
				$(document).trigger('request.select.contactphoto.fromlocal', {
					id: self.id,
				});
			});
			if(this.data.PHOTO) {
				$phototools.find('.delete').show();
				$phototools.find('.edit').show();
			} else {
				$phototools.find('.delete').hide();
				$phototools.find('.edit').hide();
			}
			$(document).bind('status.contact.photoupdated', function(e, result) {
				console.log('Contact - photoupdated')
				self.loadPhoto(true);
				var refreshstr = '&refresh='+Math.random();
				self.getListItemElement().find('td.name')
					.css('background', 'url(' + OC.filePath('', '', 'remote.php')+'/contactthumbnail?id='+self.id+refreshstr + ')');
			});
		}
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

	/**
	 * Returns true/false depending on the contact being in the
	 * specified group.
	 * @param String name The group name (not case-sensitive)
	 * @returns Boolean
	 */
	Contact.prototype.inGroup = function(name) {
		if(!this.data.CATEGORIES) {
			return false;
		}

		categories = this.data.CATEGORIES[0].value;
		for(var i in categories) {
			if(typeof categories[i] === 'string' && (name.toLowerCase() === categories[i].toLowerCase())) {
				return true;
			}
		};
		return false;
	}

	/**
	 * Add this contact to a group
	 * @param String name The group name
	 */
	Contact.prototype.addToGroup = function(name) {
		console.log('addToGroup', name);
		if(!this.data.CATEGORIES) {
			this.data.CATEGORIES = [{value:[name]},];
		} else {
			this.data.CATEGORIES[0].value.push(name);
			console.log('listelem categories', this.getPreferredValue('CATEGORIES', []).clean('').join(' / '));
			if(this.$listelem) {
				this.$listelem.find('td.categories')
					.text(this.getPreferredValue('CATEGORIES', []).clean('').join(' / '));
			}
		}
		this.saveProperty({name:'CATEGORIES', value:this.data.CATEGORIES[0].value.join(',') });
	}

	/**
	 * Remove this contact to a group
	 * @param String name The group name
	 */
	Contact.prototype.removeFromGroup = function(name) {
		console.log('removeFromGroup', name);
		if(!this.data.CATEGORIES) {
			return;
		} else {
			this.data.CATEGORIES[0].value.splice(this.data.CATEGORIES[0].value.indexOf(name), 1);
			if(this.$listelem) {
				this.$listelem.find('td.categories')
					.text(this.getPreferredValue('CATEGORIES', []).clean('').join(' / '));
			}
		}
		this.saveProperty({name:'CATEGORIES', value:this.data.CATEGORIES[0].value.join(',') });
	}

	Contact.prototype.setCurrent = function(on) {
		if(on) {
			this.$listelem.addClass('active');
		} else {
			this.$listelem.removeClass('active');
		}
		$(document).trigger('status.contact.currentlistitem', {
			id: this.id,
			pos: Math.round(this.$listelem.position().top),
			height: Math.round(this.$listelem.height()),
		});
	}

	Contact.prototype.next = function() {
		var $next = this.$listelem.next('tr');
		if($next.length > 0) {
			this.$listelem.removeClass('active');
			$next.addClass('active');
			$(document).trigger('status.contact.currentlistitem', {
				id: parseInt($next.data('id')),
				pos: Math.round($next.position().top),
				height: Math.round($next.height()),
			});
		}
	}

	Contact.prototype.prev = function() {
		var $prev = this.$listelem.prev('tr');
		if($prev.length > 0) {
			this.$listelem.removeClass('active');
			$prev.addClass('active');
			$(document).trigger('status.contact.currentlistitem', {
				id: parseInt($prev.data('id')),
				pos: Math.round($prev.position().top),
				height: Math.round($prev.height()),
			});
		}
	}

	var ContactList = function(contactlist, contactlistitemtemplate, contactfulltemplate, contactdetailtemplates) {
		//console.log('ContactList', contactlist, contactlistitemtemplate, contactfulltemplate, contactdetailtemplates);
		var self = this;
		this.length = 0;
		this.contacts = {};
		this.deletionQueue = [];
		this.$contactList = contactlist;
		this.$contactListItemTemplate = contactlistitemtemplate;
		this.$contactFullTemplate = contactfulltemplate;
		this.contactDetailTemplates = contactdetailtemplates;
		this.$contactList.scrollTop(0);
		this.loadContacts(0);

	}

	/**
	* Show/hide contacts belonging to an addressbook.
	* @param int aid. Addressbook id.
	* @param boolean show. Whether to show or hide.
	* @param boolean hideothers. Used when showing shared addressbook as a group.
	*/
	ContactList.prototype.showFromAddressbook = function(aid, show, hideothers) {
		console.log('ContactList.showFromAddressbook', aid, show);
		aid = parseInt(aid);
		for(var contact in this.contacts) {
			if(this.contacts[contact].access.id === aid) {
				this.contacts[contact].getListItemElement().toggle(show);
			} else if(hideothers) {
				this.contacts[contact].getListItemElement().hide();
			}
		}
	}

	/**
	* Show/hide contacts belonging to shared addressbooks.
	* @param boolean show. Whether to show or hide.
	*/
	ContactList.prototype.showSharedAddressbooks = function(show) {
		console.log('ContactList.showSharedAddressbooks', show);
		for(var contact in this.contacts) {
			if(this.contacts[contact].access.owner !== OC.currentUser) {
				if(show) {
					this.contacts[contact].getListItemElement().show();
				} else {
					this.contacts[contact].getListItemElement().hide();
				}
			}
		}
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
				if(contacts.indexOf(contact) === -1) {
					this.contacts[contact].getListItemElement().hide();
				} else {
					this.contacts[contact].getListItemElement().show();
				}
			}
		}
	}

	ContactList.prototype.contactPos = function(id) {
		if(!id) {
			console.log('id missing');
			return false;
		}
		var $elem = this.contacts[parseInt(id)].getListItemElement();
		var pos = $elem.offset().top - this.$contactList.offset().top + this.$contactList.scrollTop();
		console.log('pos', pos);
		return pos;
	}

	ContactList.prototype.closeContact = function(id) {
		this.contacts[parseInt(id)].close();
	}

	/**
	* Jumps to an element in the contact list
	* @param number the number of the item starting with 0
	*/
	ContactList.prototype.jumpToContact = function(id) {
		var pos = this.contactPos(id);
		console.log('scrollTop', pos);
		this.$contactList.scrollTop(pos);
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
			// This is run almost instantly. It's just to allow us to
			// show the warning. Only shows in Chrome afaik...
			setTimeout(OC.Contacts.Contacts.deleteFilesInQueue, 1);
		}
		return warn;
	}

	ContactList.prototype.delayedDeleteContact = function(id) {
		var self = this;
		this.currentContact = null;
		var listelem = this.contacts[parseInt(id)].detach();
		self.$contactList.show();
		this.deletionQueue.push(parseInt(id));
		console.log('deletionQueue', this.deletionQueue, listelem);
		if(!window.onbeforeunload) {
			window.onbeforeunload = this.warnNotDeleted;
		}
		if(this.$contactList.find('tr:visible').length === 0) {
			$(document).trigger('status.visiblecontacts');
		}
		OC.notify({
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
				OC.notify({message:t('contacts', 'Cancelled deletion of: "') + listelem.find('td.name').text() + '"'});
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
		// Local function to update queue.
		var updateQueue = function(id, remove) {
			if(remove) {
				console.log('Removing', id, 'from deletionQueue');
				OC.Contacts.Contacts.deletionQueue.splice(OC.Contacts.Contacts.deletionQueue.indexOf(parseInt(id)), 1);
			}
			if(OC.Contacts.Contacts.deletionQueue.length == 0) {
				console.log('deletionQueue is empty');
				window.onbeforeunload = null;
			}
		}

		if(OC.Contacts.Contacts.deletionQueue.indexOf(parseInt(id)) == -1 && removeFromQueue) {
			console.log('Already deleted, returning');
			updateQueue(id, removeFromQueue);
			return;
		}

		// Let contact remove itself.
		this.contacts[id].destroy(function(response) {
			console.log('deleteContact', response);
			if(response.status === 'success') {
				delete self.contacts[parseInt(id)];
				updateQueue(id, removeFromQueue);
				self.$contactList.show();
				$(document).trigger('status.contact.deleted', {
					id: id,
				});
				self.length -= 1;
				if(self.length === 0) {
					$(document).trigger('status.nomorecontacts');
				}
			} else {
				OC.notify({message:response.message});
			}
		});
	}

	/**
	* Opens the contact with this id in edit mode
	* @param id the id of the contact
	* @returns A jquery object to be inserted in the DOM.
	*/
	ContactList.prototype.showContact = function(id) {
		this.currentContact = parseInt(id);
		console.log('Contacts.showContact', id, this.contacts[this.currentContact], this.contacts)
		return this.contacts[this.currentContact].renderContact();
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
		return contact;
	}

	/**
	* Add contact
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
		if(this.currentContact) {
			this.contacts[this.currentContact].close();
		}
		return contact.renderContact();
	}

	/**
	 * Get contacts selected in list
	 *
	 * @returns array of integer contact ids.
	 */
	ContactList.prototype.getSelectedContacts = function() {
		var contacts = [];

		$.each(this.$contactList.find('tr > td > input:checkbox:visible:checked'), function(a, b) {
			contacts.push(parseInt($(b).parents('tr').first().data('id')));
		});
		return contacts;
	}

	ContactList.prototype.setCurrent = function(id, deselect_other) {
		self = this;
		if(deselect_other === true) {
			$.each(this.contacts, function(contact) {
				self.contacts[contact].setCurrent(false);
			});
		}
		this.contacts[parseInt(id)].setCurrent(true);
	}

	// Should only be neccesary with progressive loading, but it's damn fast, so... ;)
	ContactList.prototype.doSort = function() {
		var self = this;
		var rows = this.$contactList.find('tr').get();

		rows.sort(function(a, b) {
			return $(a).find('td.name').text().toUpperCase().localeCompare($(b).find('td.name').text().toUpperCase());
		});

		$.each(rows, function(index, row) {
			self.$contactList.append(row);
		});
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
				console.log('ContactList.loadContacts', jsondata.data);
				self.addressbooks = {};
				$.each(jsondata.data.addressbooks, function(i, book) {
					self.addressbooks[parseInt(book.id)] = {
						owner: book.userid,
						permissions: parseInt(book.permissions),
						id: parseInt(book.id),
						displayname: book.displayname,
						description: book.description,
						active: Boolean(parseInt(book.active)),
					};
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
					self.length +=1;
					var $item = self.contacts[parseInt(contact.id)].renderListItem();
					$item.draggable({
						distance: 10,
						revert: 'invalid',
						//containment: '#content',
						opacity: 0.8, helper: 'clone',
						zIndex: 1000,
					});
					self.$contactList.append($item);
					//self.insertContact(item);
				});
				self.doSort();
				$(document).trigger('status.contacts.loaded', {
					status: true,
					numcontacts: jsondata.data.contacts.length
				});
				self.setCurrent(self.$contactList.find('tr:first-child').data('id'), false);
			}
			if(typeof cb === 'function') {
				cb();
			}
		});
	}
	OC.Contacts.ContactList = ContactList;

})( jQuery );
