OC.notify = {
	//TODO don't hardcode this!!
    refreshInterval: 30,
    autoRefresh: true,
	dom: {
		icon: $('<a id="notify-icon" class="header-right header-action" href="#" title="' + t('notify', 'Notifications') + '"><img class="svg" alt="' + t('notify', 'Notifications') + '" src="' + OC.imagePath('notify', 'headerIcon.svg') + '" /></a>'),
		counter: $('<span id="notify-counter" data-count="0">0</span>'),
		listContainer: $('<div id="notify-list"><div id="notify-loading"></div><div id="notify-headline"><span id="notify-title">' + t('notify', 'Notifications') + '</span><div class="actionicons"><a href="#notify" id="notify-config" title="' + t('notify', 'Preferences') + '">' + t('notify', 'Preferences') + '</a><span id="notify-readall" title="' + t('notify', 'Mark all as read') + '">' + t('notify', 'Mark all as read') + '</span><span id="notify-deleteread" title="' + t('notify', 'Delete all read notifications') + '">' + t('notify', 'Delete all read notifications') + '</span></div></div>'),
		list: $('<ul></ul>'),
		notificationTemplate: $('<li class="notification"><a class="content" href="#"></a><div class="actionicons"><span class="deleteicon" title="' + t('notify', 'Delete this notification') + '">delete</span></div></li>'),
		fitContainerSize: function() {
			if(OC.notify.dom.listContainer.is(':hidden')) {
				return;
			}
			if(window.innerHeight - OC.notify.dom.listContainer.get(0).offsetTop - OC.notify.dom.listContainer.removeClass('full-height').height() < 16) {
				OC.notify.dom.listContainer.addClass('full-height');
			}
		}
	},
	notifications: [],
	addNotification: function(notification) {
		OC.notify.notifications[parseInt(notification.id)] = notification;
		var el = OC.notify.dom.notificationTemplate.clone().attr({
			'data-id': parseInt(notification.id),
			'title': notification.moment,
			'data-read': notification.read
		}).addClass(notification.app + '_' + notification.class).appendTo(OC.notify.dom.list);
		var content = el.find('a.content').attr('href', OC.linkTo('notify', 'go.php') + '?id=' + notification.id).html(notification.content).click(function(e) {
			//make the href actually work:
			e.stopPropagation();
		});
		if(typeof(notification.img) != 'undefined') {
			content.prepend('<img class="notify-img" src="' + notification.img + '" />');
		}
		for(var param in notification.params) {
			if(param == "class") {
				el.addClass(notification.params[param]);
			} else {
				el.attr('data-' + param, notification.params[param]);
			}
		}
		OC.notify.dom.fitContainerSize();
	},
    timeoutId: null,
	loaded: false,
	updated: false,
    setCount: function(count) {
		if(count < 0) {
			count = 0;
		}
		OC.notify.dom.counter.attr("data-count", count).text(count);
		OC.notify.setDocTitle();
	},
	changeCount: function(diff) {
		var count = parseInt(OC.notify.dom.counter.attr("data-count"));
		OC.notify.setCount(count + diff);
	},
	originalDocTitle: document.title,
	setDocTitle: function() {
		if(!document.title.match(/^\([0-9]+\) /)) {
			OC.notify.originalDocTitle = document.title;
		}
		var count = parseInt(OC.notify.dom.counter.attr("data-count"));
		if(count > 0) {
			document.title = "(" + count + ") " + OC.notify.originalDocTitle;
		} else {
			document.title = OC.notify.originalDocTitle;
		}
	},
	startRefresh: function(msec) {
		OC.notify.stopRefresh();
		if(typeof(msec) == 'undefined') {
			msec = parseInt(OC.notify.refreshInterval) * 1000;
		}
		OC.notify.timeoutId = window.setTimeout(OC.notify.refresh, msec, msec);
	},
	refresh: function(msec) {
		OC.notify.getCount().success(function(data) {
			OC.notify.timeoutId = window.setTimeout(OC.notify.refresh, msec, msec);
		});
	},
	stopRefresh: function() {
		if(typeof(OC.notify.timeoutId) == 'number') {
			window.clearTimeout(OC.notify.timeoutId);
			OC.notify.timeoutId = null;
		}
	},
	toggleRefresh: function(sw) {
		if(typeof(sw) != 'boolean') {
			return OC.notify.toggleRefresh(!OC.notify.autoRefresh);
		} else {
			OC.notify.autoRefresh = sw;
			OC.notify.dom.listContainer.toggleClass('autorefresh', sw);
			if(sw) {
				OC.notify.startRefresh();
			} else {
				OC.notify.stopRefresh();
			}
			return $.post(
				OC.filePath('notify', 'ajax', 'setAutoRefresh.php'),
				{flag: sw ? 1 : 0}
			);
		}
	},
	markRead: function(id, read) {
		console.log("markRead", id, read);
		var notify = $('.notification[data-id="' + id + '"]');
		if(typeof(read) == "undefined") {
			read = (notify.attr('data-read') == '0');
		}
		return $.post(
			OC.filePath('notify', 'ajax', 'markRead.php'),
			{id: id, read: read ? 1 : 0},
			function(data) {
				if(data.status == "success") {
					notify.attr('data-read', read ? 1 : 0);
					OC.notify.setCount(data.unread);
				}
			}
		);
	},
	markAllRead: function() {
		return $.post(
			OC.filePath('notify', 'ajax', 'markAllRead.php'),
			null,
			function(data) {
				if(data.status == 'success') {
					$('.notification').attr('data-read', 1);
					OC.notify.setCount(0);
					OC.notify.dom.listContainer.slideUp();
				}
			}
		);
	},
	delete: function(id) {
		console.log("delete", id);
		var notify = $('.notification[data-id="' + id + '"]');
		return $.post(
			OC.filePath('notify', 'ajax', 'delete.php'),
			{id: id},
			function(data) {
				if(data.status == "success" && parseInt(data.num)) {
					if(notify.attr('data-read') == "0") {
						OC.notify.changeCount(-1);
					}
					notify.fadeOut('slow', function() { $(this).remove(); OC.notify.dom.fitContainerSize(); });
					delete OC.notify.notifications[parseInt(id)];
				}
			}
		);
	},
	deleteRead: function() {
		return $.post(
			OC.filePath('notify', 'ajax', 'deleteRead.php'),
			{read: true},
			function(data) {
				if(data.status == "success") {
					$('.notification[data-read="1"]').fadeOut('slow', function() {
						$(this).remove();
					}).each(function(i, e) {
						delete OC.notify.notifications[$(e).attr('data-id')];
					});
					if(OC.notify.notifications.length == 0) {
						OC.notify.dom.listContainer.slideUp();
					} else {
						OC.notify.dom.fitContainerSize();
					}
				}
			}
		);
	},
	getCount: function() {
		var current = parseInt(OC.notify.dom.counter.attr("data-count"));
		return $.post(
			OC.filePath('notify','ajax','getCount.php'),
			null,
			function(data) {
				var count = parseInt(data);
				if(count != current) {
					OC.notify.setCount(parseInt(data));
					OC.notify.updated = true;
					if(!OC.notify.dom.listContainer.is(':hidden')) {
						OC.notify.loadNotifications();
					}
				}
			}
		);
	},
	getNotifications: function() {
		return $.post(
			OC.filePath('notify','ajax','getNotifications.php'),
			null,
			function(data) {
				OC.notify.notifications = new Array();
				OC.notify.dom.list.empty();
				$(data).each(function(i, n) {
					OC.notify.addNotification(n);
				});
				OC.notify.loaded = true;
				OC.notify.updated = false;
				//TODO: trigger custom events!!
			}
		);
	},
	loadNotifications: function() {
		$('#notify-loading').fadeIn(function() {
			OC.notify.getNotifications().complete(function() {
				$('#notify-loading').fadeOut();
			});
		});
	}
};

