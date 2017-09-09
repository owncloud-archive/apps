<?php

OCP\User::checkAdminUser();
OCP\JSON::checkAppEnabled('external');

OCP\Util::addscript( "external", "admin" );

$tmpl = new OCP\Template( 'external', 'settings');

$images = glob(\OC_App::getAppPath('external') . '/img/*.*');
$theme = \OC::$server->getSystemConfig()->getValue('theme', '');
if (file_exists(\OC::$SERVERROOT . "/themes/$theme/apps/external/img/")) {
	$theme_images = glob(\OC::$SERVERROOT . "/themes/$theme/apps/external/img/*.*");
	foreach ($theme_images as $theme_image) {
		$unique_flag = true;
		foreach ($images as $image) {
			if (basename($image) == basename($theme_image)) {
				$unique_flag = false;
				break;
			}
		}
		if ($unique_flag) {
			$images[] = $theme_image;
		}
	}
}

$tmpl->assign('images', $images);

$targets = array('_blank', '_top', '_self');
$targets_desc = array('New Window', 'Replace Current Window', 'Inside OwnCloud Frame');
$tmpl->assign('targets', $targets);
$tmpl->assign('targets_desc', $targets_desc);


return $tmpl->fetchPage();
