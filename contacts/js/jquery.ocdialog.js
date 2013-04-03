(function($) {
	$.widget('oc.ocdialog', {
		options: {
			width: 'auto',
			height: 'auto',
			closeButton: true,
			closeOnEscape: true
		},
		_create: function() {
			console.log('ocdialog._create');
			var self = this;

			this.originalCss = {
				display: this.element[0].style.display,
				width: this.element[0].style.width,
				height: this.element[0].style.height,
			};

			this.$dialog = $('<div class="oc-dialog" />')
				.attr({
					// Setting tabIndex makes the div focusable
					tabIndex: -1,
					role: 'dialog'
				})
				.insertBefore(this.element);
			this.$dialog.append(this.element.detach());
			this.element.addClass('oc-dialog-content').appendTo(this.$dialog);

			this.$dialog.css({
				display: 'inline-block',
				position: 'fixed'
			});

			$(document).on('keydown keyup', function(event) {
				if(event.target !== self.$dialog.get(0) && self.$dialog.find($(event.target)).length === 0) {
					return;
				}
				// Escape
				if(event.keyCode === 27 && self.options.closeOnEscape) {
					self.close();
					return false;
				}
				// Enter
				if(event.keyCode === 13) {
					event.stopImmediatePropagation();
					if(event.type === 'keyup') {
						event.preventDefault();
						return false;
					}
					// If no button is selected we trigger the primary
					if(self.$buttonrow && self.$buttonrow.find($(event.target)).length === 0) {
						var $button = self.$buttonrow.find('button.primary');
						if($button) {
							$button.trigger('click');
						}
					} else if(self.$buttonrow) {
						$(event.target).trigger('click');
					}
					return false;
				}
			});
			$(window).resize(function() {
				self.parent = self.$dialog.parent().length > 0 ? self.$dialog.parent() : $('body');
				var pos = self.parent.position();
				self.$dialog.css({
					left: pos.left + (self.parent.width() - self.$dialog.outerWidth())/2,
					top: pos.top + (self.parent.height() - self.$dialog.outerHeight())/2
				});
			});

			this._setOptions(this.options);
			$(window).trigger('resize');
		},
		_init: function() {
			console.log('ocdialog._init');
			this.$dialog.focus();
		},
		_setOption: function(key, value) {
			console.log('_setOption', key, value);
			var self = this;
			switch(key) {
				case 'title':
					var $title = $('<h3 class="oc-dialog-title">' + this.options.title
						+ '</h3><hr class="oc-dialog-separator" />');
					if(this.$title) {
						this.$title.replaceWith($title);
					} else {
						this.$title = $title.prependTo(this.$dialog);
					}
					this._setSizes();
					break;
				case 'buttons':
					var $buttonrow = $('<div class="oc-dialog-buttonrow" />');
					if(this.$buttonrow) {
						this.$buttonrow.replaceWith($buttonrow);
					} else {
						this.$buttonrow = $buttonrow.appendTo(this.$dialog);
					}
					$.each(value, function(idx, val) {
						var $button = $('<button>' + val.text + '</button>');
						if(val.defaultButton) {
							$button.addClass('primary');
							self.$defaultButton = $button;
						}
						self.$buttonrow.append($button);
						$button.click(function() {
							val.click.apply(self.element[0], arguments);
						});
					});
					this.$buttonrow.find('button')
						.on('focus', function(event) {
							self.$buttonrow.find('button').removeClass('primary');
							$(this).addClass('primary');
						});
					this._setSizes();
					break;
				case 'closeButton':
					console.log('closeButton', value);
					if(value) {
						var $closeButton = $('<a class="oc-dialog-close svg"></a>');
						console.log('closeButton', $closeButton);
						this.$dialog.prepend($closeButton);
						$closeButton.on('click', function() {
							self.close();
						});
					}
					break;
				case 'width':
					this.$dialog.css('width', value);
					break;
				case 'height':
					this.$dialog.css('height', value);
					break;
				case 'close':
					this.closeCB = value;
					break;
			}
			//this._super(key, value);
			$.Widget.prototype._setOption.apply(this, arguments );
		},
		_setOptions: function(options) {
			console.log('_setOptions', options);
			//this._super(options);
			$.Widget.prototype._setOptions.apply(this, arguments);
		},
		_setSizes: function() {
			var content_height = this.$dialog.height();
			if(this.$title) {
				content_height -= this.$title.outerHeight(true);
			}
			if(this.$buttonrow) {
				content_height -= this.$buttonrow.outerHeight(true);
			}
			this.element.css({
				height: content_height + 'px',
				width: this.$dialog.innerWidth() + 'px'
			});
		},
		close: function() {
			console.log('close');
			var self = this;
			// Ugly hack to catch remaining keyup events.
			setTimeout(function() {
				self._trigger('close', self);
				self.$dialog.hide();
			}, 200);
		},
		destroy: function() {
			console.log('destroy');
			if(this.$title) {
				this.$title.remove()
			}
			if(this.$buttonrow) {
				this.$buttonrow.remove()
			}
			this.element.removeClass('oc-dialog-content')
					.css(this.originalCss).detach().insertBefore(this.$dialog);
			this.$dialog.remove();
		}
	});
}(jQuery));
