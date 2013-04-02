<?php
OCP\Util::addscript( 'files_tree', 'tree');
OCP\Util::addStyle('files_tree', 'files_tree');

OC::$CLASSPATH['OC_FilesTree_Hooks'] = 'apps/files_tree/lib/hooks.php'; 
OC::$CLASSPATH['OC_FilesTree_Explore'] = 'apps/files_tree/ajax/explore.php';  
//General Hooks
OCP\Util::connectHook('OC_Filesystem', 'create', 'OC_FilesTree_Hooks', 'ClearCache');
OCP\Util::connectHook('OC_Filesystem', 'delete', 'OC_FilesTree_Hooks', 'ClearCache');
OCP\Util::connectHook('OC_Filesystem', 'rename', 'OC_FilesTree_Hooks', 'ClearCache');