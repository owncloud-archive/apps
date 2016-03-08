<?php

/**
 * ownCloud - Persona plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012-2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\User_persona;

\OCP\User::checkAdminUser();

\OCP\Util::addScript(App::APP_ID, 'settings');

$tmpl = new \OCP\Template(App::APP_ID, 'settings');
$tmpl->assign('allPolicies', Policy::getAllPolicies());
$tmpl->assign('currentPolicy', Policy::getSystemPolicy());

return $tmpl->fetchPage();