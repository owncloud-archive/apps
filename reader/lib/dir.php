<?php
	function explore($current_dir,$sub_dirs,$num_of_results) {
		$return = array();
		// Search for pdfs in sub directories.
		foreach ($sub_dirs as $dir) {
			$pdfs = \OC_FileCache::searchByMime('application', 'pdf', '/'.\OCP\USER::getUser().'/files'.$current_dir.$dir.'/');
			sort($pdfs);
			$max_count = min(count($pdfs),$num_of_results);
			$thumbs = array();
			for ($i = $max_count - 1; $i >= 0; $i--) {
				if (!in_array($pdfs[$i],$thumbs)) 
					$thumbs[] = $pdfs[$i];
			}
			$return[] = array($dir,$thumbs); 
		}
		return $return; 
	}
	
?>
