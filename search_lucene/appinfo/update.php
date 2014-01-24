<?php

$currentVersion=OCP\Config::getAppValue('search_lucene', 'installed_version');

if (version_compare($currentVersion, '0.5.0', '<')) {
	//clear old background jobs
	$stmt = OCP\DB::prepare('DELETE FROM `*PREFIX*queuedtasks` WHERE `app`=?');
	$stmt->execute(array('search_lucene'));
}

if (version_compare($currentVersion, '0.6.0', '<')) {
	//force reindexing of files
	$stmt = OCP\DB::prepare('DELETE FROM `*PREFIX*lucene_status`');
	$stmt->execute();
	//FIXME wipe index on disk because primary key changed
	
}