<?php

OC_Util::checkAdminUser();

OC_Util::addScript( "user_openid_provider", "admin" );

$tmpl = new OC_Template( 'user_openid_provider', 'settings');

$tmpl->assign('url',OC_Config::getValue( "somesetting", '' ));

return $tmpl->fetchPage();

?>
