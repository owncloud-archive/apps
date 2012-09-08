<?php

/**
* ownCloud - gallery application
*
* @author Bartek Przybylski
* @copyright 2012 Bartek Przybylski <bartek@alefzero.eu>
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library. If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\Gallery;

class Scanner {
	private static $albums=array();
	private static $images=array();

	public static function getGalleryRoot() {
		return \OCP\Config::getUserValue(OCP\USER::getUser(), 'gallery', 'root', '/');
	}
	public static function getScanningRoot() {
		return \OC_Filesystem::getRoot().self::getGalleryRoot();
	}

	public static function cleanUp() {
		Album::cleanup();
	}

	public static function createName($name) {
		$name = basename($name);
		return $name == '.' ? '' : $name;
	}

	/**
	 * Scan single dir relative to gallery root
	 * @param OC_EventSource $eventSource
	 */
	public static function scan( $eventSource ) {
		$images = \OC_FileCache::searchByMime('image','', self::getScanningRoot());

		$done = 0;
		foreach ( $images as $image ){
			$path = dirname($image);
			if( !isset(self::$albums[$path]) ){
				self::$albums[$path] = self::createAlbum( $path );
			}
			self::scanImage( $image, self::$albums[$path] );
			self::$images[$path][] = $image;
			$done++;
			$eventSource->send('scanned', $done);
		}

		foreach( self::$albums as $path => $albumId ){
			self::createThumbnails($albumId, self::$images[$path]);
		}
		$eventSource->send('done', 1);
	}

	private static function scanImage($path, $albumId){
		if( count( Photo::find($albumId, $path) ) ){
			Photo::create($albumId, $path);
		}
	}

	private static function createAlbum($path){
		$owner = \OCP\USER::getUser();
		if( count( Album::find($owner, null, $path) ) == 0 ){
			$name = self::createName($path);
			$id = Album::create($owner, $name, $path);

			//create parent albums where necesary
			if($path !=='' ){
				$parent = basename( $path );
				if( !isset( self::$albums[$parent] ) ){
					self::createAlbum($parent);
				}
			}

			self::$images[$path]=array();
			return $id;
		}
	}

	public static function createThumbnails($albumId, $files) {
		// create gallery thumbnail
		$count = min(count($files), 10);
		$thumbnail = imagecreatetruecolor($count*200, 200);
		for ($i = 0; $i < $count; $i++) {
			$image = Photo::getThumbnail($files[$i], null, true);
			if ($image && $image->valid()) {
				imagecopy($thumbnail, $image->resource(), $i*200, 0, 0, 0, 200, 200, 200, 200);
				$image->destroy();
			}
		}
		
		$galleryDir = \OC_User::getHome(OC_User::getUser()) . '/gallery/';
		imagepng($thumbnail, $galleryDir.$albumId.'.png');
		imagedestroy($thumbnail);
	}
}
