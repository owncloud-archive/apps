<?php

// Check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('map');

OCP\App::setActiveNavigationEntry( 'map' );

$tmpl = new OCP\Template( 'map', 'main', 'user' );
OCP\Util::addscript('map/3rdparty/leaflet', 'leaflet');
OCP\Util::addStyle('map/3rdparty/leaflet', 'leaflet');
OCP\Util::addscript('map/3rdparty', 'js_tpl');

OCP\Util::addStyle('map', 'main');
OCP\Util::addscript('map', 'main');

$tmpl->assign( 'category', OC_Map::$loaders);

$tmpl->printPage();
