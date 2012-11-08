<?php
/**
 * Copyright (c) 2011, Frank Karlitschek <karlitschek@kde.org>
 * Copyright (c) 2012, Florian Hülsmann <fh@cbix.de>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

OCP\User::checkAdminUser();
OCP\JSON::callCheck();

OCP\Config::setAppValue('unhosted_apps',  'storage_origin', $_POST['storage_origin'] );

echo 'true';
