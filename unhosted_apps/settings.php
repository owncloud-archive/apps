<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "unhosted_apps", "admin" );

$tmpl = new OCP\Template( 'unhosted_apps', 'settings');

$tmpl->assign('storage_origin_value', OCP\Config::getAppValue( "storage_origin", '' ));

return $tmpl->fetchPage();
