<?php

/**
 * 
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class OC_Search_Lucene_Hooks {

	/**
	 * handle file writes (triggers reindexing)
	 * 
	 * the file indexing is queued as a background job
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param $param array from postWriteFile-Hook
	 */
	public static function indexFile(array $param) {
		if (isset($param['path'])) {
			//Add Background Job:
			\OCP\BackgroundJob::addQueuedTask(
					'search_lucene',
					'OC_Search_Lucene_Indexer',
					'indexFile',
					$param['path'] );
		} else {
			\OCP\Util::writeLog('search_lucene',
				'missing path parameter',
				OC_Log::WARN);
		}
	}

	/**
	 * handle file renames (triggers indexing and deletion)
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param $param array from postRenameFile-Hook
	 */
	public static function renameFile(array $param) {
		if (isset($param['newpath'])) {
			self::indexFile(array('path'=>$param['newpath']));
		}
		if (isset($param['oldpath'])) {
			self::deleteFile(array('path'=>$param['oldpath']));
		}
	}

	/**
	 * remove a file from the lucene index when deleting a file
	 *
	 * file deletion from the index is queued as a background job
	 *
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param $param array from postDeleteFile-Hook
	 */
	static public function deleteFile(array $param) {
		// we cannot use post_delete as $param would not contain the id
		// of the deleted file and we could not fetch it with getId
		if (isset($param['path'])) {
			//Add Background Job:
			\OCP\BackgroundJob::addQueuedTask(
					'search_lucene',
					'OC_Search_Lucene',
					'deleteFile',
					$param['path'] );
		} else {
			\OCP\Util::writeLog('search_lucene',
					'missing path parameter',
					OC_Log::WARN);
		}

	}

}
