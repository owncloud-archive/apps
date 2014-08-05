<?php

OCP\User::checkAdminUser();
OCP\JSON::checkAppEnabled('external');

OCP\Util::addscript( "external", "admin" );

$tmpl = new OCP\Template( 'external', 'settings');

$tmpl->assign('images', glob(\OC_App::getAppPath('external') . '/img/*.*'));

return $tmpl->fetchPage();
