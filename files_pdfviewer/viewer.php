<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('files_pdfviewer');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

// TODO: add mime type detection and load the template
$mime = "application/pdf";

$page = new OCP\Template( 'files_pdfviewer', 'pdf');
$page->assign('dir', $dir);
$page->assign('file', $file);
$page->printPage();