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
			$arguments = array('user' => \OCP\User::getUser());
			//Add Background Job:
			BackgroundJob::registerJob( '\OCA\Search_Lucene\IndexJob', $arguments );
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
		if (isset($param['oldpath'])) {
			//delete from lucene index
			$lucene = new Lucene($user);
			$lucene->deleteFile($param['oldpath']);
		}
		if (isset($param['newpath'])) {
			$view = new \OC\Files\View('/' . $user . '/files');
			$info = $view->getFileInfo($param['newpath']);
			Status::fromFileId($info['fileid'])->markNew();
			self::indexFile(array('path'=>$param['newpath']));
		}
	}

	/**
	 * remove a file from the lucene index when deleting a file
	 *
	 * file deletion from the index is queued as a background job
	 *
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param $param array from deleteFile-Hook
	 */
	static public function deleteFile(array $param) {
		Util::writeLog(
			'search_lucene',
			'deleting status for ' . json_encode($param),
			Util::DEBUG
		);
		// we cannot use post_delete as $param would not contain the id
		// of the deleted file and we could not fetch it with getId
		if (isset($param['path'])) {
			$user = \OCP\User::getUser();
			//delete status
			$deletedIds = Status::getDeleted();
			foreach ($deletedIds as $fileid) {
				Util::writeLog(
					'search_lucene',
					'deleting status for ('.$fileid.') ',
					Util::DEBUG
				);
				\OCA\Search_Lucene\Status::delete($fileid);
			}
			//delete from lucene
			$lucene = new Lucene($user);
			$lucene->deleteFile($param['path']);
		} else {
			Util::writeLog(
				'search_lucene',
				'missing path parameter',
				Util::ERROR
			);
		}

	}
}
