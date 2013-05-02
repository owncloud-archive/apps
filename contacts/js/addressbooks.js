OC.Contacts = OC.Contacts || {};


(function(window, $, OC) {
	'use strict';

	var AddressBook = function(storage, book, template) {
		this.storage = storage;
		this.book = book;
		this.$template = template;
	}

	AddressBook.prototype.render = function() {
		var self = this;
		this.$li = this.$template.octemplate({
			id: this.book.id,
			displayname: this.book.displayname,
			backend: this.book.backend,
			permissions: this.book.permissions
		});
		this.$li.find('a.action').tipsy({gravity: 'w'});
		this.$li.find('a.action.delete').on('click keypress', function() {
			$('.tipsy').remove();
			console.log('delete', self.getId());
			self.destroy();
		});
		this.$li.find('a.action.globe').on('click keypress', function() {
			var uri = (this.book.owner === oc_current_user ) ? this.book.uri : this.book.uri + '_shared_by_' + this.book.owner;
			var link = OC.linkToRemote('carddav')+'/addressbooks/'+encodeURIComponent(oc_current_user)+'/'+encodeURIComponent(uri);
			var $dropdown = $('<div id="dropdown" class="drop"><input type="text" value="' + link + '" readonly /></div>');
			$dropdown.appendTo($li);
			var $input = $dropdown.find('input');
			$input.focus().get(0).select();
			$input.on('blur', function() {
				$dropdown.hide('blind', function() {
					$dropdown.remove();
				});
			});
		});
		return this.$li;
	};

	AddressBook.prototype.getId = function() {
		return this.book.id;
	};

	AddressBook.prototype.getBackend = function() {
		return this.book.backend;
	};

	AddressBook.prototype.getDisplayName = function() {
		return this.book.displayname;
	};

	AddressBook.prototype.getPermissions = function() {
		return this.book.permissions;
	};

	/**
	 * Delete address book from data store and remove it from the DOM
	 * @param cb Optional callback function which
	 * @return An object with a boolean variable 'error'.
	 */
	AddressBook.prototype.destroy = function(cb) {
		var self = this;
		$.when(this.storage.deleteAddressBook('local', self.getId()))
			.then(function(response) {
			if(!response.error) {
				self.$li.remove();
				$(document).trigger('status.addressbook.removed', {
					addressbook: self
				});
			} else {
				$(document).trigger('status.contact.error', {
					message: response.message
				});
			}
		});
	}

	/**
	 * Controls access to address books
	 */
	var AddressBookList = function(
			storage,
			bookTemplate,
			bookItemTemplate
  		) {
		this.storage = storage;
		this.$bookTemplate = bookTemplate;
		this.$bookList = this.$bookTemplate.find('.addressbooklist');
		this.$bookItemTemplate = bookItemTemplate;
		this.$importFileInput = this.$bookTemplate.find('#import_upload_start');
		this.$importIntoSelect = this.$bookTemplate.find('#import_into');
		this.$importProgress = this.$bookTemplate.find('#import-status-progress');
		this.$importStatusText = this.$bookTemplate.find('#import-status-text');
		this.addressBooks = [];

		var $addInput = this.$bookTemplate.find('#add-address-book');
		var self = this;
		$addInput.addnew({
			ok: function(event, name) {
				console.log('add-address-book ok', name);
				$addInput.addClass('loading');
				self.add(name, function(response) {
					console.log('response', response);
					if(response.error) {
						$(document).trigger('status.contact.error', {
							message: response.message
						});
					} else {
						$(this).addnew('close');
					}
					$addInput.removeClass('loading');
				});
			}
		});
		$(document).bind('status.addressbook.removed', function(e, data) {
			var addressBook = data.addressbook;
			self.addressBooks.splice(self.addressBooks.indexOf(addressBook), 1);
			self.buildImportSelect();
		});
		this.$importIntoSelect.on('change', function() {
			// Disable file input if no address book selected
			var value = $(this).val();
			self.$importFileInput.prop('disabled', value === '-1' );
			if(value !== '-1') {
				var url = OC.Router.generate(
					'contacts_import_upload',
					{addressbookid:value, backend: $(this).find('option:selected').data('backend')}
				);
				self.$importFileInput.fileupload('option', 'url', url);
				//self.$importFileInput.attr('data-url', url);
			}
		});
		this.$importFileInput.fileupload({
			dataType: 'json',
			start: function(e, data) {
				self.$importProgress.progressbar({value:false});
				$('.tipsy').remove();
				$('.import-upload').hide();
				$('.import-status').show();
				self.$importProgress.fadeIn();
				self.$importStatusText.text(t('contacts', 'Uploading...'));
			},
			done: function (e, data) {
				console.log('Upload done:', data.result);
				self.doImport(data.result);
			},
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				self.$importProgress.progressbar('value', progress);
			},
			fail: function(e, data) {
				console.log('fail');
				OC.notify({message:data.errorThrown + ': ' + data.textStatus});
				numfiles = uploadedfiles = importedfiles = retries = failed = succeded = aid = 0;
				$('.import-upload').show();
				$('.import-status').hide();
			}
		});
	};

	AddressBookList.prototype.count = function() {
		return this.addressBooks.length;
	}

	AddressBookList.prototype.doImport = function(response) {
		var done = false;
		var interval = null;
		var self = this;
		var closeImport = function() {
			self.$importProgress.fadeOut();
			setTimeout(function() {
				$('.import-upload').show();
				$('.import-status').hide();
				self.importCount = null;
				self.$importProgress.progressbar('destroy');
			}, 5000);
		};
		if(response.status === 'success') {
			this.importCount = response.data.count;
			this.$importProgress.progressbar('value', '0');
			this.$importProgress.progressbar('option', 'max', this.importCount);
			var data = response.data;
			var getStatus = function(backend, addressbookid, progresskey, interval, done) {
				if(done) {
					clearInterval(interval);
					closeImport();
					return;
				}
				$.when(
					self.storage.importStatus(
						backend, addressbookid,
						{progresskey:progresskey}
					))
					.then(function(response) {
					if(!response.error) {
						self.$importProgress.progressbar('value', response.data.progress);
						self.$importStatusText.text(t('contacts', 'Imported {count} of {total} contacts',
													  {count:response.data.progress, total: self.importCount}));
					} else {
						console.warn('Error', response.message);
						self.$importStatusText.text(response.message);
					}
				});
			};
			$.when(
				self.storage.startImport(
					data.backend, data.addressbookid,
					{filename:data.filename, progresskey:data.progresskey}
  				))
				.then(function(response) {
				if(!response.error) {
					done = true;
					console.log('Import done');
					self.$importStatusText.text(t('contacts', 'Imported {imported} contacts. {failed} failed.',
													  {imported:response.data.imported, failed: response.data.failed}));
				} else {
					done = true;
					self.$importStatusText.text(response.message);
					$(document).trigger('status.contact.error', {
						message: response.message
					});
				}
			});
			interval = setInterval(function() {
				getStatus(data.backend, data.addressbookid, data.progresskey, interval, done);
			}, 1500);
		} else {
			done = true;
			self.$importStatusText.text(response.data.message);
			closeImport();
			$(document).trigger('status.contact.error', {
				message: response.data.message
			});
		}
	}

	/**
	 * Rebuild the select to choose which address book to import into.
	 */
	AddressBookList.prototype.buildImportSelect = function() {
		var self = this;
		this.$importIntoSelect.find('option:not([value="-1"])').remove();
		var addressBooks = this.selectByPermission(OC.PERMISSION_UPDATE);
		$.each(addressBooks, function(idx, book) {
			var $opt = $('<option />');
			$opt.val(book.getId()).text(book.getDisplayName()).data('backend', book.getBackend());
			self.$importIntoSelect.append($opt);
		});
		self.$importFileInput.prop('disabled', true);
	}

	/**
	 * Create an AddressBook object, save it in internal list and append it's rendered result to the list
	 *
	 * @param object addressBook
	 * @param bool rebuild If true rebuild the address book select for import.
	 * @return AddressBook
	 */
	AddressBookList.prototype.insertAddressBook = function(addressBook, rebuild) {
		var book = new AddressBook(this.storage, addressBook, this.$bookItemTemplate);
		var result = book.render();
		this.$bookList.append(result);
		this.addressBooks.push(book);
		if(rebuild) {
			this.buildImportSelect();
		}
		return book;
	};

	/**
	 * Get an array of address books with at least the required permission.
	 *
	 * @param int permission
	 * @param bool noClone If true the original objects will be returned and can be manipulated.
	 */
	AddressBookList.prototype.selectByPermission = function(permission, noClone) {
		var books = [];
		var self = this;
		$.each(this.addressBooks, function(idx, book) {
			if(book.getPermissions() & permission) {
				// Clone the address book not to mess with with original
				books.push(noClone ? book : $.extend(true, {}, book));
			}
		});
		return books;
	};

	/**
	 * Add a new address book.
	 *
	 * @param string name
	 * @param function cb
	 */
	AddressBookList.prototype.add = function(name, cb) {
		console.log('AddressBookList.add', name, typeof cb);
		// Check for wrong, duplicate or empty name
		if(typeof name !== 'string') {
			throw new TypeError('BadArgument: AddressBookList.add() only takes String arguments.');
		}
		if(name.trim() === '') {
			throw new Error('BadArgument: Cannot add an address book with an empty name.');
		}
		var error = '';
		$.each(this.addressBooks, function(idx, book) {
			if(book.getDisplayName() == name) {
				console.log('Dupe');
				error = t('contacts', 'An address book called {name} already exists', {name:name});
				cb({error:true, message:error});
				return false; // break loop
			}
		});
		if(error.length) {
			console.warn('Error:', error);
			return;
		}
		var self = this;
		$.when(this.storage.addAddressBook('local',
		{displayname: name, description: ''})).then(function(response) {
			if(response.error) {
				error = response.message;
				cb({error:true, message:error});
				return;
			} else {
				var book = self.insertAddressBook(response.data, true);
				if(typeof cb === 'function') {
					cb({error:false, addressbook: book});
					return;
				}
			}
		})
		.fail(function(jqxhr, textStatus, error) {
			$(this).removeClass('loading');
			var err = textStatus + ', ' + error;
			console.log( "Request Failed: " + err);
			error = t('contacts', 'Failed adding address book: {error}', {error:err});
			cb({error:true, message:error});
			return;
		});
	};

	/**
	* Load address books
	*/
	AddressBookList.prototype.loadAddressBooks = function(cb) {
		var self = this;
		$.when(this.storage.getAddressBooksForUser()).then(function(response) {
			if(!response.error) {
				var num = response.data.addressbooks.length;
				$.each(response.data.addressbooks, function(idx, addressBook) {
					var book = self.insertAddressBook(addressBook);
				});
				self.buildImportSelect();
				if(typeof OC.Share !== 'undefined') {
					OC.Share.loadIcons('addressbook');
				} else {
					self.$bookList.find('a.action.share').css('display', 'none');
				}
				cb({error:false, addressbooks: self.addressBooks});
			} else {
				cb(response);
				$(document).trigger('status.contact.error', {
					message: response.message
				});
				return false;
			}
		})
		.fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ', ' + error;
			console.warn( "Request Failed: " + err);
			$(document).trigger('status.contact.error', {
				message: t('contacts', 'Failed loading address books: {error}', {error:err})
			});
		});
	};

	OC.Contacts.AddressBookList = AddressBookList;

})(window, jQuery, OC);
