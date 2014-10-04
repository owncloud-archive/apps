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
 * @file js/settings.js
 * @brief Handle settings logic
 * @author Christian Reiner
 */

$(document).ready(function(){
	// prepare storing of changed settings
	$('#imprint #imprint-option-position').on('change',function(){
		OC.AppConfig.setValue('imprint','position',$(this).val());
	})
	$('#imprint #imprint-option-anonposition').on('change',function(){
		OC.AppConfig.setValue('imprint','anonposition',$(this).val());
	})
	$('#imprint').find('#imprint-content').on('focusout',function(){
		OC.AppConfig.setValue('imprint','content',$(this).val());
	})

	// initialize options with stored settings
	OC.AppConfig.getValue('imprint','position','',function(data){
		$('#imprint #imprint-option-position option[value="'+data+'"]').attr('selected', 'yes')
	});
	OC.AppConfig.getValue('imprint','anonposition','',function(data){
		$('#imprint #imprint-option-anonposition option[value="'+data+'"]').attr('selected', 'yes')
	});
	OC.AppConfig.getValue('imprint','content','',function(data){
		$('#imprint #imprint-content').html(data);
	});
})
