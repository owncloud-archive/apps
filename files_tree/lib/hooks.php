<?php

class OC_FilesTree_Hooks{
	public static function ClearCache($parameters) {
		$cache = new OC_Cache_File();
		$cache->remove('files_tree_cache');
	}
}