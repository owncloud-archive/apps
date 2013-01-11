<?php

$xmpplogin=new OC_xmpp_login(OCP\Config::getAppValue('xmpp', 'xmppAdminUser',''),OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''),OCP\Config::getAppValue('xmpp', 'xmppAdminPasswd',''),OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));
$xmpplogin->doLogin();
$jid=OCP\User::getUser().OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain','');
$passwd=$xmpplogin->getUserPasswd($jid);
$params=array('uid'=>OCP\User::getUser(),'password'=>$passwd);
$xmpplogin->logout();

OC_User_xmpp_Hooks::createXmppSession($params);

?>
