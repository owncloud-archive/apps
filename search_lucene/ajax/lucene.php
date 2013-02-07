<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('search_lucene');

function handleIndex($id = -1) {
	
	set_time_limit(0);//scanning can take ages
	$eventSource = new OC_EventSource();
	
	$path = OC\Files\Filesystem::getPath($id);
	
	OC_Util::obEnd();
	$eventSource->send('indexing', array('file'=>$path));
	ob_start();
	
	$query = \OC_DB::prepare('INSERT INTO `*PREFIX*lucene_status`'
							.' VALUES (?,?)');
	
	$skipped_dirs = explode(';', OCP\Config::getUserValue(OCP\User::getUser(), 'search_lucene', 'skipped_dirs', '.git;.svn;.CVS;.bzr'));
	$skipped = false;
	
	foreach($skipped_dirs as $skipped_dir) {
		if (strpos($path, '/'.$skipped_dir.'/') !== false  //contains dir
			|| strrpos($path, '/'.$skipped_dir) === strlen($path) - (strlen($skipped_dir) + 1)  // ends with dir
		) {
			$result = $query->execute(array($id,'S'));
			$skipped = true;
			break;
		}
	}
	if ( ! $skipped ) {
		if (OC_Search_Lucene_Indexer::indexFile($path)) {
			$result = $query->execute(array($id,'I'));
		} else {
			$result = $query->execute(array($id,'E'));
		}
	}
	//TODO mark unsupported as U ...?
	
	if (!$result) {
		OC_JSON::error(array('message'=>'Could not index file.'));
		OC_Util::obEnd();
		$eventSource->send('error', array('message'=>'Could not index file.','file'=>$path));
		ob_start();
	}
	
	OC_Util::obEnd();
	$eventSource->send('done', '');
	$eventSource->close();
	exit();
}


function handleOptimize() {
	OC_Search_Lucene::optimizeIndex();
}

if ($_GET['operation']) {
	switch($_GET['operation']) {
		case 'index':
			handleIndex($_GET['id']);
			break;
		case 'optimize':
			handleOptimize();
			break;
		default:
			OCP\JSON::error(array('cause' => 'Unknown operation'));
	}
}
