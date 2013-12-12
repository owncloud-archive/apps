<?php

$currentVersion=OC_Appconfig::getValue('search_lucene', 'installed_version');

if (version_compare($currentVersion, '0.5.2', '<')) {
	//delete duplicate id entries
	$stmt = OCP\DB::prepare('
		DELETE FROM `*PREFIX*lucene_status`
		WHERE `fileid` IN (
			SELECT `fileid`
			FROM (
				SELECT `fileid`
				FROM `*PREFIX*lucene_status`
				GROUP BY `fileid`
				HAVING count(`status`) > 1
			) AS `mysqlerr1093hack`
		)
	');
	$stmt->execute();
}