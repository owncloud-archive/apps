(function( $ ) {
	// Support older browsers. From http://www.yelotofu.com/2008/08/jquery-outerhtml/
	jQuery.fn.outerHTML = function(s) {
		return s
			? this.before(s).remove()
			: jQuery('<p>').append(this.eq(0).clone()).html();
	};
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
		}
	};

	$.fn.octemplate = function(options) {
		if ( this.length ) {
			var _template = Object.create(Template);
			return _template.init(options, this);
		}
	};

})( jQuery );

