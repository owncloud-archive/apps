<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$file = isset($_GET['link']) ? $_GET['link'] : '';

$user = OCP\User::getUser();

$path_parts = pathinfo($file);
$extension = $path_parts['extension'];

$sourceDir = OC::$SERVERROOT.'/data/previews/'.$user.'/files'.$path_parts['dirname'];
$outputFile = $sourceDir.'/'.$path_parts['basename'];

if (!(file_exists($outputFile) && (filemtime($outputFile) > filemtime($inputFile)))){
 // New file, create a preview and store in local file system
 //$command = 'python /opt/jischtml5/tools/commandline/WordDownOO.py --dataURIs --epub '.escapeshellarg($inputFile).' '.escapeshellarg($outputDir);
	$command = 'wget -output-file='.$outputFile;
	system($command, $retval);
}

switch ($extension) {
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
















/*$url = 'http://localhost:9997/portal/default/download/'.$storage_id.'/'.$preview;
header('Location: /index.php');

$cookie_file = '/tmp/cookie-session';
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
#curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
#curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
#curl_setopt($ch, CURLOPT_USERPWD, "admin:admin" );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
 
$result = curl_getinfo($ch);
curl_close($ch);

echo $content;*/