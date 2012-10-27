<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "user_oauth", "admin" );

$tmpl = new OCP\Template( 'user_oauth', 'settings');

$tmpl->assign('url', OCP\Config::getSystemValue( "somesetting", '' ));

return $tmpl->fetchPage();
