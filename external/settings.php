<?php

OCP\User::checkAdminUser();
OCP\JSON::checkAppEnabled('external');

OCP\Util::addscript( "external", "admin" );

$tmpl = new OCP\Template( 'external', 'settings');

$images = glob(\OC_App::getAppPath('external') . '/img/*.*');
$theme = \OC::$server->getThemeService()->getTheme();
$themeDir = $theme->getBaseDirectory() . '/' . $theme->getDirectory();
$imagesDir = "$themeDir/apps/external/img/";
if (is_dir($imagesDir)) {
	$theme_images = glob("$imagesDir/*.*");
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

return $tmpl->fetchPage();
