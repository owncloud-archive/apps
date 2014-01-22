<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('search_lucene');
session_write_close();

function index() {
	if ( isset($_GET['fileid']) ){
		$fileIds = array($_GET['fileid']);
	} else {
		$fileIds = OCA\Search_Lucene\Status::getUnindexed();
	}

	$eventSource = new OC_EventSource();
	$eventSource->send('count', count($fileIds));

	$user = OCP\User::getUser();
	
	OC_Util::tearDownFS();
	OC_Util::setupFS($user);
	
	$view = new OC\Files\View('/' . $user . '/files');
	$lucene = new OCA\Search_Lucene\Lucene($user);
	
	$indexer = new OCA\Search_Lucene\Indexer($view, $lucene);
	$indexer->indexFiles($fileIds, $eventSource);

	$eventSource->send('done', '');
	$eventSource->close();
}

function handleOptimize() {
	OCA\Search_Lucene\Lucene::optimizeIndex();
}

if ($_GET['operation']) {
	switch ($_GET['operation']) {
		case 'index':
			index();
			break;
		case 'optimize':
			handleOptimize();
			break;
		default:
			OCP\JSON::error(array('cause' => 'Unknown operation'));
	}
}
