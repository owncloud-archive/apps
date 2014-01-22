<?php

namespace OCA\Search_Lucene;

class IndexJob extends \OC\BackgroundJob\Job {
	public function run($arguments){
		if (isset($arguments['user'])) {
			$user = $arguments['user'];
			$fileIds = Status::getUnindexed();
			\OCP\Util::writeLog(
				'search_lucene',
				'background job indexing '.count($fileIds).' files for '.$user,
				\OCP\Util::DEBUG
			);

			\OC_Util::tearDownFS();
			\OC_Util::setupFS($user);
			$view = new \OC\Files\View('/' . $user . '/files');
			
			$indexer = new Indexer($view);
			$indexer->indexFiles($fileIds);
		} else {
			\OCP\Util::writeLog(
				'search_lucene',
				'indexer job did not receive user in arguments',
				\OCP\Util::DEBUG
			);
		}
	}
}
