<?php
if(OCP\Config::getAppValue('external', 'allowUsers') == 'true'){
	OCP\User::checkLoggedIn();

	OCP\Util::addscript("external", "addsites" );

	$tmpl = new OCP\Template( 'external', 'personal');

	return $tmpl->fetchPage();
}