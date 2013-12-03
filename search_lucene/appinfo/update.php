<?php

$currentVersion=OC_Appconfig::getValue('search_lucene', 'installed_version');
if (version_compare($currentVersion, '0.5.0', '<')) {
	//force reindexing of files
	$stmt = OCP\DB::prepare('DELETE FROM `*PREFIX*lucene_status` WHERE 1=1');
	$stmt->execute();
	//clear old background jobs
	$stmt = OCP\DB::prepare('DELETE FROM `*PREFIX*queuedtasks` WHERE `app`=?');
	$stmt->execute(array('search_lucene'));
}

if (version_compare($currentVersion, '0.5.2', '<')) {
	//delete duplicate entries and rescan them
	$stmt = OCP\DB::prepare('
		DELETE FROM `*PREFIX*lucene_status`
		WHERE `fileid` IN (
			SELECT `fileid`
			FROM `*PREFIX*lucene_status`
			GROUP BY `fileid`
			HAVING count(`status`) > 1
		)
	');
	$stmt->execute();
}