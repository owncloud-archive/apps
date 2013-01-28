<?php

/**
* ownCloud - gallery application
*
* @author Bartek Przybylski
* @copyright 2012 Bartek Przybylski bart.p.pl@gmail.com
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

OC::$CLASSPATH['OC_Gallery_Album'] = 'gallery/lib/album.php';
OC::$CLASSPATH['OC_Gallery_Photo'] = 'gallery/lib/photo.php';
OC::$CLASSPATH['OC_Gallery_Scanner'] = 'gallery/lib/scanner.php';
OC::$CLASSPATH['OC_Gallery_Sharing'] = 'gallery/lib/sharing.php';
OC::$CLASSPATH['OC_Gallery_Hooks_Handlers'] = 'gallery/lib/hooks_handlers.php';
OC::$CLASSPATH['OC\Pictures\ThumbnailsManager'] = 'gallery/lib/managers.php';
OC::$CLASSPATH['OC\Pictures\DatabaseManager'] = 'gallery/lib/managers.php';
OC::$CLASSPATH['OC\Pictures\TilesLine'] = 'gallery/lib/tiles.php';
OC::$CLASSPATH['OC\Pictures\TileSingle'] = 'gallery/lib/tiles.php';
OC::$CLASSPATH['OC\Pictures\TileStack'] = 'gallery/lib/tiles.php';
OC::$CLASSPATH['OC_Share_Backend_Photo'] = 'gallery/lib/share.php';

// OCP\Share::registerBackend('photo', new OC_Share_Backend_Photo());

$l = OC_L10N::get('gallery');

OCP\App::addNavigationEntry( array(
 'id' => 'gallery_index',
 'order' => 20,
 'href' => OCP\Util::linkTo('gallery', 'index.php'),
 'icon' => OCP\Util::imagePath('core', 'places/picture.svg'),
 'name' => $l->t('Pictures')));

class OC_GallerySearchProvider extends OC_Search_Provider{
	function search($query) {
		$stmt = OCP\DB::prepare('SELECT * FROM `*PREFIX*gallery_albums` WHERE `uid_owner` = ? AND `album_name` LIKE ?');
		$result = $stmt->execute(array(OCP\USER::getUser(),'%'.$query.'%'));
		$results=array();
		while($row=$result->fetchRow()) {
			$results[]=new OC_Search_Result($row['album_name'],'',OCP\Util::linkTo('gallery', 'index.php').'?view='.$row['album_name'], (string) $l->t('Galleries'));
		}
		return $results;
	}
}

//OC_Search::registerProvider('OC_GallerySearchProvider');

OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME, \OC\Files\Filesystem::signal_post_write, 'OC_Gallery_Hooks_Handlers', 'writePhoto');
OCP\Util::connectHook(\OC\Files\Filesystem::CLASSNAME, \OC\Files\Filesystem::signal_delete, 'OC_Gallery_Hooks_Handlers', 'removePhoto');
//OCP\Util::connectHook(OC_Filesystem::CLASSNAME, OC_Filesystem::signal_post_rename, "OC_Gallery_Hooks_Handlers", "renamePhoto");
