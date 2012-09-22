<?php
// Init owncloud
require_once '../../lib/base.php';
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_svgedit');
// load required style sheets:
OC_Util::addStyle('files_svgedit', 'ocsvg');
// load required javascripts:
OC_Util::addScript('files_svgedit', 'svg-edit/embedapi');
OC_Util::addScript('files_svgedit', 'ocsvgEditor');
OC_Util::addScript('files_svgedit', 'canvg/canvg');
OC_Util::addScript('files_svgedit', 'canvg/rgbcolor');
OC_Util::addScript('files_svgedit', 'base64');
//OC_Util::addScript('files_svgedit', 'jsPDF/libs/sprintf');
//OC_Util::addScript('files_svgedit', 'jsPDF/jspdf');
OC_Util::addScript('files_svgedit', 'jsPDF/jspdf.min');
OC_Util::addScript('files_svgedit', 'svgToPdf');
OC_App::setActiveNavigationEntry('files_index');
$path = $_GET['file'];
if(method_exists('OC_Filesystem', 'is_writable')) {
	$writable = OC_Filesystem::is_writable($path);
} else {
	$writable = OC_Filesystem::is_writeable($path);
}
if(isset($_GET['file']) and $writable) {
    $filecontents = OC_Filesystem::file_get_contents($path);
    $filemtime = OC_Filesystem::filemtime($path);
} else {
    $filecontents = "";
    $filemtime = 0;
}
    
$tmpl = new OC_TEMPLATE( "files_svgedit", "editor", "user" );
$tmpl->assign('fileContents', json_encode($filecontents));
$tmpl->assign('filemTime', $filemtime);
$tmpl->assign('filePath', json_encode($path));
$tmpl->printPage();
