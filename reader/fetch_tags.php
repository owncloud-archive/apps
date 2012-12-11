<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('reader');
OCP\App::setActiveNavigationEntry( 'reader_index' );

OCP\Util::addscript( 'reader', 'integrate' );
OCP\Util::addscript( 'reader', 'pdf' );
OCP\Util::addStyle('reader','reader');
OCP\Util::addStyle('files','files');

$tag = $_GET['tag'];
$tmpl = new OCP\Template( 'reader', 'tagged', 'user' );
$tmpl->assign('tag', $tag);
$tmpl->printPage();

?>
