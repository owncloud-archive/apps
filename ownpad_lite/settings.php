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

$tmpl = new \OCP\Template(App::APP_ID, 'settings');

$tmpl->assign(App::CONFIG_ETHERPAD_URL, App::getServiceUrl());
$tmpl->assign(App::CONFIG_USERNAME, App::getUsername());

$tmpl->printPage();
