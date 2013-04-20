<?php

namespace OCA\Search_Lucene;

class IndexJob extends \OC\BackgroundJob\QueuedJob{
	public function run($param){
		Hooks::doIndexFile($param);
	}
}

class DeleteJob extends \OC\BackgroundJob\QueuedJob{
	public function run($param){
		Hooks::doDeleteFile($param);
	}
}
