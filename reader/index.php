<?php

/**
* ownCloud - eBook reader application
*
* @author Priyanka Menghani
* 
*/

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('reader');
OCP\App::setActiveNavigationEntry( 'reader_index' );

OCP\Util::addStyle( 'files', 'files' );
OCP\Util::addscript( 'reader', 'integrate' );

// Get the current directory from window url.
$dir = empty($_GET['dir'])?'/':$_GET['dir'];

$tmpl = new OCP\Template( 'reader', 'index', 'user' );
$tmpl->assign('dir', $dir);
$tmpl->printPage();

?>
