<?php

/**
* ownCloud - ocDownloader plugin
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

require_once '../../../../lib/base.php';
OC_JSON::checkAppEnabled('ocdownloader');
OC_JSON::checkLoggedIn();

if(!OC_Filesystem::is_dir('/Downloads')) {
	OC_Filesystem::mkdir('/Downloads');
}

$pr = $_POST['pr'];
switch($pr) {
	case 'web':
		$k = OC_ocDownloaderFile::getHttpFile($_POST['url']);
	break;
	default:
		if(preg_match('/^pr_([0-9]{1,4})$/', $pr, $m)) {
			$pr_name = OC_ocDownloader::getProvider($m[1]);
			$user_info = OC_ocDownloader::getUserProviderInfo($m[1]);
			
			$pr_name = strtolower($pr_name['pr_name']);
			if(file_exists(OC::$SERVERROOT . '/apps/ocdownloader/providers/' . $pr_name . '.php')) {
				require_once OC::$SERVERROOT . '/apps/ocdownloader/providers/' . $pr_name . '.php';
			}
		}
}

OC_JSON::encodedPrint($k);