(function( $ ) {
	/**
	* Object Template
	* Inspired by micro templating done by e.g. underscore.js
	*/
	var Template = {
		init: function(vars, options, elem) {
			// Mix in the passed in options with the default options
			this.vars = vars;
			this.options = $.extend({},this.options,options);

			this.elem = elem;
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
			var data = this.elem.attr('type') === 'text/template' ? this.elem.html() : this.elem.get(0).outerHTML;
			try {
				return data.replace(/{([^{}]*)}/g,
					function (a, b) {
						var r = o[b];
						return typeof r === 'string' || typeof r === 'number' ? r : a;
					}
				);
			} catch(e) {
				console.error(e, 'data:', data)
			}
		},
		options: {
			escapeFunction: function(str) {return $('<i></i>').text(str).html();}
		}
	};

	$.fn.octemplate = function(vars, options) {
		var vars = vars ? vars : {};
		if(this.length) {
			var _template = Object.create(Template);
			return _template.init(vars, options, this);
		}
	};

})( jQuery );

