<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('search_lucene');

function index_storage(&$files, $storageid) {
	
	$query = \OC_DB::prepare('SELECT `*PREFIX*filecache`.`fileid`'
							.' FROM `*PREFIX*filecache`'
							.' LEFT JOIN `*PREFIX*lucene_status`'
							.' ON `*PREFIX*filecache`.`fileid` = `*PREFIX*lucene_status`.`fileid`'
							.' WHERE `storage` = ?'
							.' AND `status` is null OR `status` = ?');
	$result = $query->execute(array($storageid,'N'));
	if (!$result) {
		OC_JSON::error(array('message'=>'Could not fetch file id\'s.'));
		return false;
	}
	while ($row = $result->fetchRow()) {
		$files[] = $row['fileid'];
	}
	return true;
}

//fetch list of files to scan
// = files in cache but not in lucene_status table or noy yet indexed

//FROM `*PREFIX*permissions` WHERE `fileid` = ? AND `user` = ?


$absoluteRoot = \OC\Files\Filesystem::getView()->getAbsolutePath('/');

// index local files of the user
$mountPoint = \OC\Files\Filesystem::getMountPoint($absoluteRoot);
$files = array();

//resolve cache id
$storage = \OC\Files\Filesystem::getStorage($mountPoint);
$cache = $storage->getCache('/');
$id = $cache->getNumericStorageId(); // TODO is the id unique over all mount points and users?

$result = index_storage($files, $id);
	
/* FIXME also index shared files and other mount points

// index other mount points
$mountPoints = \OC\Files\Filesystem::getMountPoints($absoluteRoot);

foreach ($mountPoints as $mountPoint) {
	
	//resolve cache id
	$storage = \OC\Files\Filesystem::getStorage($mountPoint);
	$cache = $storage->getCache('/');
	$id = $cache->getNumericStorageId(); // TODO is the id unique over all mount points and users?

	index_storage($files, $id);
}
*/

if ($result) {
	OCP\JSON::success(array('files'=>$files));
} else {
	OCP\JSON::error(array('message'=>'Erroro indexing files'));
}