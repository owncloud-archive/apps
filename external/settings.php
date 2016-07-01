<?php

OCP\User::checkAdminUser();
OCP\JSON::checkAppEnabled('external');

OCP\Util::addscript( "external", "admin" );

$tmpl = new OCP\Template( 'external', 'settings');

$images = glob(\OC_App::getAppPath('external') . '/img/*.*');
$theme = \OC::$server->getSystemConfig()->getValue('theme', '');
if (file_exists(\OC::$SERVERROOT . "/themes/$theme/apps/external/img/")) {
	$images = array_merge($images, glob(\OC::$SERVERROOT . "/themes/$theme/apps/external/img/*.*"));
}

$tmpl->assign('images', $images);

return $tmpl->fetchPage();
