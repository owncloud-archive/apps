<?php

/**
* ownCloud - Internal Bookmarks plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

OC::$CLASSPATH['OC_IntBks'] = 'apps/internal_bookmarks/lib/intbks.class.php';

OC_App::register(Array(
	'order' => 70,
	'id' => 'internal_bookmarks',
	'name' => 'Internal Bookmarks'
));

OC_Util::addScript("internal_bookmarks", "actlink.min");

$i = 0;
foreach(OC_IntBks::getAllItemsByUser() as $item) {
	OC_App::addNavigationEntry(Array(
  		'id' => 'internal_bookmarks_index_' . $item['bkid'],
  		'order' => 70 + ($item['bkorder'] / 100),
  		'href' => OC_Helper::linkTo('files', 'index.php?dir=' . $item['bktarget']),
  		'icon' => OC_Helper::imagePath('internal_bookmarks', 'star_on.png'),
  		'name' => $item['bktitle']
  	));
	$i++;
}

if($i > 0) {
	OC_App::registerPersonal('internal_bookmarks', 'settings');
}
