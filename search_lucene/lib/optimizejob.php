<?php

namespace OCA\Search_Lucene;

class OptimizeJob extends \OC\BackgroundJob\TimedJob {
	
	
	public function __construct() {
		$this->setInterval(86400); //execute at most once a day
	}
	
	public function run($arguments){
		if (!empty($arguments['user'])) {
			$user = $arguments['user'];
			\OCP\Util::writeLog(
				'search_lucene',
				'background job optimizing index for '.$user,
				\OCP\Util::DEBUG
			);

			$lucene = new \OCA\Search_Lucene\Lucene($user);
			// check if we have to rebuild the index because old pk: entries
			// from pre 0.6.0 are still in the cache
			
			\Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0); 
			$hits = $lucene->find('pk:*');
			foreach ($hits as $hit) {
				\OCP\Util::writeLog(
					'search_lucene',
					'deleting deprecated index document for ' . $hit->id . ':' . $hit->path ,
					\OCP\Util::DEBUG
				);
				$lucene->index->delete($hit);
			}
			
			$lucene->optimizeIndex();
		} else {
			\OCP\Util::writeLog(
				'search_lucene',
				'optimize job did not receive user in arguments: '.json_encode($arguments),
				\OCP\Util::DEBUG
			);
		}
	}
}
