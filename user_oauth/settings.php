<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "user_oauth", "admin" );

$tmpl = new OCP\Template( 'user_oauth', 'settings');

$tmpl->assign('tokenInfoEndpoint', OCP\Config::getSystemValue( "tokenInfoEndpoint", 'http://localhost/oauth/php-oauth/tokeninfo.php' ));

return $tmpl->fetchPage();
