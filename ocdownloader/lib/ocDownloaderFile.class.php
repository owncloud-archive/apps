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

/**
 * This class manages ocDownloader within file interactions. 
 */
class OC_ocDownloaderFile {
	
	/**
	 * Get the file by an URL
	 * @param URL of the file
	 */
	public static function getHttpFile($file, $pr_transfer = NULL) {
		try{
	    	if(!self::remoteFileExists($file)) {
	    		return 'The file does not exists ...';
	    	}
			
			if(!isset($pr_transfer)) {
				$fileinfo = pathinfo($file);
			}else{
				$fileinfo = pathinfo($pr_transfer);
			}
			$filename = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) : $fileinfo['basename'];
			
			if(strpos($filename, 'rsapi.cgi')) {
				$filename = substr($filename, 0, strpos($filename, '&'));
			}
		  	
			if(OC_Filesystem::file_exists('/Downloads/' . $filename)) {
				$filename = md5(rand()) . '_' . $filename;
			}
			$fs = OC_Filesystem::fopen('/Downloads/' . $filename, 'w');
			
			$size = self::getRemoteFileSize($file);
			if($size == 0) {
				return 'Error ! Null file size.';
			}
			
		    switch(strtolower($fileinfo['extension'])) {
		        case 'exe': $ctype = 'application/octet-stream'; break;
		        case 'zip': $ctype = 'application/zip'; break;
		        case 'mp3': $ctype = 'audio/mpeg'; break;
		        case 'mpg': $ctype = 'video/mpeg'; break;
		        case 'avi': $ctype = 'video/x-msvideo'; break;
				case 'png': $ctype = 'image/png'; break;
		        default:    $ctype = 'application/force-download';
		    }
			
		    $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
		    $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
		
		    /*header("Cache-Control: cache, must-revalidate");  
		    header("Pragma: public");
		    header('Content-Type: ' . $ctype);
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		    header('Content-Length: ' . ($seek_end - $seek_start + 1));*/
		
		    $fp = fopen($file, 'rb');
			set_time_limit(0);
		    while(!feof($fp)) {
		        $data = fread($fp, 1024*8);
		        if($data == '') {
		        	break;
		        }
				fwrite($fs, $data);
		    }
		
		    fclose($fp);
			fclose($fs);
			
			return Array('ok' => 'Transfer completed successfully. The file has been moved in your Downloads folder.');
		}catch(exception $e) {
			return Array('error' => $e->getMessage());
		}
	}

	/**
	 * cURL session
	 * @param $url The URL to be executed with curl
	 * @return The cURL result
	 */
	public static function execURL($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		$result = curl_exec($ch);
	    curl_close($ch);
	    return $result; 
	}

	/**
	 * Get size of the remote file
	 * @param $remorteFile The remote file URL
	 * @return Int Size of the remote file 
	 */
	private static function getRemoteFileSize($remoteFile) {
		$ch = curl_init($remoteFile);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data === false) {
			return 0;
		}
		
		$contentLength = 'unknown';
		$status = 'unknown';
		if(preg_match('/^HTTP\/1\.[01] (\d\d\d)/', $data, $matches)) {
		  	$status = (int)$matches[1];
		}
		if(preg_match('/Content-Length: (\d+)/', $data, $matches)) {
		  	$contentLength = (int)$matches[1];
		}
		
		return $contentLength;
	}
	
	/**
	 * Check if the remote file exists
	 * @param $url The remote file URL
	 * @return Boolean
	 */
	private static function remoteFileExists($url) {
		$f = @fopen($url, 'r');
		if($f) {
			fclose($f);
			return TRUE;
		}
		return FALSE;
	}
	
}