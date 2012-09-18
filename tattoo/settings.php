<?php

OC_Util::addStyle('tattoo', 'settings');

// die($_POST['tattooWallpaper']);
if(isset($_POST['tattooSetWallpaper']) && isset($_POST['tattooWallpaper'])) {
	OC_Preferences::setValue(OC_User::getUser(),'tattoo','wallpaper',$_POST['tattooWallpaper']);
	OC_Preferences::setValue(OC_User::getUser(),'tattoo','lastModified',gmdate('D, d M Y H:i:s') . ' GMT');
}
$wallpaper=OC_Preferences::getValue(OC_User::getUser(),'tattoo','wallpaper','none');

$tmpl = new OC_Template( 'tattoo', 'settings');
$tmpl->assign('tattooSelectedWallpaper',$wallpaper);

return $tmpl->fetchPage();
