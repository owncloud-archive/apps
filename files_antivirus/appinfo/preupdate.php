<?php

$installedVersion=OCP\Config::getAppValue('files_antivirus', 'installed_version');
if (version_compare($installedVersion, '0.6', '<')) {
	$query = OC_DB::prepare( 'SELECT COUNT(*) AS `count`, `fileid` FROM `*PREFIX*files_antivirus` GROUP BY `fileid` HAVING COUNT(*) > 1' );
	$result = $query->execute();
	while( $row = $result->fetchRow()) {
		$deleteQuery = OC_DB::prepare('DELETE FROM `*PREFIX*files_antivirus` WHERE `fileid` = ? ORDER BY `check_time` ASC', $row['count']-1);
		$deleteQuery->execute(array($row['fileid']));
	}
}
