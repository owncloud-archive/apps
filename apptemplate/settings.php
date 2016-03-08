<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "apptemplate", "admin" );

$tmpl = new OCP\Template( 'apptemplate', 'settings');

$tmpl->assign('url', OCP\Config::getSystemValue( "somesetting", '' ));

return $tmpl->fetchPage();
