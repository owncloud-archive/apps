<?php

$xmpplogin=new OC_xmpp_login(OCP\Config::getAppValue('xmpp', 'xmppAdminUser',''),'acs.li',OCP\Config::getAppValue('xmpp', 'xmppAdminPasswd',''),OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));
$xmpplogin->doLogin();
$jid=OCP\User::getUser().'@acs.li';
$passwd=$xmpplogin->getUserPasswd($jid);
$params=array('uid'=>OCP\User::getUser(),'password'=>$passwd);

OC_User_xmpp_Hooks::createXmppSession($params);

?>
