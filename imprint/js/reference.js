/**
* @package imprint an ownCloud app
* @author Christian Reiner
* @copyright 2012-2014 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php?content=153220
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * @file js/reference.js
 * @brief Client side activity library
 * @author Christian Reiner
 */
OC.Imprint = {
	Label: t('imprint',"Legal notice"),
	Target: OC.linkTo('imprint','index.php'),
	View: {
		'body-user':     'user',
		'body-settings': 'user',
		'body-guest':    'guest',
		'body-public':   'guest',
		'body-login':    'login'
	}, // View
	Placement: {
		'user': {
			'header-left':  function(anchor){$('#header a.menutoggle').after(anchor);},
			'header-right': function(anchor){$('#header form.searchbox').after(anchor);}
		},
		'guest': {
			'header-left':  function(anchor){$('#header #owncloud').after(anchor);},
			'header-right': function(anchor){$('#header div.header-right').after(anchor);},
			'footer-left':  function(anchor){$('footer p.info').before(anchor);},
			'footer-right': function(anchor){$('footer p.info').before(anchor);}
		},
		'login': {
			'header-left':  function(anchor){$('header div#header').before(anchor);},
			'header-right': function(anchor){$('header div#header').before(anchor);},
			'footer-left':  function(anchor){$('footer p.info').before(anchor);},
			'footer-right': function(anchor){$('footer p.info').before(anchor);}
		}
	},
	injectAnchor: function(view,position){
		if (view && position) {
			// create an anchor element (imprint reference)
			var anchor=$('<a />');
			anchor.attr('href',OC.Imprint.Target);
			anchor.text(OC.Imprint.Label);
			anchor.addClass('imprint-anchor').addClass('imprint-view-'+view).addClass('imprint-position-'+position);
			// inject anchor element into DOM
			if (typeof OC.Imprint.Placement[view][position] === 'function') {
				OC.Imprint.Placement[view][position](anchor);
			}
		};
	} // injectAnchor
}

$(document).ready(function() {
	// extract positioning information from DOM
	var view     = OC.Imprint.View[$('body').attr('id')];
	var position = $('head meta[data-imprint-position-'+view+']').attr('data-imprint-position-'+view);
	// inject a reference anchor (imprint link) into the page
	OC.Imprint.injectAnchor(view,position);
})
