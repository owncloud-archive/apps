<?php
OCP\User::checkAdminUser();

OCP\Util::addscript("external", "admin" );
OCP\Util::addscript("external", "addsites" );

$tmpl = new OCP\Template( 'external', 'admin');

return $tmpl->fetchPage();
