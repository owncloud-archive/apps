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
 * @file js/add.js
 * @brief Client side desktop initialization in case of a call with an url to add
 * @description
 * The Shorty app comes with a javascript bookmarklet ('Shortlet'). This calls
 * the app and specifies a url to be shortened, this makes shortening an open
 * web pages url muhc easier: just click-and-store as Shorty. So the user does
 * not ahve to manually open his ownCloud, navigate the the Shorty app and open
 * the 'New Shorty' dialog. This script is added in case such a request is
 * detected, it takes case that the dialog is opened and filled with the url
 * to be shortened. 
 * @author Christian Reiner
 */

$(window).load(function(){
	// initialize desktop
	var dialog = $('#dialog-add');
	$.when(
		OC.Shorty.WUI.Controls.init()
	).pipe(function(){
		OC.Shorty.WUI.List.build();
	}).done(function(){
		$.when(
			OC.Shorty.WUI.Dialog.toggle(dialog)
		).done(function(){
			// any referrer handed over from php (explicitly in markup) ?
			var target=$('#controls').attr('data-referrer');
			$('#controls').removeAttr('data-referrer');
			dialog.find('#target').val(target);
			dialog.find('#title').focus();
			OC.Shorty.WUI.Meta.collect(dialog);
		})
	});
}); // document.ready
