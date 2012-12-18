<?php

OCP\Util::addscript( 'reader', 'integrate' );
OCP\Util::addscript( 'reader', 'pdf' );
OCP\Util::addStyle('reader','reader');
OCP\Util::addStyle('files','files');

$file = $_GET['file'];
$path = dirname($file);
$filename = basename($file); 

$tmpl = new OCP\Template( 'reader', 'results', 'user' );
$tmpl->assign('file', $file);
$tmpl->assign('path', $path);
$tmpl->assign('filename', $filename);
$tmpl->printPage();

?>
