<?php

namespace OCA\Search_Lucene;

use \OCP\BackgroundJob;
use \OCP\Util;

/**
 * 
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class Hooks {

	/**
	 * classname which used for hooks handling
	 * used as signalclass in OC_Hooks::emit()
	 */
	const CLASSNAME = 'Hooks';

	/**
	 * handle for indexing file
	 *
	 * @param string $path
	 */
	const handle_post_write = 'indexFile';

	/**
	 * handle for renaming file
	 *
	 * @param string $path
	 */
	const handle_post_rename = 'renameFile';

	/**
	 * handle for removing file
	 *
	 * @param string $path
	 */
	const handle_delete = 'deleteFile';

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
			$param['user'] = \OCP\User::getUser();
			//Add Background Job:
			BackgroundJob::addQueuedTask(
					'search_lucene',
					'OCA\Search_Lucene\Hooks',
					'doIndexFile',
					json_encode($param) );
		} else {
			Util::writeLog('search_lucene',
				'missing path parameter',
				Util::WARN);
		}
	}
	static public function doIndexFile($param) {
		$data = json_decode($param);
		if ( ! isset($data->path) ) {
			Util::writeLog('search_lucene',
				'missing path parameter',
				Util::WARN);
			return false;
		}
		if ( ! isset($data->user) ) {
			Util::writeLog('search_lucene',
				'missing user parameter',
				Util::WARN);
			return false;
		}
		Indexer::indexFile($data->path, $data->user);
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
			$param['user'] = \OCP\User::getUser();
			//Add Background Job:
			BackgroundJob::addQueuedTask(
					'search_lucene',
					'OCA\Search_Lucene\Hooks',
					'doDeleteFile',
					json_encode($param) );
		} else {
			Util::writeLog('search_lucene',
					'missing path parameter',
					Util::WARN);
		}

	}
	static public function doDeleteFile($param) {
		$data = json_decode($param);
		if ( ! isset($data->path) ) {
			Util::writeLog('search_lucene',
				'missing path parameter',
				Util::WARN);
			return false;
		}
		if ( ! isset($data->user) ) {
			Util::writeLog('search_lucene',
				'missing user parameter',
				Util::WARN);
			return false;
		}
		Lucene::deleteFile($data->path, $data->user);
	}

}
