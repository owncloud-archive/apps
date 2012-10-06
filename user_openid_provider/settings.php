<?php
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/3rdparty');

OCP\Util::addScript( 'user_openid_provider', 'settings' );
OCP\Util::addStyle( 'user_openid_provider', 'settings' );

$storage = new OC_OpenIdProviderStorage();
$trusted_sites = $storage->getTrustedSites('/?'.OCP\User::getUser());

if (empty($trusted_sites)) {
	return;
}

$tmpl = new OCP\Template( 'user_openid_provider', 'settings');
$tmpl->assign('trusted_sites', $trusted_sites);
return $tmpl->fetchPage();
