<?php

OC_Util::checkAdminUser();

OC_Util::addScript( "kolab", "admin" );

$tmpl = new OC_Template( 'kolab', 'settings');

$tmpl->assign('url',OC_Config::getValue( "kolab-url", '' ));

return $tmpl->fetchPage();

?>
