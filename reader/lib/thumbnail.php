<?php
	
	function check_thumb_exists($location) {
		$image_path = rtrim($location,'pdf').'png';
		if(!file_exists('/opt/lampp/htdocs/owncloud/data/priyanka/reader'.$image_path)) {
			$check = "false";
		}
		else
			$check = "true";
		return $check;
	}

	function thumb($path) {
		$thumb_path = \OCP\Config::getSystemValue( 'datadirectory' ).'/'.\OC_User::getUser().'/reader';
		if (file_exists($thumb_path.$path)) {
			return new \OC_Image($thumb_path.$path);
		}
		if (!\OC\Files\Filesystem::file_exists($path)) {
			return false;
		}
	}

	function check_dir_exists($current_dir,$dir) {
		$owner = OCP\USER::getUser();
		$path = OCP\Config::getSystemValue("datadirectory").'/'. $owner .'/reader';
		$path .= $current_dir.$dir;
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
			return true;
		}
		else
			return true;
	}

?>
