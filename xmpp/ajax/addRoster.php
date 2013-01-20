<?php

$x=new OC_xmpp_login(OCP\Config::getAppValue('xmpp', 'xmppAdminUser',''),OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''),OCP\Config::getAppValue('xmpp', 'xmppAdminPasswd',''),OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));
$nx=$x->doLogin(OC_USER::getUser().'@'.OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''));
$nx->addRoster($_POST['jid'],$_POST['name']);
$x->logout();
$nx->logout();
?>
