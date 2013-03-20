<?php
// Clear cache when creating/deleting or renaming a folder
class OC_FilesTree_Hooks{
	public static function ClearCache($parameters) {
		$cache = new OC_Cache_File();
		$cache->remove('files_tree_cache');
	}
}