<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('search_lucene');
session_write_close();

function index() {
	$fileIds = OCA\Search_Lucene\Indexer::getUnindexed();

	$eventSource = new OC_EventSource();
	$eventSource->send('count', count($fileIds));

	$skippedDirs = explode(';', OCP\Config::getUserValue(OCP\User::getUser(), 'search_lucene', 'skipped_dirs', '.git;.svn;.CVS;.bzr'));

	$query = OC_DB::prepare('INSERT INTO `*PREFIX*lucene_status` VALUES (?,?)');

	foreach ($fileIds as $id) {
		$skipped = false;

		try{
			//before we start mark the file as error so we know there was a problem when the php execution dies
			$result = $query->execute(array($id, 'E'));

			$path = OC\Files\Filesystem::getPath($id);
			$eventSource->send('indexing', $path);

			//clean jobs for indexed file
			$param=json_encode(array('path'=>$path,'user'=>OCP\User::getUser()));
			$cleanjobquery = OC_DB::prepare('DELETE FROM `*PREFIX*queuedtasks` WHERE `app`=? AND `parameters`=?');
			$cleanjobquery->execute(array('search_lucene',$param));

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
				if (OCA\Search_Lucene\Indexer::indexFile($path, OCP\User::getUser())) {
					$result = $query->execute(array($id, 'I'));
				}
			}

			if (!$result) {
				OC_JSON::error(array('message' => 'Could not index file.'));
				$eventSource->send('error', $path);
			}
		} catch (PDOException $e) { //sqlite might report database locked errors when stock filescan is in progress
			//this also catches db locked exception that might come up when using sqlite
			\OCP\Util::writeLog('search_lucene',
				$e->getMessage() . ' Trace:\n' . $e->getTraceAsString(),
				\OCP\Util::ERROR);
			OC_JSON::error(array('message' => 'Could not index file.'));
			$eventSource->send('error', $e->getMessage());
			//try to mark the file as new to let it reindex
			$query->execute(array($id, 'N')); // Add UI to trigger rescan of files with status 'E'rror?
		}
	}

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
