<?php

$l=OCP\Util::getL10N('settings');

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('user_openid');

// Get data
if( isset( $_POST['identity'] ) ) {
	$identity=$_POST['identity'];
	OCP\Config::setUserValue(OCP\User::getUser(), 'user_openid', 'identity', $identity);
	OCP\JSON::success(array("data" => array( "message" => $l->t("OpenID Changed") )));
}else{
	OCP\JSON::error(array("data" => array( "message" => $l->t("Invalid request") )));
}
