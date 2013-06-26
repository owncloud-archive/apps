<?php
namespace OCA\file_previewer\lib;
class Solr {
	
	//static $solr;
	public function __construct() {
		
	}
	
	public static function getStorageId($query){
		try
		{
			$solr = new \Apache_Solr_Service('localhost', 9997, '/solr/fascinator/');
			$storage_id = '';
			$results = $solr->search($query, 0, 20);
			$base_ids = array();
			if($results)
			{
				foreach ($results->response->docs as $doc) {
					foreach ($doc as $field => $value) {
						if($field === "storage_id"){
							$storage_id = $value;
							break;
						}
					}
				}
				return $storage_id;
			}
		}catch (Exception $e){
			// in production you'd probably log or email this error to an admin
			// and then show a special message to the user but for this example
			// we're going to show the full exception
			die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
		}
	}
}