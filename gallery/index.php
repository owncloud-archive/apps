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
OCP\Util::addScript('gallery', 'slideshow');
OCP\Util::addStyle('gallery', 'styles');

$tmpl = new OCP\Template('gallery', 'index', 'user');
$tmpl->printPage();
