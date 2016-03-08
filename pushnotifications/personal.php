<?php

/**
 * ownCloud - push notifications app
 *
 * @author Frank Karlitschek
 * @copyright 2014 Frank Karlitschek frank@owncloud.org
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\pushnotifications;

// load js and css
\OCP\Util::addScript('pushnotifications', 'personal');
\OCP\Util::addStyle('pushnotifications', 'personal');

// show template
$tmpl = new \OCP\Template('pushnotifications', 'personal');
$pushId = \OCP\Config::getUserValue(\OCP\User::getUser(), 'pushnotifications', 'pushid', '');
$appId = \OCP\Config::getSystemValue('pushnotifications_pushover_app', '');
$tmpl->assign('pushId', $pushId);
$tmpl->assign('appId', $appId);

return $tmpl->fetchPage();
