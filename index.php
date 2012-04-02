<?php
// Init owncloud
require_once('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_svgedit');
if(isset($_GET['file']) and OC_Filesystem::is_writeable($path = $_GET['file'])) {
    $filecontents = OC_Filesystem::file_get_contents($path);
    $filemtime = OC_Filesystem::filemtime($path);
} else {
    $filecontents = "";
    $filemtime = 0;
    $path = "";
}
    
$tmpl = new OC_TEMPLATE( "files_svgedit", "editor", "user" );
$tmpl->assign('fileContents', json_encode($filecontents));
$tmpl->assign('filemTime', $filemtime);
$tmpl->assign('filePath', json_encode($path));
$tmpl->printPage();
?>
