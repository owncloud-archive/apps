<?php
// Init owncloud
require_once '../../lib/base.php';
OCP\User::checkLoggedIn();
OC_Util::checkAppEnabled('files_svgedit');
// load required style sheets:
OCP\Util::addStyle('files_svgedit', 'ocsvg');
// load required javascripts:
OCP\Util::addScript('files_svgedit', 'svg-edit/embedapi');
OCP\Util::addScript('files_svgedit', 'ocsvgEditor');
OCP\Util::addScript('files_svgedit', 'canvg/canvg');
OCP\Util::addScript('files_svgedit', 'canvg/rgbcolor');
OCP\Util::addScript('files_svgedit', 'base64');
//OCP\Util::addScript('files_svgedit', 'jsPDF/libs/sprintf');
//OCP\Util::addScript('files_svgedit', 'jsPDF/jspdf');
OCP\Util::addScript('files_svgedit', 'jsPDF/jspdf.min');
OCP\Util::addScript('files_svgedit', 'svgToPdf');
OCP\App::setActiveNavigationEntry('files_index');
$path = $_GET['file'];

$writable = \OC\Files\Filesystem::isUpdatable($path);

if(isset($_GET['file']) and $writable) {
    $filecontents = \OC\Files\Filesystem::file_get_contents($path);
    $filemtime = \OC\Files\Filesystem::filemtime($path);
} else {
    $filecontents = "";
    $filemtime = 0;
}
    
$tmpl = new OCP\Template( "files_svgedit", "editor", "user" );
$tmpl->assign('fileContents', json_encode($filecontents));
$tmpl->assign('filemTime', $filemtime);
$tmpl->assign('filePath', json_encode($path));
$tmpl->printPage();
