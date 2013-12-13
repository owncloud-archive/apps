<?php

$currentVersion=OC_Appconfig::getValue('search_lucene', 'installed_version');

if (version_compare($currentVersion, '0.5.2', '<')) {
	
	//delete duplicate id entries
	
	$dbtype = \OCP\Config::getSystemValue('dbtype', 'sqlite3');
	
	if ($dbtype === 'mysql') {
		// fix MySQL ERROR 1093 (HY000), see http://stackoverflow.com/a/12969601
		$sql = 'DELETE FROM `*PREFIX*lucene_status`
				WHERE `fileid` IN (
					SELECT `fileid` FROM (
						SELECT `fileid`
						FROM `*PREFIX*lucene_status`
						GROUP BY `fileid`
						HAVING count(`status`) > 1
					) AS `mysqlerr1093hack`
				)
			';
	} else {
		$sql = 'DELETE FROM `*PREFIX*lucene_status`
				WHERE `fileid` IN (
					SELECT `fileid`
					FROM `*PREFIX*lucene_status`
					GROUP BY `fileid`
					HAVING count(`status`) > 1
				)
			';
	}
	
	$stmt = OCP\DB::prepare($sql);
	$stmt->execute();
}