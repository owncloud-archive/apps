<?php

//check if curl extension installed
if (!in_array ('curl', get_loaded_extensions())) {
	return;
}
/*
$userName='';
if(strpos(OCP\Util::getRequestUri(),'?') and !strpos(OCP\Util::getRequestUri(),'=')) {
	if(strpos(OCP\Util::getRequestUri(),'/?') !== false) {
		$userName=substr(OCP\Util::getRequestUri(),strpos(OCP\Util::getRequestUri(),'/?')+2);
	}elseif(strpos(OCP\Util::getRequestUri(),'.php?') !== false) {
		$userName=substr(OCP\Util::getRequestUri(),strpos(OCP\Util::getRequestUri(),'.php?')+5);
	}
}

OCP\Util::addHeader('link',array('rel'=>'openid.server', 'href'=>OCP\Util::linkToAbsolute( "user_openid", "user.php" ).'/'.$userName));
OCP\Util::addHeader('link',array('rel'=>'openid.delegate', 'href'=>OCP\Util::linkToAbsolute( "user_openid", "user.php" ).'/'.$userName));
 *
 */

OCP\App::registerPersonal('user_openid','settings');

require_once 'user_openid/user_openid.php';

//active the openid backend
OC_User::useBackend('openid');

//check for results from openid requests
if(isset($_GET['openid_mode']) and $_GET['openid_mode'] == 'id_res') {
	OCP\Util::writeLog('user_openid','openid returned',OCP\Util::DEBUG);
	$openid = new SimpleOpenID();
	$openid->SetIdentity($_GET['openid_identity']);
	$openid_validation_result = $openid->ValidateWithServer();
	if ($openid_validation_result == true) {         // OK HERE KEY IS VALID
		OCP\Util::writeLog('user_openid','auth sucessfull',OCP\Util::DEBUG);
		$identity=$openid->GetIdentity();
		OCP\Util::writeLog('user_openid','auth as '.$identity,OCP\Util::DEBUG);
		$user=OC_USER_OPENID::findUserForIdentity($identity);
		OCP\Util::writeLog('user_openid','user is '.$user,OCP\Util::DEBUG);
		if($user) {
			$_SESSION['user_id']=$user;
			header("Location: ".OCP\Util::linkToAbsolute('', 'index.php'));
			exit();
		}
	}else if($openid->IsError() == true) {            // ON THE WAY, WE GOT SOME ERROR
		$error = $openid->GetError();
		OCP\Util::writeLog('user_openid','ERROR CODE: '. $error['code'],OCP\Util::ERROR);
		OCP\Util::writeLog('user_openid','ERROR DESCRIPTION: '. $error['description'],OCP\Util::ERROR);
	}else{                                            // Signature Verification Failed
		OCP\Util::writeLog('user_openid','INVALID AUTHORIZATION',OCP\Util::ERROR);
	}
}else if (isset($_GET['openid_mode']) and $_GET['openid_mode'] == 'cancel') { // User Canceled your Request
	OCP\Util::writeLog('user_openid','USER CANCELED REQUEST',OCP\Util::DEBUG);
	return false;
}
