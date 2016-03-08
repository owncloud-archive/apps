<?php
OC::$CLASSPATH['OC_USER_VD'] = 'user_vd/lib/vd.php';
OC::$CLASSPATH['OC_USER_VD_DOMAIN'] = 'user_vd/lib/domains.php';

OCP\App::registerAdmin('user_vd','adminSettings');

if(OCP\Config::getAppValue('user_vd','forceCreateUsers')){
	OCP\Util::connectHook('OC_User','pre_createUser','OC_USER_VD','deleteBackends');
}

if(OCP\Config::getAppValue('user_vd','disableBackends')){
	OC_User::clearBackends();
}

OC_User::useBackend('VD');
?>
