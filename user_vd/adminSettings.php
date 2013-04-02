<?php
OCP\Util::addscript('user_vd', 'adminSettings');
$paramsCheck = array( 'forceCreateUsers', 'disableBackends');
if ($_POST) {
	if(array_key_exists('domain',$_POST) && array_key_exists('fqdn',$_POST)){
		foreach($_POST['domain'] as $index => $domain){
			$fqdn=$_POST['fqdn'][$index];
			if($domain!='' OR $fqdn!=''){
				$domains[$domain]=$fqdn;
			}
		}
		OC_USER_VD_DOMAIN::saveDomains($domains);
	}
	foreach($paramsCheck as $param){
		if(isset($_POST[$param])){
			OCP\Config::setAppValue('user_vd',$param,true);
		}else{
			OCP\Config::setAppValue('user_vd',$param,false);
		}
	}
}


$tmpl = new OCP\Template('user_vd', 'adminSettings');
$tmpl->assign('domains',OC_USER_VD_DOMAIN::getDomains(true));
foreach($paramsCheck as $param){
	$tmpl->assign($param,OCP\Config::getAppValue('user_vd',$param));
}
return $tmpl->fetchPage();
