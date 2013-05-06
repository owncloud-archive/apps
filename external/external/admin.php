<?php
/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
OCP\User::checkAdminUser();

OCP\Util::addscript("external", "admin" );
OCP\Util::addscript("external", "addsites" );

$tmpl = new OCP\Template( 'external', 'admin');

return $tmpl->fetchPage();
