<?php
// Init owncloud
require_once('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_svgedit');
echo "foo";
if(isset($_GET['file']) and OC_Filesystem::is_writeable($path = $_GET['file'])) {
    $filecontents = OC_Filesystem::file_get_contents($path);
} else {
    $filecontents = "";
}
    
$tmpl = new OC_TEMPLATE( "files_svgedit", "editor", "user" );
$tmpl->assign('fileContents', json_encode($filecontents));
$tmpl->printPage();
?>
