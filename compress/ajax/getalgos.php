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

require_once '../../../lib/base.php';

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('compress');

require_once '../config/config.php';

$k = Array();
if(in_array('zip', get_loaded_extensions())) {
	$k[] = '<option value="zip">Zip</option>';
}
if(file_exists($_CompressConf['tar_bin_path']) && file_exists($_CompressConf['gzip_bin_path'])) {
	$k[] = '<option value="gzip">Gzip</option>';
}

OC_JSON::encodedPrint($k);
