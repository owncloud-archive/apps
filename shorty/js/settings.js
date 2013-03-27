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
 * @file js/settings.js
 * @brief Client side activity initialization for the system settings dialog
 * @description
 * This script takes care if initializing the client side reactions to events
 * during an opened (system) settings dialog. Shorty follows the paradigm of
 * change-and-store, so all inputs and changes are stored right away, no
 * specific 'save' button has to be used, nor does it exist.
 * @author Christian Reiner
 */

$(document).ready(function(){
	// initialize example that depends on backend-base
	if ($('#shorty #backend-static-base').val().length)
		$('#shorty #backend-static #example').text($('#shorty #backend-static-base').val()+'<shorty id>');
	// modify example upon input of a base
	$('#shorty #backend-static-base').bind('input',function(){
		$('#shorty #backend-static #example').text($('#shorty #backend-static-base').val()+'<shorty id>');
	});
	// backend 'static': offer a clickable example link to verify the correct setup
	$('#shorty #backend-static #example').bind('click',function(event){
		event.preventDefault();
		OC.Shorty.Action.Setting.verify();
	});
	// store setting
	$('#shorty #backend-static-base').focusout(function(){
		// modify example
		$('#shorty #backend-static #example').text($('#shorty #backend-static-base').val()+'<shorty id>');
		// save setting
		OC.Shorty.Action.Setting.set($('#shorty #backend-static-base').serialize());
		return false;
	});
});
