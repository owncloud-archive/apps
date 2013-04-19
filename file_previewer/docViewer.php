<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('file_previewer');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$doc_root = $_SERVER["DOCUMENT_ROOT"];
$user = OCP\User::getUser();

$path_parts = pathinfo($file);
$file_name = basename($file, '.'.$path_parts['extension']);

$mime = "application/msword";

//how to do caching? well see if this id or something in the cache and if not, insert it and then show.
//otherwise, retrieve it from cache and show

system('python /opt/jischtml5/tools/commandline/WordDownOO.py '.$doc_root.'/owncloud/data/'.$user.'/files'.$dir.$file , $retval);
$content = file_get_contents($doc_root.'/owncloud/data/'.$user.'/files/'.$file_name.'/'.$file_name.'.html');
print $content;