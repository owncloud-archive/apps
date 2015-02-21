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
	$('#imprint #imprint-option-position-user').on('change',function(){
		OC.AppConfig.setValue('imprint','position-user',$(this).val());
	})
	$('#imprint #imprint-option-position-guest').on('change',function(){
		OC.AppConfig.setValue('imprint','position-guest',$(this).val());
	})
	$('#imprint #imprint-option-position-login').on('change',function(){
		OC.AppConfig.setValue('imprint','position-login',$(this).val());
	})
	$('#imprint #imprint-option-standalone').on('change',function(){
		OC.AppConfig.setValue('imprint','standalone',$(this).is(':checked'));
	})
	$('#imprint').find('#imprint-content').on('focusout',function(){
		OC.AppConfig.setValue('imprint','content',$(this).val());
	})

	// initialize options with stored settings
	OC.AppConfig.getValue('imprint','position-user','',function(data){
		$('#imprint #imprint-option-position-user option[value="'+data+'"]').attr('selected', 'yes')
	});
	OC.AppConfig.getValue('imprint','position-guest','',function(data){
		$('#imprint #imprint-option-position-guest option[value="'+data+'"]').attr('selected', 'yes')
	});
	OC.AppConfig.getValue('imprint','position-login','',function(data){
		$('#imprint #imprint-option-position-login option[value="'+data+'"]').attr('selected', 'yes')
	});
	// checkbox 'standalone'
	OC.AppConfig.getValue('imprint','standalone','',function(data){
		if ('true' === data) {
			$('#imprint #imprint-option-standalone').attr('checked', 'checked');
		} else {
			$('#imprint #imprint-option-standalone').removeAttr('checked');
		}
	});
	OC.AppConfig.getValue('imprint','content','',function(data){
		$('#imprint #imprint-content').html(data);
	});
})
