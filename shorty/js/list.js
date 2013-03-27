/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401 
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
 * @file js/list.js
 * @brief Client side desktop initialization for normal calls of the plugin
 * @description
 * This script takes care of initializing the list of Shortys. Creation of that
 * list requires a fully initialized app, so it takes care that process has
 * finished before initializing the list creation.
 * @author Christian Reiner
 */

$(document).ready(function(){
	// initialize desktop
	$.when(
		// prepare the controls panel
		OC.Shorty.WUI.Controls.init()
	).done(function(){
		// hide or show the controls panel in a persistent manner
		$.when(
			OC.Shorty.Action.Preference.get('controls-panel-visible')
		).done(function(pref){
			if (pref['controls-panel-visible'])
				OC.Shorty.WUI.Controls.show();
			else
				OC.Shorty.WUI.Controls.hide();
		});
		// build and show list of Shortys
		OC.Shorty.WUI.List.build();
	});
}); // document.ready
