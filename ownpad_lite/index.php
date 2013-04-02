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

namespace OCA\ownpad_lite;

// Check if we are a user
\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled(App::APP_ID);

\OCP\Util::addStyle(App::APP_ID, 'style');
\OCP\Util::addScript(App::APP_ID, 'etherpad');
\OCP\App::setActiveNavigationEntry('ownpad_lite_index');



$tmpl = new \OCP\Template(App::APP_ID, "index", "user" );

$tmpl->assign(App::CONFIG_ETHERPAD_URL, App::getServiceUrl());
$tmpl->assign(App::CONFIG_USERNAME, App::getUsername());

$tmpl->printPage();