$(document).ready(function() {
	OC.notify.dom.icon.append(OC.notify.dom.counter).click(function(event) {
		if(!OC.notify.loaded || OC.notify.updated) {
			OC.notify.loadNotifications();
		}
		OC.notify.dom.listContainer.slideToggle('slow', OC.notify.dom.fitContainerSize);
		return false;
	}).attr('title', t('notify', 'Notifications'))
		.children('img').attr('alt', t('notify', 'Notifications')).attr('src', OC.imagePath('notify', 'headerIcon.svg'));
    OC.notify.dom.listContainer.append(OC.notify.dom.list).click(false).on('click', '.readicon', function(e) {
		OC.notify.markRead($(this).parentsUntil('.notification').parent().attr('data-id'), $(this).hasClass('unread'));
		return false;
	}).on('click', '.deleteicon', function(e) {
		OC.notify.delete($(this).parentsUntil('.notification').parent().attr('data-id'));
		return false;
	});
    $(window).click(function(e) {
        OC.notify.dom.listContainer.slideUp();
    }).resize(OC.notify.dom.fitContainerSize);
    //TODO: do it right and tipsy
    //OC.notify.dom.listContainer.find('.actionicons span').tipsy();
    OC.notify.dom.listContainer.find('#notify-config').click(function(e) { this.href = OC.Router.generate('settings_personal') + '#notify';e.stopPropagation(); });
    OC.notify.dom.listContainer.find('#notify-refresh').click(OC.notify.loadNotifications);
    OC.notify.dom.listContainer.find('#notify-readall').click(OC.notify.markAllRead);
    OC.notify.dom.listContainer.find('#notify-deleteread').click(OC.notify.deleteRead);
    OC.notify.dom.listContainer.find('.notify-autorefresh').click(OC.notify.toggleRefresh);
    OC.notify.dom.icon.appendTo('body:not(#body-login) #header').after(OC.notify.dom.listContainer);
    OC.notify.setDocTitle();
    OC.notify.getCount();
    $.post(OC.filePath('notify', 'ajax', 'getAutoRefresh.php'), null, function(response) {
		if(response) {
			OC.notify.startRefresh();
			OC.notify.autoRefresh = true;
		} else {
			OC.notify.stopRefresh();
			OC.notify.autoRefresh = false;
		}
		OC.notify.dom.listContainer.toggleClass('autorefresh', OC.notify.autoRefresh);
	});
});
