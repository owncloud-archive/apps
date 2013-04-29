<?php
OCP\App::setActiveNavigationEntry("xmpp_roster");
OCP\Util::addScript('xmpp', 'manageRoster');

$tmpl = new OCP\Template("xmpp", "manageRoster", 'user');
$tmpl->printpage();
?>

