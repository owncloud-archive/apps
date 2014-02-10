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
		$user = \OCP\User::getUser();
		if (!empty($user)) {
			$arguments = array('user' => $user);
			//Add Background Job:
			BackgroundJob::registerJob( '\OCA\Search_Lucene\IndexJob', $arguments );
		} else {
			\OCP\Util::writeLog(
				'search_lucene',
				'Hook indexFile could not determine user when called with param '.json_encode($param),
				\OCP\Util::DEBUG
			);
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
			$user = \OCP\User::getUser();
		if (!empty($param['oldpath'])) {
			//delete from lucene index
			$lucene = new Lucene($user);
			$lucene->deleteFile($param['oldpath']);
		}
		if (!empty($param['newpath'])) {
			$view = new \OC\Files\View('/' . $user . '/files');
			$info = $view->getFileInfo($param['newpath']);
			Status::fromFileId($info['fileid'])->markNew();
			self::indexFile(array('path'=>$param['newpath']));
		}
	}

	/**
	 * deleteFile triggers the removal of any deleted files from the index
	 *
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param $param array from deleteFile-Hook
	 */
	static public function deleteFile(array $param) {
		// we cannot use post_delete as $param would not contain the id
		// of the deleted file and we could not fetch it with getId
		$user = \OCP\User::getUser();
		$lucene = new Lucene($user);
		$deletedIds = Status::getDeleted();
		$count = 0;
		foreach ($deletedIds as $fileid) {
			Util::writeLog(
				'search_lucene',
				'deleting status for ('.$fileid.') ',
				Util::DEBUG
			);
			//delete status
			\OCA\Search_Lucene\Status::delete($fileid);
			//delete from lucene
			$count += $lucene->deleteFile($fileid);
			
		}

	}
	
	/**
	 * was used by backgroundjobs to index individual files
	 * 
	 * @deprecated since version 0.6.0
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param $param array from deleteFile-Hook
	 */
	static public function doIndexFile(array $param) {/* ignore */}
	
}
