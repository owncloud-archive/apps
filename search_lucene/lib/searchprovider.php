<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace OCA\Search_Lucene;

use \OC\Files\Filesystem;
use \OCP\User;
use \OCP\Util;

/**
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class SearchProvider extends \OC_Search_Provider {

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
				$lucene = new Lucene(\OCP\User::getUser());
				//default is 3, 0 needed to keep current search behaviour
				//Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0); 
				
				//$term  = new Zend_Search_Lucene_Index_Term($query);
				//$query = new Zend_Search_Lucene_Search_Query_Term($term);
				
				$hits = $lucene->find($query);

				//limit results. we cant show more than ~30 anyway. TODO use paging later
				for ($i = 0; $i < 30 && $i < count($hits); $i++) {
					$results[] = self::asOCSearchResult($hits[$i]);
				}

			} catch ( Exception $e ) {
				Util::writeLog(
					'search_lucene',
					$e->getMessage().' Trace:\n'.$e->getTraceAsString(),
					Util::ERROR
				);
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
	private static function asOCSearchResult(\Zend_Search_Lucene_Search_QueryHit $hit) {

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
				$url = Util::linkTo('files', 'index.php') . '?dir='.$hit->path;
				break;
			default:
				$url = \OC::getRouter()->generate('download', array('file'=>$hit->path));
		}
		
		return new \OC_Search_Result(
			basename($hit->path),
			dirname($hit->path)
				. ', ' . \OCP\Util::humanFileSize($hit->size)
				. ', Score: ' . number_format($hit->score, 2),
			$url,
			$type,
			dirname($hit->path)
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