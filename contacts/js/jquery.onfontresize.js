/**
 *
 * Copyright (c) 2008 Tom Deater (http://www.tomdeater.com)
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * uses an iframe, sized in ems, to detect font size changes then trigger a "fontresize" event
 * heavily based on code by Hedger Wang: http://www.hedgerwow.com/360/dhtml/js-onfontresize.html
 *
 * "fontresize" event is triggered on the document object
 * subscribe to event using: $(document).bind("fontresize", function (event, data) {});
 * "data" contains the current size of 1 em unit (in pixels)
 * 
 */
 
jQuery.onFontResize = (function ($) {
	// initialize
	$(document).ready(function () {
		var $resizeframe = $("<iframe />")
			.attr("id", "frame-onFontResize" + Date.parse(new Date))
			.css({width: "100em", height: "10px", position: "absolute", borderWidth: 0, top: "-5000px", left: "-5000px"})
			.appendTo("body");
			
		if ($.browser.msie) {
			// use IE's native iframe resize event
			$resizeframe.bind("resize", function () {
				$.onFontResize.trigger($resizeframe[0].offsetWidth / 100);
			});
		} else {
			// everyone else uses script inside the iframe to detect resize
			var doc = $resizeframe[0].contentWindow || $resizeframe[0].contentDocument || $resizeframe[0].document;
			doc = doc.document || doc; 
			doc.open();
			doc.write('<div id="em" style="width:100em;height:10px;"></div>');
			doc.write('<scri' + 'pt>window.onload = function(){var em = document.getElementById("em");window.onresize = function(){if(parent.jQuery.onFontResize){parent.jQuery.onFontResize.trigger(em.offsetWidth / 100);}}};</scri' + 'pt>');
			doc.close();
		}
	});
	
	return {
		// public method, so it can be called from within the iframe
		trigger: function (em) {
			$(document).trigger("fontresize", [em]);
		}
	};
}) (jQuery);