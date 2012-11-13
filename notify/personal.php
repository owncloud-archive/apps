<?php
OCP\Util::addScript('notify', 'personalSettings');
OCP\Util::addStyle('notify', 'personalSettings');
$tmpl = new OCP\Template('notify', 'personalSettings');
$notificationClasses = OC_Notify::getClasses();
$classes = array();
foreach($notificationClasses as $c) {
	//$l = OC_L10N::get($c['appid'], null); //TODO: put this into notify.php
	$appInfo = OCP\App::getAppInfo($c['appid']);
	$classes[$c['id']] = array(
		'blocked' => $c['blocked'],
		'summary' => $c['summary'], //FIXME: translate this
		'name' => $c['name'],
		'appName' => $appInfo['name'],
		'appid' => $c['appid']
	);
}
$tmpl->assign('classes', $classes);
return $tmpl->fetchPage();
