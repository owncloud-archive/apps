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

OC_Util::checkAppEnabled('ocdownloader');
OC_Util::checkLoggedIn();

if(isset($_POST['ocdownloader']) && $_POST['ocdownloader'] == 1) {
	foreach($_POST as $key => $value) {
		$value = trim($value);
		if(strcmp(substr($key, 0, 19), 'ocdownloader_pr_un_') == 0) {
			$pr_id = substr($key, strrpos($key, '_')+1);
			if(strlen($value) != 0) {
				if(is_numeric($pr_id)) {
					$pwd = trim($_POST['ocdownloader_pr_pw_' . $pr_id]);
					if(strlen($pwd) != 0) {
						OC_ocDownloader::updateUserInfo($pr_id, $value, $pwd);
					}
				}
			}else{
				if(is_numeric($pr_id)) {
					OC_ocDownloader::deleteUserInfo($pr_id);
				}
			}
		}
	}
}

$tmpl = new OC_Template('ocdownloader', 'personalsettings.tpl');
$tmpl->assign('pr_list', OC_ocDownloader::getUserProvidersList());
return $tmpl->fetchPage();
