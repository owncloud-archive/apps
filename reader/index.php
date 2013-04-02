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

OCP\Util::addscript( 'reader', 'integrate' );
OCP\Util::addscript( 'reader', 'pdf' );
OCP\Util::addStyle('reader','reader');
OCP\Util::addStyle('files','files');

// Get the current directory from window url.
$dir = empty($_GET['dir'])?'/':$_GET['dir'];

$tmpl = new OCP\Template( 'reader', 'index', 'user' );
$tmpl->assign('dir', $dir);
$tmpl->printPage();

?>
