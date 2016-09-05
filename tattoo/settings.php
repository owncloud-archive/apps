<?php

OCP\Util::addStyle('tattoo', 'settings');

// die($_POST['tattooWallpaper']);
if(isset($_POST['tattooSetWallpaper']) && isset($_POST['tattooWallpaper'])) {
	OCP\Config::setUserValue(OCP\User::getUser(),'tattoo','wallpaper',$_POST['tattooWallpaper']);
	OCP\Config::setUserValue(OCP\User::getUser(),'tattoo','lastModified',gmdate('D, d M Y H:i:s') . ' GMT');
}
$wallpaper=OCP\Config::getUserValue(OCP\User::getUser(),'tattoo','wallpaper','none');

$tmpl = new OCP\Template( 'tattoo', 'settings');
$tmpl->assign('tattooSelectedWallpaper',$wallpaper);

return $tmpl->fetchPage();
