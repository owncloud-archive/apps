<?php

// Check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('map');

OCP\App::setActiveNavigationEntry( 'map' );

$tmpl = new OCP\Template( 'map', 'main', 'user' );
OCP\Util::addscript('map/3rdparty/leaflet', 'leaflet');
OCP\Util::addStyle('map/3rdparty/leaflet', 'leaflet');
OCP\Util::addscript('map/3rdparty', 'js_tpl');

OCP\Util::addStyle('map/3rdparty/codaslider', 'coda-slider');
OCP\Util::addscript('map/3rdparty/codaslider', 'jquery.coda-slider-3.0');

OCP\Util::addStyle('map', 'main');
OCP\Util::addscript('map', 'main');
$items = OC_Map::findAll();
$tmpl->assign( 'map_item', $items );
$tmpl->assign( 'category', array(
	array('name'=> 'Home', 'id' => 'home', 'depth' => 1),
	array('name'=> 'Favorite', 'id' => 'fav', 'icon'=> 'star important', 'depth' => 2),
	array('name'=> 'My Contacts', 'id' => 'contact', 'depth' => 2),
	array('name'=> 'Events', 'id' => 'event', 'depth' => 2),
	array('name'=> 'My places', 'id' => 'place', 'depth' => 2),
)
 );
$tmpl->printPage();
