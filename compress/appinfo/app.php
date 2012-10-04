<?php

/**
* ownCloud - Compress plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

$app_id = 'compress';

OC_Util::checkAppEnabled($app_id);

OC_App::register(Array(
	'order' => 70,
	'id' => $app_id,
	'name' => ucfirst($app_id)
));

OC_Util::addScript($app_id, 'actlink.min');
OC_Util::addScript('3rdparty','chosen/chosen.jquery.min');
OC_Util::addStyle('3rdparty','chosen/chosen');

if(!OC_App::isEnabled('files_sharing')) {
	OC_Util::addStyle($app_id,'styles');
}
