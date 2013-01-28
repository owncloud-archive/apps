<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('gallery');
OCP\App::setActiveNavigationEntry('gallery_index');

OCP\Util::addScript('gallery', 'gallery');
OCP\Util::addStyle('gallery', 'styles');

OCP\Util::addStyle('gallery', 'supersized');
OCP\Util::addStyle('gallery', 'supersized.shutter');
OCP\Util::addScript('gallery', 'bigscreen.min');
OCP\Util::addScript('gallery', 'jquery.easing.min');
OCP\Util::addScript('gallery', 'supersized.3.2.7.min');
OCP\Util::addScript('gallery', 'supersized.shutter.min');

$tmpl = new OCP\Template('gallery', 'index', 'user');
$tmpl->printPage();
