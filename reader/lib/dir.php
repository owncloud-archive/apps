<?php
	function explore($current_dir,$sub_dirs) {
		$return = array();
		// Search for pdfs in sub directories.
		foreach ($sub_dirs as $dir) {
			$pdfs = \OC_FileCache::searchByMime('application', 'pdf', '/'.\OCP\USER::getUser().'/files'.$current_dir.$dir.'/');
			sort($pdfs);
			$thumbs = array();
			$count = 1;
			foreach ($pdfs as $pdf) {
				// We need only 3 pdf pages to create thumbnails for folders. 
				if ($count <= 3) {
					// Store the urls in an array.
					$thumbs[] = $pdf;
					$count++;
				} 
			}
			// Return the directory and contained pdfs(any 3).
			$return[] = array($dir,$thumbs); 
		}
		return $return; 
	}
?>
