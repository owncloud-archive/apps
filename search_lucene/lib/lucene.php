<?php

//require_once 'Zend/Search/Lucene.php';
//require_once 'Zend/Search/Lucene/Index/Term.php';
//require_once 'Zend/Search/Lucene/Search/Query/Term.php';

/**
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class OC_Search_Lucene extends OC_Search_Provider {


	/**
	 * opens or creates the users lucene index
	 * 
	 * stores the index in <datadirectory>/<user>/lucene_index
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @return Zend_Search_Lucene_Interface 
	 */
	public static function openOrCreate() {

		try {
			
			Zend_Search_Lucene_Analysis_Analyzer::setDefault(
				new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive()
			); //let lucene search for numbers as well as words
			
			// Create index
			//$ocFilesystemView = OC_App::getStorage('search_lucene'); // encrypt the index on logout, decrypt on login

			$indexUrl = \OC_User::getHome(\OCP\User::getUser()) . '/lucene_index';
			if (file_exists($indexUrl)) {
				$index = Zend_Search_Lucene::open($indexUrl);
			} else {
				$index = Zend_Search_Lucene::create($indexUrl);
				//todo index all user files
				
			}
		} catch ( Exception $e ) {
			OC_Log::write('search_lucene',
						$e->getMessage().' Trace:\n'.$e->getTraceAsString(),
						OC_Log::ERROR);
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
	 * @param Zend_Search_Lucene_Interface $index an optional index
	 * 
	 * @return void
	 */
	static public function optimizeIndex(Zend_Search_Lucene_Interface $index = null) {  

		if ($index === null) {
			$index = self::openOrCreate();
		}

		OC_Log::write('search_lucene',
					  'optimizing index ' . $path,
					  OC_Log::DEBUG);

		$index->optimize();

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
	static public function updateFile(Zend_Search_Lucene_Document $doc,
									  $path = '',
									  Zend_Search_Lucene_Interface $index = null) {

		if ($index === null) {
			$index = self::openOrCreate();
		}
		
		// TODO profile perfomance for searching before adding to index
		self::deleteFile($path, $index);

		OC_Log::write('search_lucene',
					  'adding ' . $path,
					  OC_Log::DEBUG);
		
		// Add document to the index
		$index->addDocument($doc);

		$index->commit();

	}

	/**
	 * removes a file frome the lucene index
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param string                       $path  path to the document to remove from the index
	 * @param Zend_Search_Lucene_Interface $index optional can be passed ro reuse an existing instance
	 * 
	 * @return void
	 */
	static public function deleteFile($path, Zend_Search_Lucene_Interface $index = null) {

		if ( $path === '' ) {
			//ignore the empty path element
			return;
		}

		if ($index === null) {
			$index = self::openOrCreate();
		}

		$root=  OC\Files\Filesystem::getRoot();
		$pk = md5($root.$path);

		OC_Log::write('search_lucene',
					  'searching hits for pk:' . $pk,
					  OC_Log::DEBUG);


		$hits = $index->find( 'pk:' . $pk ); //id would be internal to lucene

		OC_Log::write('search_lucene',
					  'found ' . count($hits) . ' hits ',
					  OC_Log::DEBUG);

		foreach ($hits as $hit) {
			OC_Log::write('search_lucene',
						  'removing ' . $hit->id . ':' . $hit->path . ' from index',
						  OC_Log::DEBUG);
			$index->delete($hit);
		}
	}

	/**
	 * performs a search on the users index
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param string $query lucene search query
	 * @return array of OC_Search_Result
	 */
	public function search($query){
		$results=array();
		if ( $query !== null ) {
			// * query * kills performance for bigger indexes
			// query * works ok
			// query is still best
			//FIXME emulates the old search but breaks all the nice lucene search query options
			//$query = '*' . $query . '*';
			//if (strpos($query, '*')===false) {
			//	$query = $query.='*'; // append query *, works ok
			//	TODO add end user guide for search terms ... 
			//}
			try {
				$index = self::openOrCreate(); 
				//default is 3, 0 needed to keep current search behaviour
				//Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0); 
				
				//$term  = new Zend_Search_Lucene_Index_Term($query);
				//$query = new Zend_Search_Lucene_Search_Query_Term($term);
				
				$hits = $index->find($query);

				//limit results. we cant show more than ~30 anyway. TODO use paging later
				for ($i = 0; $i < 30 && $i < count($hits); $i++) {
					$results[] = self::asOCSearchResult($hits[$i]);
				}

			} catch ( Exception $e ) {
				OC_Log::write('search_lucene',
							$e->getMessage().' Trace:\n'.$e->getTraceAsString(),
							OC_Log::ERROR);
			}

		}
		return $results;
	}

	/**
	 * converts a zend lucene search object to a OC_SearchResult
	 *
	 * Example:
	 * 
	 * Text | Some Document.txt
	 *      | /path/to/file, 148kb, Score: 0.55
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param Zend_Search_Lucene_Search_QueryHit $hit The Lucene Search Result
	 * @return OC_Search_Result an OC_Search_Result
	 */
	private static function asOCSearchResult(Zend_Search_Lucene_Search_QueryHit $hit) {

		$mimeBase = self::baseTypeOf($hit->mimetype);

		switch($mimeBase){
			case 'audio':
				$type='Music';
				break;
			case 'text':
				$type='Text';
				break;
			case 'image':
				$type='Images';
				break;
			default:
				if ($hit->mimetype=='application/xml') {
					$type='Text';
				} else {
					$type='Files';
				}
		}

		switch ($hit->mimetype) {
			case 'httpd/unix-directory':
				$url = OCP\Util::linkTo('files', 'index.php') . '?dir='.$hit->path;
				break;
			default:
				$url = OC::getRouter()->generate('download', array('file'=>$hit->path));
		}
		
		return new OC_Search_Result(
				basename($hit->path),
				dirname($hit->path)
					. ', ' . OC_Helper::humanFileSize($hit->size)
					. ', Score: ' . number_format($hit->score, 2),
				$url,
				$type
		);
	}

	/**
	 * get the base type of a mimetype string
	 * 
	 * returns 'text' for 'text/plain'
	 * 
	 * @author Jörn Dreyer <jfd@butonic.de>
	 * 
	 * @param string $mimetype mimetype
	 * @return string basetype 
	 */
	public static function baseTypeOf($mimetype) {
		return substr($mimetype, 0, strpos($mimetype, '/'));
	}

}
