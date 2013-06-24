<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$file = isset($_GET['link']) ? $_GET['link'] : '';
$sid = isset($_GET['sid']) ? $_GET['sid'] : '';

$user = OCP\User::getUser();

$solr = new \Apache_Solr_Service('localhost', 9997, '/solr/fascinator/');

$path_parts = pathinfo($file);
$extension = $path_parts['extension'];

if($extension === "doc" || $extension === "docx") {
	$file_path = "/files".$file;
	$query = 'full_path:"/files'. $file .'"';
	$preview = $path_parts['filename'].'.htm';
}
else {
	$file_path = "/files";
	$query = 'identifier:"'. $file .'"';
	$preview = '/'.basename($path_parts['dirname']).'/'.$path_parts['basename'];
}

static $storage_id = "";

try
{
	$results = $solr->search($query, 0, 20);
	$base_ids = array();
	if($results)
	{
		foreach ($results->response->docs as $doc) {
			foreach ($doc as $field => $value) {
				if($field === "storage_id"){
					$storage_id = $value;
					break;
				}
			}
		}
	}
	
	if(!empty($sid))
	{
		$url = 'http://localhost:9997/portal/default/download/'.$sid.$preview;
	}
	else{
		$url = 'http://localhost:9997/portal/default/download/'.$storage_id.'/'.$preview;
	}
	
	
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
  	
  	if(empty($sid)){
	  	//Find the source and alter the source
	  	$rgx = "/<img [^>]*src=\"([^\"]+)\"[^>]*>/";
	  	 
	  	$matches = array();
	  	 
	  	$src = preg_match_all($rgx, $content, $matches, PREG_SET_ORDER);
	
	  	$altered_tags = array();
	  	
	  	foreach ($matches as $value) {
	  		$tag = $value[0];
	  		$src_link = $value[1];
	  		$new_link = $src_link."?sid=".$storage_id;
	  		$new_tag = str_replace($src_link, $new_link, $tag);
	  		$altered_tags[$tag] = $new_tag;
	  	}
	  	//var_dump($altered_tags);
	  	//var_dump($matches);
	  	
	  	foreach ($altered_tags as $key => $value) {
	  		$content = str_replace($key, $value, $content);
	  	}
  	}
	
  	echo $content;
  	//echo $result;
	
	//$data = array();
	
	/*$options = array(
			'http' => array(
					'header'  => "Content-type: text/html",
					'method'  => 'GET',
					'content' => http_build_query($data),
			),
	);
	
	$context  = stream_context_create($options);*/
	//$result = file_get_contents($url);//, false, $context);
	
	//var_dump($result);
	
	//var_dump($result);
	/*$q = 'file_path:"'. $file_path .'" AND (id:"'.implode('" OR id:"', $base_ids).'")';
	$query2 = 'file_path:"'. $file_path .'" AND preview_type:"html" AND (preview:"'.$path_parts['filename'].'.htm" OR preview:"'.$path_parts['basename'].'.html")';
	$real_doc = $solr->search($query2, 0, 10);
	if($real_doc){
		foreach ($real_doc->response->docs as $doc) {
			foreach ($doc as $field => $value) {
				if($field === "file_path"){
					if(is_array($value)){
						$v = end($value);
					}
					else {
						$v = $value;
					}
					if($v === $file_path)
					{
						
					}
				}
			}
		}
	}*/
}
catch (Exception $e)
{
	// in production you'd probably log or email this error to an admin
	// and then show a special message to the user but for this example
	// we're going to show the full exception
	die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
}

// $sourceDir = OC::$SERVERROOT.'/data/previews/'.$user.'/files'.$path_parts['dirname'];
// $outputFile = $sourceDir.'/'.$path_parts['basename'];

/*if (!(file_exists($outputFile) && (filemtime($outputFile) > filemtime($inputFile)))){
	// New file, create a preview and store in local file system
	$command = 'python /opt/jischtml5/tools/commandline/WordDownOO.py --dataURIs --epub '.escapeshellarg($inputFile).' '.escapeshellarg($outputDir);
	system($command, $retval);
}*/

/*switch ($extension){
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
}*/
