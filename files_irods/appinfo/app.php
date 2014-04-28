<?php
/**
 * Copyright (c) 2013 Thomas Müller <thomas.mueller@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
$l = \OC_L10N::get('files_irods');

OC::$CLASSPATH['OC_Mount_Config'] = 'files_external/lib/config.php';

// connecting hooks
OCP\Util::connectHook('OC_User', 'post_login', 'OC\Files\Storage\iRODS', 'login');

OC_Mount_Config::registerBackend('\OCA\Files_iRODS\iRODS', array(
	'backend' => 'iRODS',
	'configuration' => array(
		'host' => (string)$l->t('Host'),
		'port' => (string)$l->t('Port'),
		'use_logon_credentials' => '!'.$l->t('Use ownCloud login'),
		'user' => (string)$l->t('Username'),
		'password' => '*'.$l->t('Password'),
		'auth_mode' => (string)$l->t('Authentication Mode'),
		'zone' => (string)$l->t('Zone'),
		'root' => (string)$l->t('Root'))));
