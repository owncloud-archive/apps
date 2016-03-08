<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "user_oauth", "admin" );

$tmpl = new OCP\Template( 'user_oauth', 'settings');

$tmpl->assign('introspectionEndpoint', OCP\Config::getSystemValue( "introspectionEndpoint", 'https://frko.surfnetlabs.nl/workshop/php-oauth/introspect.php' ));

return $tmpl->fetchPage();
