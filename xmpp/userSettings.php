<?php
$params = array('autoroster');

if ($_POST) {
	foreach ($params as $param) {
		if (isset($_POST[$param])) {
			if($param==='autoroster'){
				OCP\Config::setUserValue(OCP\User::getUser(),'xmpp','autoroster',true);
			}else{
				OCP\Config::setUserValue(OCP\User::getUser(),'xmpp',$param,$_POST[$param]);
			}
		}else{
			if($param==='autoroster'){
				OCP\Config::setUserValue(OCP\User::getUser(),'xmpp','autoroster',false);
			}
		}
	}
}

// fill template
$tmpl = new OCP\Template( 'xmpp', 'userSettings');
foreach($params as $param){
                $value = OCP\Config::getUserValue(OCP\User::getUser(),'xmpp',$param);
                $tmpl->assign($param, $value);
}
return $tmpl->fetchPage();
?>
