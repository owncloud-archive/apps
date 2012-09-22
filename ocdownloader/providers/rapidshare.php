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
* RapidShare provider file
* 
*/

$url = $_POST['url'];
$k = Array();
if(preg_match('/^(http|https):\/\/rapidshare.com\/files\/([0-9]+)\/(.+)$/', $url, $m)) {
	$pr_url = 'https://api.rapidshare.com/cgi-bin/rsapi.cgi?sub=download';
	if(isset($user_info['us_username']) && isset($user_info['us_password'])) {
		$pr_url .= '&login=' . $user_info['us_username'] . '&password=' . $user_info['us_password']; 
	}
	$pr_url .= '&fileid=' . $m[2] . '&filename=' . $m[3];
	
	$curl = OC_ocDownloaderFile::execURL($pr_url);
	if(preg_match('/^(ERROR.*)(\n) {0,1}.*$/', $curl, $ma)) {
		$k['error'] = $ma[1];
	}else{
		$curl = explode(',', str_replace('DL:', '', $curl));
		if($curl[2]) {
			$k['error'] = 'You have to wait ' . $curl[2] . ' seconds ! This funciton is not currently supported :(';
		}else{
			$pr_url = $m[1] . '://' . $curl[0] . '/cgi-bin/rsapi.cgi?sub=download';
			if(isset($user_info['us_username']) && isset($user_info['us_password'])) {
				$pr_url .= '&login=' . $user_info['us_username'] . '&password=' . $user_info['us_password']; 
			}
			$pr_url .= '&fileid=' . $m[2] . '&filename=' . $m[3] . '&dlauth=' . $curl[1];
			
			$curl = OC_ocDownloaderFile::execURL($pr_url);
			if(preg_match('/^(ERROR.*)(\n) {0,1}.*$/', $curl, $m)) {
				$k['error'] = $m[1];
			}else{
				$k = OC_ocDownloaderFile::getHttpFile($pr_url, $_POST['url']);
			}
		}
	}
}else{
	$k['error'] = 'This file does not exists (URL must be https://rapidshare.com/files...)';
}
