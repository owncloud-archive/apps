<?php

// in case there are private configurations in the users home -> use them
$privateConfigFile = $_SERVER['HOME'] . '/owncloud-extfs-test-config.php';
if (file_exists($privateConfigFile)) {
	$config = include($privateConfigFile);
	return $config;
}

// this is now more a template now for your private configurations
return array(
	'irods' => array (
		'run'=>false,
		'host'=>'',
		'port'=>'',
		'use_logon_credentials'=>false,
		'zone'=>'',
		'user'=>'',
		'password'=>'',
		'auth_mode'=>'',
		'root'=>''
	)
);
