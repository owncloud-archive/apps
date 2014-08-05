/**
* @package imprint an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
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
 * @file js/imprint_header_right.js
 * @brief Client side activity library
 * @author Christian Reiner
 */
$(document).ready(function(){
	var anchor=$('<a />');
	anchor.attr('href',OC.linkTo('imprint','index.php'));
	anchor.text(t("imprint","Legal notice"));
	anchor.addClass('imprint-anchor').addClass('header-right');

	// Indicates oC<7
	var lessSevenLinkShare = $('#save-button').contents()[0] == undefined;

	//	workaround for chaotic header layout definitions in OC-4.x
	if (  (0==$('#header').height()) // indicates OC<5
		&&('right'!=$('#header .searchbox').css('float'))) // special situations like guest view
		anchor.css('right','250px');

	if (lessSevenLinkShare) {
		$('#header').append(anchor);
	} else {
		$('#header .header-right').prepend(anchor);
		anchor.css('float', 'none');
	}
})
