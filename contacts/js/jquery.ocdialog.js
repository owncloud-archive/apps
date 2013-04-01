(function($) {
	$.widget('oc.ocdialog', {
		options: {
			width: 'auto',
			height: 'auto'
		},
		_create: function() {
			console.log('ocdialog._init');
			var self = this;

			console.log('test name', this);

			this.$element = $('<div class="oc-dialog" />').insertBefore(this.element);
			this.$element.append(this.element.detach());
			this.$content = this.element.wrap('<div class="oc-dialog-content" />').parent();

			this.$element.css('display', 'inline-block');

			$(window).resize(function() {
				self.parent = self.$element.parent().length > 0 ? self.$element.parent() : $('body');
				console.log('parent', self.parent.length);
				var pos = self.parent.position();
				self.$element.css({
				position:'absolute',
					left: pos.left + (self.parent.width() - self.$element.outerWidth())/2,
					top: pos.top + (self.parent.height() - self.$element.outerHeight())/2
				});
			});

			this._setOptions(this.options);
			$(window).trigger('resize');
		},
		_init: function() {
			console.log('ocdialog._init');
		},
		_setOption: function(key, value) {
			console.log('_setOption', key, value);
			switch(key) {
				case 'title':
					var $title = $('<h3 class="oc-dialog-title">' + this.options.title
						+ '</h3><hr class="oc-dialog-separator" />');
					if(this.$title) {
						this.$title.replaceWith($title);
					} else {
						this.$title = $title.prependTo(this.$element);
					}
					this._setSizes();
					break;
				case 'buttons':
					var $buttonrow = $('<div class="oc-dialog-buttonrow" />');
					if(this.$buttonrow) {
						this.$buttonrow.replaceWith($buttonrow);
					} else {
						this.$buttonrow = $buttonrow.appendTo(this.$element);
					}
					var self = this;
					$.each(value, function(idx, val) {
						console.log('button text', val.text);
						var $button = $('<button>' + val.text + '</button>');
						self.$buttonrow.append($button);
						$button.click(function() {
							val.click.apply(self.element[0], arguments);
							//val.click(new $.Event(self));
						});
					});
					this._setSizes();
					break;
				case 'width':
					this.$element.css('width', value);
					break;
				case 'height':
					this.$element.css('height', value);
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
			var content_height = this.$element.height();
			if(this.$title) {
				content_height -= this.$title.outerHeight(true);
			}
			if(this.$buttonrow) {
				content_height -= this.$buttonrow.outerHeight(true);
				console.log('buttonrow', this.$buttonrow.outerHeight(true));
			}
			this.$content.css({
				height: content_height + 'px',
				width: this.$element.innerWidth() + 'px'
			});
		},
		close: function() {
			console.log('close 1');
			this._trigger('close', this);
			this.$element.hide();
		},
		destroy: function() {
			console.log('destroy');
			if(this.$title) {
				this.$title.remove()
			}
			if(this.$buttonrow) {
				this.$buttonrow.remove()
			}
			this.element.detach().insertBefore(this.$element);
			this.$element.remove();
		}
	});
}(jQuery));
