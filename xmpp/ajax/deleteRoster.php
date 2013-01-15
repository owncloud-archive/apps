<?php

$x=new OC_xmpp_login(OCP\Config::getAppValue('xmpp', 'xmppAdminUser',''),OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''),OCP\Config::getAppValue('xmpp', 'xmppAdminPasswd',''),OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));
$nx=$x->doLogin(OC_USER::getUser().'@'.OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''));
$nx->deleteRoster($_POST['jid']);
$x->logout();
$nx->logout();
?>
