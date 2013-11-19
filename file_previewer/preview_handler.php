<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$file = isset($_GET['fname']) ? $_GET['fname'] : '';

$user = OCP\User::getUser();

$p_parts = pathinfo($file);
$basename = $p_parts["basename"]; 

$filename = str_replace("_html/".$basename, "", $file);

if (\OC\Files\Filesystem::isReadable($filename)) {
	list($storage) = \OC\Files\Filesystem::resolvePath($filename);
	if ($storage instanceof \OC\Files\Storage\Local) {
		$full_path = \OC\Files\Filesystem::getLocalFile($filename);
		$path_parts = pathinfo($filename);
		$preview = $path_parts['basename'].'_html/'.$basename;
		$preview_path = dirname($full_path).'/'.$preview;
		if(file_exists($preview_path)){
			echo file_get_contents($preview_path);
			return;
		}
		else{
			header("HTTP/1.0 404 Preview Not Found");
			$tmpl = new OC_Template('', '404', 'guest');
			$tmpl->assign('file', $name);
			$tmpl->printPage();
			return;
		}
	}
} elseif (!\OC\Files\Filesystem::file_exists($filename)) {
	header("HTTP/1.0 404 Not Found");
	$tmpl = new OC_Template('', '404', 'guest');
	$tmpl->assign('file', $name);
	$tmpl->printPage();
} else {
	header("HTTP/1.0 403 Forbidden");
	die('403 Forbidden');
}