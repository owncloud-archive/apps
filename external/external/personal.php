<?php 
/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

if(OCP\Config::getAppValue('external', 'allowUsers') == 'true'){
	OCP\User::checkLoggedIn();

	OCP\Util::addscript("external", "addsites" );

	$tmpl = new OCP\Template( 'external', 'personal');

	return $tmpl->fetchPage();
}