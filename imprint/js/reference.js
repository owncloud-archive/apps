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
		'body-user': 'user',
		'body-settings': 'user',
		'body-guest': 'guest',
		'body-login': 'none'
	}, // View
	injectAnchor: function(){
		var view = OC.Imprint.View[$('body').attr('id')];
		var option = 'position-'+view;
		OC.AppConfig.getValue('imprint',option,'',function(position){
			// create an anchor element (imprint reference)
			var anchor=$('<a />');
			anchor.attr('href',OC.Imprint.Target);
			anchor.text(OC.Imprint.Label);
			anchor.addClass('imprint-anchor').addClass('imprint-view-'+view).addClass('imprint-position-'+position);
			// inject anchor element into DOM
			$('#header form.searchbox').after(anchor);
		});
	} // injectAnchor
}

$(document).ready(function() {
	// inject a reference anchor (imprint link) into the page
	OC.Imprint.injectAnchor();
})
