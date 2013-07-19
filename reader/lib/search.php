<?php
class OC_ReaderSearchProvider extends OC_Search_Provider{
	function search($query){
		$files=OC_FileCache::search($query,true);
		$results=array();
		foreach($files as $fileData){
			$file=$fileData['path'];
			$mime=$fileData['mimetype'];
			if($mime=='application/pdf'){
				$results[]=new OC_Search_Result(basename($file),'',OCP\Util::linkTo( 'reader', 'results.php' ).'?file='.$file,'eBook');
			}
		}
		return $results;
	}
}
?>
