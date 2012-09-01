<?php

/**
 * ownCloud - ownpad_lite plugin
 * 
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 * 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

// Check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('ownpad_lite');

OCP\Util::addStyle('ownpad_lite', 'style');
OCP\Util::addScript('ownpad_lite', 'etherpad');
OCP\App::setActiveNavigationEntry('ownpad_lite_index');


$tmpl = new OCP\Template( "ownpad_lite", "index", "user" );
$tmpl->assign('id', $id, false);
$tmpl->printPage();
