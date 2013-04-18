<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$doc_root = $_SERVER["DOCUMENT_ROOT"];
$user = OCP\User::getUser();

$mime = "application/msword";

$mystring = system('python /opt/jischtml5/tools/commandline/WordDownOO.py '.$doc_root.'/owncloud/data/'.$user.'/files'.$dir.$file , $retval);
