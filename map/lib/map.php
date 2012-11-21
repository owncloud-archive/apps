<?php

class OC_Map {
	public static $loaders = array(
		array('name'=> 'Home', 'id' => 'home', 'depth' => 1),
		array('name'=> 'Favorite', 'id' => 'fav', 'icon'=> 'star important', 'depth' => 2),
		array('name'=> 'My Contacts', 'id' => 'contact', 'depth' => 2),
		array('name'=> 'Events', 'id' => 'event', 'depth' => 2),
		array('name'=> 'My Places', 'id' => 'place', 'depth' => 2),
	);
}
