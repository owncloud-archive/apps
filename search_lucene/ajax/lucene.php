<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('search_lucene');
session_write_close();

function index() {
	$fileIds = OC_Search_Lucene_Indexer::getUnindexed();

	$eventSource = new OC_EventSource();
	$eventSource->send('count', count($fileIds));

	$skippedDirs = explode(';', OCP\Config::getUserValue(OCP\User::getUser(), 'search_lucene', 'skipped_dirs', '.git;.svn;.CVS;.bzr'));

	$query = \OC_DB::prepare('INSERT INTO `*PREFIX*lucene_status` VALUES (?,?)');

	foreach ($fileIds as $id) {
		$skipped = false;
		$result = false;
		$path = OC\Files\Filesystem::getPath($id);
		$eventSource->send('indexing', $path);
		foreach ($skippedDirs as $skippedDir) {
			if (strpos($path, '/' . $skippedDir . '/') !== false //contains dir
				|| strrpos($path, '/' . $skippedDir) === strlen($path) - (strlen($skippedDir) + 1) // ends with dir
			) {
				$result = $query->execute(array($id, 'S'));
				$skipped = true;
				break;
			}
		}
		if (!$skipped) {
			if (OC_Search_Lucene_Indexer::indexFile($path)) {
				$result = $query->execute(array($id, 'I'));
			} else {
				$result = $query->execute(array($id, 'E'));
			}
		}

		if (!$result) {
			OC_JSON::error(array('message' => 'Could not index file.'));
			$eventSource->send('error', $path);
		}
	}

	$eventSource->send('done', '');
	$eventSource->close();
}

function handleOptimize() {
	OC_Search_Lucene::optimizeIndex();
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
