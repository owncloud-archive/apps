<?php
$params = array('autoroster');

if ($_POST) {
	foreach ($params as $param) {
		if (isset($_POST[$param])) {
			if($param==='autoroster'){
				OC_Preferences::setValue(OC_USER::getUser(),'xmpp','autoroster',true);
			}else{
				OC_Preferences::setValue(OC_USER::getUser(),'xmpp',$param,$_POST[$param]);
			}
		}else{
			if($param==='autoroster'){
				OC_Preferences::setValue(OC_USER::getUser(),'xmpp','autoroster',false);
			}
		}
	}
}

// fill template
$tmpl = new OCP\Template( 'xmpp', 'userSettings');
foreach($params as $param){
                $value = OC_Preferences::getValue(OC_USER::getUser(),'xmpp',$param);
                $tmpl->assign($param, $value);
}
return $tmpl->fetchPage();
?>
