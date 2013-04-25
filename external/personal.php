<?php
OCP\User::checkLoggedIn();
OCP\Util::addscript("external", "personal" );

$tmpl = new OCP\Template( 'external', 'personal');

return $tmpl->fetchPage();
