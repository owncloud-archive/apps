<?php

$x=new OC_xmpp_login(OCP\Config::getAppValue('xmpp', 'xmppAdminUser',''),OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''),OCP\Config::getAppValue('xmpp', 'xmppAdminPasswd',''),OCP\Config::getAppValue('xmpp', 'xmppBOSHURL',''));
$nx=$x->doLogin(OC_USER::getUser().'@'.OCP\Config::getAppValue('xmpp', 'xmppDefaultDomain',''));
$roster=$nx->getRoster();
foreach($roster->iq->query->item as $item){
        $ret[]=(string)$item->attributes()->jid;
}
$x->logout();
$nx->logout();

echo json_encode($ret);
?>

