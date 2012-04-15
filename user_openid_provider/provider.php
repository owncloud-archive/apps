<?php
require_once('../../lib/base.php');
require_once('Zend/OpenId/Provider.php');
OC_Util::checkAppEnabled('user_openid_provider');

$session = new OC_OpenIdProviderUserSession();
$storage = new OC_OpenIdProviderStorage();
$loginPage = OC_Helper::linkToAbsolute( '', 'index.php' ).'?redirect_url='.urlencode($_SERVER["REQUEST_URI"]);
$trustPage = OC_Helper::linkToAbsolute('user_openid_provider', 'trust.php');

$server = new Zend_OpenId_Provider($loginPage, $trustPage, $session, $storage);


$ret = $server->handle();
if (is_string($ret)) {
	echo $ret;
} else if ($ret !== true) {
	header('HTTP/1.0 403 Forbidden');
	echo 'Forbidden';
}