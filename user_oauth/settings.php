<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "user_oauth", "admin" );

$tmpl = new OCP\Template( 'user_oauth', 'settings');

$tmpl->assign('tokenInfoEndpoint', OCP\Config::getSystemValue( "tokenInfoEndpoint", 'https://www.googleapis.com/oauth2/v1/tokeninfo' ));

return $tmpl->fetchPage();
