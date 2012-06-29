<?php

OCP\User::checkAdminUser();

OCP\Util::addScript( "user_openid_provider", "admin" );

$tmpl = new OCP\Template( 'user_openid_provider', 'settings');

$tmpl->assign('url',OCP\Config::getValue( "somesetting", '' ));

return $tmpl->fetchPage();

?>
