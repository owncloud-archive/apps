<?php
OCP\User::checkLoggedIn();

if(OCP\Config::getAppValue('external', 'allowUsers') == 'true'){

	OCP\Util::addscript("external", "personal" );

	$tmpl = new OCP\Template( 'external', 'personal');

	return $tmpl->fetchPage();
}