<?php
$params = array('xmppAdminUser', 'xmppAdminPasswd', 'xmppBOSHURL', 'xmppDefaultDomain');

if ($_POST) {
	foreach ($params as $param) {
		if (isset($_POST[$param])) {
			OCP\Config::setAppValue('xmpp', $param, $_POST[$param]);
		}
	}
}

// fill template
$tmpl = new OCP\Template( 'xmpp', 'adminSettings');
foreach($params as $param){
                $value = OCP\Config::getAppValue('xmpp', $param,'');
                $tmpl->assign($param, $value);
}
return $tmpl->fetchPage();
?>
