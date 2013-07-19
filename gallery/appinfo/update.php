<?php

$currentVersion=OCP\Config::getAppValue('gallery', 'installed_version');
if (version_compare($currentVersion, '0.5.0', '<')) {
	$stmt = OCP\DB::prepare('DROP TABLE IF EXISTS `*PREFIX*gallery_photos`');
	$stmt->execute();
	$stmt = OCP\DB::prepare('DROP TABLE IF EXISTS `*PREFIX*gallery_albums`');
	$stmt->execute();

	\OC_DB::createDbFromStructure(OC_App::getAppPath($appid).'/appinfo/database.xml');
}
