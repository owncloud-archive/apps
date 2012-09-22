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

class OC_Compress {

	/**
	 * Compress File or Folder
	 * @param $target The target to compress
	 * @return Boolean  
	 */
	public static function compressTarget($target) {
		$oc_target = OC::$CONFIG_DATADIRECTORY . $target;
		
		if(OC_Filesystem::is_file($target)) {
			$fileinfo = pathinfo($oc_target);
			$archiveName = $fileinfo['filename'];
			$dirTarget = $fileinfo['dirname'];
		}else{
			$archiveName = basename($oc_target);
			$dirTarget = dirname($oc_target);
		}
		$archiveName .= '.tar';
		
		if(file_exists($dirTarget . '/' . $archiveName)) {
			$archiveName = md5(rand()) . '_' . $archiveName;
		}
		
		require_once '../config/config.php';
		exec($_CompressConf['tar_bin_path'] . " cf " . $dirTarget . '/' . $archiveName . " " . $oc_target);
		exec($_CompressConf['gzip_bin_path'] . " -9 " . $dirTarget . '/' . $archiveName);
	}

}
