<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "open_web_apps", "admin" );

$tmpl = new OCP\Template( 'open_web_apps', 'settings');

$tmpl->assign('storage_origin_value', OCP\Config::getAppValue('open_web_apps',  "storage_origin", '' ));

return $tmpl->fetchPage();
