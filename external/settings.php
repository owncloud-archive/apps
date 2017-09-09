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

// _blank opens link in a new window/tab
// _top replaces the current owncloud window with link
// _self opens link in the ownCloud iframe
$tmpl->assign('targets', array('_blank', '_top', '_self'));
$tmpl->assign('targets_desc', array('New Window', 'Replace Current Window', 'Inside OwnCloud Frame'));


return $tmpl->fetchPage();
