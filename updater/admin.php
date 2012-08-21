<?php

/**
 * ownCloud - Updater plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA_Updater;

\OCP\User::checkAdminUser();
$tmpl = new \OCP\Template(App::APP_ID, 'admin');
return $tmpl->fetchPage();