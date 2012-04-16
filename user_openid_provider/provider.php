<?php
require_once('../../lib/base.php');
require_once('Zend/OpenId/Provider.php');
OC_Util::checkAppEnabled('user_openid_provider');

$session = new OC_OpenIdProviderUserSession();
$storage = new OC_OpenIdProviderStorage();
$server = new Zend_OpenId_Provider(null, null, $session, $storage);

if (OC_User::isLoggedIn() and !$session->getLoggedInUser()) {
	$session->setLoggedInUser(OC_Helper::linkToAbsolute('user_openid_provider', 'provider.php').'/'.OC_User::getUser());
}

if (isset($_GET['openid_action']) and $_GET['openid_action']=='login') {
	unset($_GET['openid_action']);
	$params = '?';
	foreach($_GET as $key => $value) {
		$params .= '&' . $key . '=' . $value;
	}
	$loginPage = OC_Helper::linkToAbsolute( '', 'index.php' ).'?redirect_url='
		.urlencode(OC_Helper::linkToAbsolute('user_openid_provider', 'provider.php')
				. $params);
	header('Location: '.$loginPage );
} else if (isset($_GET['openid_action']) and $_GET['openid_action'] == 'trust') {
	OC_Util::checkLoggedIn();
	if (isset($_POST['allow'])) {
		$server->respondToConsumer($_GET);
	} else {
		$tmpl = new OC_Template( 'user_openid_provider', 'trust', 'user');
		$tmpl->assign('site', $server->getSiteRoot($_GET));
		$tmpl->assign('openid', $server->getLoggedInUser());
		$tmpl->printPage();
	}
} else {

	$ret = $server->handle();
	if (is_string($ret)) {
		echo $ret;
	} else if ($ret !== true) {
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden';
	}
}