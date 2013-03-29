(function( $ ) {
	// Support older browsers. From http://www.yelotofu.com/2008/08/jquery-outerhtml/
	$.fn.outerHTML = function(s) {
		return s
			? this.before(s).remove()
			: $('<p>').append(this.eq(0).clone()).html();
	};
	/**
	* Object Template
	* Inspired by micro templating done by e.g. underscore.js
	*/
	var Template = {
		init: function(vars, options, elem) {
			// Mix in the passed in options with the default options
			this.vars = vars;
			this.options = $.extend({},this.options,options);

			// Save the element reference, both as a jQuery
			// reference and a normal reference
			this.elem  = elem;
			this.$elem = $(elem);
			var self = this;

			if(typeof this.options.escapeFunction === 'function') {
			$.each(this.vars, function(key, val) {
				if(typeof val === 'string') {
					self.vars[key] = self.options.escapeFunction(val);
				}
			});
			}

			var _html = this._build(this.vars);
			return $(_html);
		},
		// From stackoverflow.com/questions/1408289/best-way-to-do-variable-interpolation-in-javascript
		_build: function(o){
			var data = this.$elem.html();
				//this.$elem.attr('type') === 'text/template'
				//? this.$elem.html() : this.$elem.outerHTML();
			return data.replace(/{([^{}]*)}/g,
				function (a, b) {
					var r = o[b];
					return typeof r === 'string' || typeof r === 'number' ? r : a;
				}
			);
		},
		options: {
			escapeFunction: escapeHTML
		}
	};

	$.fn.octemplate = function(vars, options) {
		if ( this.length ) {
			var _template = Object.create(Template);
			return _template.init(vars, options, this);
		}
	};

})( jQuery );

