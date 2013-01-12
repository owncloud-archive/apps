OC.Contacts = OC.Contacts || {};
OC.Contacts.Settings = OC.Contacts.Settings || {
	init:function() {
		this.Addressbook.adrsettings = $('.addressbooks-settings').first();
		this.Addressbook.adractions = $('#contacts-settings').find('div.actions');
		//console.log('actions: ' + this.Addressbook.adractions.length);
		OC.Share.loadIcons('addressbook');
	},
	Addressbook:{
		showActions:function(act) {
			this.adractions.children().hide();
			this.adractions.children('.'+act.join(',.')).show();
		},
		doActivate:function(id, tgt) {
			var active = tgt.is(':checked');
			//console.log('doActivate: ', id, active);
			$.post(OC.filePath('contacts', 'ajax', 'addressbook/activate.php'), {id: id, active: Number(active)}, function(jsondata) {
				if (jsondata.status == 'success') {
					$(document).trigger('request.addressbook.activate', {
						id: id,
						activate: active,
					});
				} else {
					//console.log('Error:', jsondata.data.message);
					OC.Contacts.notify(t('contacts', 'Error') + ': ' + jsondata.data.message);
					tgt.checked = !active;
				}
			});
		},
		doDelete:function(id) {
			//console.log('doDelete: ', id);
			var check = confirm('Do you really want to delete this address book?');
			if(check == false){
				return false;
			} else {
				var row = $('.addressbooks-settings tr[data-id="'+id+'"]');
				OC.Contacts.loading(row.find('.name'));
				$.post(OC.filePath('contacts', 'ajax', 'addressbook/delete.php'), { id: id}, function(jsondata) {
					if (jsondata.status == 'success'){
						$('#contacts h3[data-id="'+id+'"],#contacts ul[data-id="'+id+'"]').remove();
						row.remove()
						OC.Contacts.Settings.Addressbook.showActions(['new',]);
						OC.Contacts.update();
					} else {
						OC.dialogs.alert(jsondata.data.message, t('contacts', 'Error'));
					}
				});
			}
		},
		doEdit:function(id) {
			//console.log('doEdit: ', id);
			var owner = this.adrsettings.find('[data-id="'+id+'"]').data('owner');
			var actions = ['description', 'save', 'cancel'];
			if(owner == OC.currentUser || id === 'new') {
				actions.push('active', 'name');
			}
			this.showActions(actions);
			var name = this.adrsettings.find('[data-id="'+id+'"]').find('.name').text();
			var description = this.adrsettings.find('[data-id="'+id+'"]').find('.description').text();
			var active = this.adrsettings.find('[data-id="'+id+'"]').find(':checkbox').is(':checked');
			//console.log('name, desc', name, description);
			this.adractions.find('.active').prop('checked', active);
			this.adractions.find('.name').val(name);
			this.adractions.find('.description').val(description);
			this.adractions.data('id', id);
		},
		doSave:function() {
			var name = this.adractions.find('.name').val();
			var description = this.adractions.find('.description').val();
			var active = this.adractions.find('.active').is(':checked');
			var id = this.adractions.data('id');
			//console.log('doSave:', id, name, description, active);

			if(name.length == 0) {
				OC.dialogs.alert(t('contacts', 'Displayname cannot be empty.'), t('contacts', 'Error'));
				return false;
			}
			var url;
			if (id == 'new'){
				url = OC.filePath('contacts', 'ajax', 'addressbook/add.php');
			}else{
				url = OC.filePath('contacts', 'ajax', 'addressbook/update.php');
			}
			self = this;
			$.post(url, { id: id, name: name, active: Number(active), description: description },
				function(jsondata){
					if(jsondata.status == 'success'){
						self.showActions(['new',]);
						self.adractions.removeData('id');
						active = Boolean(Number(jsondata.data.addressbook.active));
						if(id == 'new') {
							self.adrsettings.find('table')
								.append('<tr class="addressbook" data-id="'+jsondata.data.addressbook.id+'" data-uri="'+jsondata.data.addressbook.uri+'">'
									+ '<td class="active"><input type="checkbox" '+(active ? 'checked="checked"' : '')+' /></td>'
									+ '<td class="name">'+jsondata.data.addressbook.displayname+'</td>'
									+ '<td class="description">'+jsondata.data.addressbook.description+'</td>'
									+ '<td class="action"><a class="svg action globe" title="'+t('contacts', 'Show CardDav link')+'"></a></td>'
									+ '<td class="action"><a class="svg action cloud" title="'+t('contacts', 'Show read-only VCF link')+'"></a></td>'
									+ '<td class="action"><a class="svg action download" title="'+t('contacts', 'Download')+'" '
									+ 'href="'+OC.linkTo('contacts', 'export.php')+'?bookid='+jsondata.data.addressbook.id+'"></a></td>'
									+ '<td class="action"><a class="svg action edit" title="'+t('contacts', 'Edit')+'"></a></td>'
									+ '<td class="action"><a class="svg action delete" title="'+t('contacts', 'Delete')+'"></a></td>'
									+ '</tr>');
						} else {
						var row = self.adrsettings.find('tr[data-id="'+id+'"]');
							row.find('td.active').find('input:checkbox').prop('checked', active);
							row.find('td.name').text(jsondata.data.addressbook.displayname);
							row.find('td.description').text(jsondata.data.addressbook.description);
						}
						OC.Contacts.update();
					} else {
						OC.dialogs.alert(jsondata.data.message, t('contacts', 'Error'));
					}
			});
		},
		showLink:function(id, row, link) {
			//console.log('row:', row.length);
			row.next('tr.link').remove();
			var linkrow = $('<tr class="link"><td colspan="5"><input style="width: 95%;" type="text" value="'+link+'" /></td>'
				+ '<td colspan="3"><button>'+t('contacts', 'Cancel')+'</button></td></tr>').insertAfter(row);
			linkrow.find('input').focus().select();
			linkrow.find('button').click(function() {
				$(this).parents('tr').first().remove();
			});
		},
		showCardDAV:function(id) {
			//console.log('showCardDAV: ', id);
			var row = this.adrsettings.find('tr[data-id="'+id+'"]');
			var owner = row.data('owner');
			var uri = (owner === oc_current_user ) ? row.data('uri') : row.data('uri') + '_shared_by_' + owner;
			this.showLink(id, row, totalurl+'/'+encodeURIComponent(oc_current_user)+'/'+encodeURIComponent(uri));
		},
		showVCF:function(id) {
			//console.log('showVCF: ', id);
			var row = this.adrsettings.find('tr[data-id="'+id+'"]');
			var owner = row.data('owner');
			var uri = (owner === oc_current_user ) ? row.data('uri') : row.data('uri') + '_shared_by_' + owner;
			var link = totalurl+'/'+encodeURIComponent(oc_current_user)+'/'+encodeURIComponent(uri)+'?export';
			//console.log(link);
			this.showLink(id, row, link);
		}
	}
};


