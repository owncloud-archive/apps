<?php

namespace OCA\Search_Lucene;

class IndexJob extends \OC\BackgroundJob\Job {
	public function run($arguments){
		if (!empty($arguments['user'])) {
			$user = $arguments['user'];
			\OC_Util::tearDownFS();
			\OC_Util::setupFS($user);
			$fileIds = Status::getUnindexed();
			\OCP\Util::writeLog(
				'search_lucene',
				'background job indexing '.count($fileIds).' files for '.$user,
				\OCP\Util::DEBUG
			);

			$view = new \OC\Files\View('/' . $user . '/files');
			$lucene = new \OCA\Search_Lucene\Lucene($user);
			
			$indexer = new Indexer($view, $lucene);
			$indexer->indexFiles($fileIds);
		} else {
			\OCP\Util::writeLog(
				'search_lucene',
				'indexer job did not receive user in arguments: '.json_encode($arguments),
				\OCP\Util::DEBUG
			);
		}
	}
}
