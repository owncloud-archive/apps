<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_openid_provider');
OCP\JSON::callCheck();
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/../3rdparty');

$url = $_POST['url'];

$storage = new OC_OpenIdProviderStorage();
$storage->addSite('/?'.OCP\User::getUser(), $url, null);
OCP\JSON::success();
