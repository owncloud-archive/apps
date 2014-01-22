<?php

namespace OCA\Search_Lucene;

use \OC\Files\Filesystem;
use \OCP\User;
use \OCP\Util;

/**
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class Lucene {

	/**
	 * classname which used for hooks handling
	 * used as signalclass in OC_Hooks::emit()
	 */
	const CLASSNAME = 'Lucene';
	
	public $user;
	public $index;

	public function __construct($user) {
		$this->user = $user;
		$this->index = self::openOrCreate();
	}
	
	/**
	 * opens or creates the users lucene index
	 * 
	 * stores the index in <datadirectory>/<user>/lucene_index
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @return Zend_Search_Lucene_Interface 
	 */
	private function openOrCreate() {

		try {
			
			//let lucene search for numbers as well as words
			\Zend_Search_Lucene_Analysis_Analyzer::setDefault(
				new \Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive()
			);
			
			// Create index
			// TODO profile: encrypt the index on logout, decrypt on login
			//$ocFilesystemView = OCP\Files::getStorage('search_lucene');

			$indexUrl = \OC_User::getHome($this->user) . '/lucene_index';
			if (file_exists($indexUrl)) {
				$index = \Zend_Search_Lucene::open($indexUrl);
			} else {
				$index = \Zend_Search_Lucene::create($indexUrl);
				//todo index all user files
			}
		} catch ( Exception $e ) {
			Util::writeLog(
				'search_lucene',
				$e->getMessage().' Trace:\n'.$e->getTraceAsString(),
				Util::ERROR
			);
			return null;
		}
		

		return $index;
	}

	/**
	 * optimizes the lucene index
	 * 
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @return void
	 */
	public function optimizeIndex() {

		Util::writeLog(
			'search_lucene',
			'optimizing index ',
			Util::DEBUG
		);

		$this->index->optimize();

	}

	/**
	 * upates a file in the lucene index
	 * 
	 * 1. the file is deleted from the index
	 * 2. the file is readded to the index
	 * 3. the file is marked as index in the status table
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param Zend_Search_Lucene_Document $doc  the document to store for the path
	 * @param string                      $path path to the document to update
	 * 
	 * @return void
	 */
	public function updateFile(
		\Zend_Search_Lucene_Document $doc,
		$path = ''
	) {

		
		// TODO profile perfomance for searching before adding to index
		$this->deleteFile($path);

		Util::writeLog(
			'search_lucene',
			'adding ' . $path ,
			Util::DEBUG
		);
		
		// Add document to the index
		$this->index->addDocument($doc);

		$this->index->commit();

	}

	/**
	 * removes a file frome the lucene index
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param string                       $path  path to the document to remove from the index
	 * 
	 * @return void
	 */
	public function deleteFile($path) {
		Util::writeLog(
			'search_lucene',
			'Lucene::deleteFile('.$path.')',
			Util::DEBUG
		);
		if ( $path === '' ) {
			//ignore the empty path element
			return;
		}
		
		//TODO remember view as instance member?
		$view = new \OC\Files\View('/' . $this->user . '/files');

		if ( ! $view ) {
			Util::writeLog(
				'search_lucene',
				'could not resolve filesystem view',
				Util::WARN
			);
			return false;
		}

		$root= $view->getRoot();
		$pk = md5($root.$path);

		Util::writeLog(
			'search_lucene',
			'searching hits for pk:' . $pk,
			Util::DEBUG
		);

		$hits = $this->index->find( 'pk:' . $pk ); //id would be internal to lucene

		Util::writeLog(
			'search_lucene',
			'found ' . count($hits) . ' hits ',
			Util::DEBUG
		);

		foreach ($hits as $hit) {
			Util::writeLog(
				'search_lucene',
				'removing ' . $hit->id . ':' . $hit->path . ' from index',
				Util::DEBUG
			);
			$this->index->delete($hit);
		}
	}

	public function find ($query) {
		return $this->index->find($query);
	}
}