$(document).ready(function() {
	OC.Contacts.Settings.init();

	var moreless = $('#contacts-settings').find('.moreless').first();
	moreless.keydown(function(event) {
		if(event.which == 13 || event.which == 32) {
			moreless.click();
		}
	});
	moreless.on('click', function(event) {
		event.preventDefault();
		if(OC.Contacts.Settings.Addressbook.adrsettings.is(':visible')) {
			OC.Contacts.Settings.Addressbook.adrsettings.slideUp();
			moreless.text(t('contacts', 'More...'));
		} else {
			OC.Contacts.Settings.Addressbook.adrsettings.slideDown();
			moreless.text(t('contacts', 'Less...'));
		}
	});

	OC.Contacts.Settings.Addressbook.adrsettings.keydown(function(event) {
		if(event.which == 13 || event.which == 32) {
			OC.Contacts.Settings.Addressbook.adrsettings.click();
		}
	});


	OC.Contacts.Settings.Addressbook.adrsettings.on('click', function(event){
		$('.tipsy').remove();
		var tgt = $(event.target);
		if(tgt.is('a') || tgt.is(':checkbox')) {
			var id = tgt.parents('tr').first().data('id');
			if(!id) {
				return;
			}
			if(tgt.is(':checkbox')) {
				OC.Contacts.Settings.Addressbook.doActivate(id, tgt);
			} else if(tgt.is('a')) {
				if(tgt.hasClass('edit')) {
					OC.Contacts.Settings.Addressbook.doEdit(id);
				} else if(tgt.hasClass('delete')) {
					OC.Contacts.Settings.Addressbook.doDelete(id);
				} else if(tgt.hasClass('globe')) {
					OC.Contacts.Settings.Addressbook.showCardDAV(id);
				} else if(tgt.hasClass('cloud')) {
					OC.Contacts.Settings.Addressbook.showVCF(id);
				}
			}
		} else if(tgt.is('button')) {
			event.preventDefault();
			if(tgt.hasClass('save')) {
				OC.Contacts.Settings.Addressbook.doSave();
			} else if(tgt.hasClass('cancel')) {
				OC.Contacts.Settings.Addressbook.showActions(['new']);
			} else if(tgt.hasClass('new')) {
				OC.Contacts.Settings.Addressbook.doEdit('new');
			}
		}
	});
});
