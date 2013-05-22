<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'html';
$user = OCP\User::getUser();

$path_parts = pathinfo($file);
$file_name = basename($file, '.'.$path_parts['extension']);

$mime = "application/msword";

if($dir === '/'){
	$sourceDir = $dir;
	$previewDir = $dir.$user.'/'.$file_name;
}
else{
	$sourceDir = $dir.'/';
	$previewDir = $dir.'/'.$file_name;
}

$inputFile = OC::$SERVERROOT.'/data/'.$user.'/files'.$sourceDir.$file;
$outputDir = OC::$SERVERROOT.'/data/previews/'.$user.'/files'.$sourceDir.$file;
$outputFile = $outputDir.'/'.$file_name.'.html';

//$web = OC::$WEBROOT;

switch ($type)
{
	case "epub":
		$outputFile = $outputDir.'/'.$file_name.'.epub';
		break;
	case "pdf":
		$outputFile = $outputDir.'/'.$file_name.'.pdf';
		break;
	default:
		$outputFile = $outputDir.'/'.$file_name.'.html';
}

/*if (!(file_exists($outputFile) && (filemtime($outputFile) > filemtime($inputFile)))){
	// New file, create a preview and store in local file system
	$command = 'python /opt/jischtml5/tools/commandline/WordDownOO.py --dataURIs --epub '.escapeshellarg($inputFile).' '.escapeshellarg($outputDir);
	system($command, $retval);
}*/

switch ($type){
	case "epub":
		//Download epub
		header("Content-type:application/epub+zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=".$file_name.'.epub');
		readfile($outputFile);
	case "pdf":
		//TODO
		break;
	default:
		$content = file_get_contents($outputFile);
		print $content;
}

//TODO: stop using data URIs
