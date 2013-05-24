<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$file = isset($_GET['link']) ? $_GET['link'] : '';

$user = OCP\User::getUser();

$path_parts = pathinfo($file);
$extension = $path_parts['extension'];

$sourceDir = OC::$SERVERROOT.'/data/previews/'.$user.'/files'.$path_parts['dirname'];
$outputFile = $sourceDir.'/'.$path_parts['basename'];

/*if (!(file_exists($outputFile) && (filemtime($outputFile) > filemtime($inputFile)))){
	// New file, create a preview and store in local file system
	$command = 'python /opt/jischtml5/tools/commandline/WordDownOO.py --dataURIs --epub '.escapeshellarg($inputFile).' '.escapeshellarg($outputDir);
	system($command, $retval);
}*/

switch ($extension){
	case "epub":
		//Download epub
		header("Content-type:application/epub+zip");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment;filename=".$path_parts['basename']);
		readfile($outputFile);
	case "pdf":
		//TODO
		break;
	default:
		$content = file_get_contents($outputFile);
		print $content;
}
