<?php
OCP\User::checkAdminUser();
OCP\Util::addscript("external", "admin" );

$tmpl = new OCP\Template( 'external', 'admin');

return $tmpl->fetchPage();
