<?php

OCP\App::checkAppEnabled('user_openid_provider');
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/3rdparty');
require_once 'Zend/OpenId/Provider.php';

if (!isset($_REQUEST['openid_mode'])) {
	OCP\Template::printGuestPage('user_openid_provider', 'main');
	die;
}

$session = new OC_OpenIdProviderUserSession();
$storage = new OC_OpenIdProviderStorage();
$server = new Zend_OpenId_Provider(null, null, $session, $storage);

if (OCP\User::isLoggedIn() and !$session->getLoggedInUser()) {
	$session->setLoggedInUser(OCP\Util::linkToAbsolute('', '?').OCP\User::getUser());
}

if (isset($_GET['openid_action']) and $_GET['openid_action']=='login') {
	unset($_GET['openid_action']);
	$params = '?'.Zend_OpenId::paramsToQuery($_GET);
	$next = OCP\Util::linkToRemote('openid_provider') . $params;
	$loginPage = OCP\Util::linkToAbsolute( '', 'index.php' ).'?redirect_url='
		.urlencode($next);
	header('Location: '.$loginPage );
} else if (isset($_GET['openid_action']) and $_GET['openid_action'] == 'trust') {
	OCP\User::checkLoggedIn();
	if (isset($_POST['allow'])) {
		if (isset($_POST['forever'])) {
			$server->allowSite($server->getSiteRoot($_GET));
		}
		$server->respondToConsumer($_GET);
	} else if (isset($_POST['deny'])) {
		if (isset($_POST['forever'])) {
			$server->denySite($server->getSiteRoot($_GET));
		}
		Zend_OpenId::redirect($_GET['openid_return_to'],
				array('openid.mode'=>'cancel'));
	} else {
		$tmpl = new OCP\Template( 'user_openid_provider', 'trust', 'user');
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
