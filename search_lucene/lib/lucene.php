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
	
	private function getIndexURL () {
		// TODO profile: encrypt the index on logout, decrypt on login
		//return OCP\Files::getStorage('search_lucene');
		return \OC_User::getHome($this->user) . '/lucene_index';
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

			$indexUrl = $this->getIndexURL();
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
			'optimizing index',
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
	 * @param int $fileid fileid to update
	 * 
	 * @return void
	 */
	public function updateFile(
		\Zend_Search_Lucene_Document $doc,
		$fileid
	) {

		// TODO profile perfomance for searching before adding to index
		$this->deleteFile($fileid);

		Util::writeLog(
			'search_lucene',
			'adding ' . $fileid .' '.json_encode($doc),
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
	 * @param int $fileid fileid to remove from the index
	 * 
	 * @return int count of deleted documents in the index
	 */
	public function deleteFile($fileid) {

		$hits = $this->index->find( 'fileid:' . $fileid );

		Util::writeLog(
			'search_lucene',
			'found ' . count($hits) . ' hits for fileid ' . $fileid,
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
		
		return count($hits);
	}

	public function find ($query) {
		return $this->index->find($query);
	}

}
