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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library.	If not, see <http://www.gnu.org/licenses/>.
*
*/

class OC_Gallery_Hooks_Handlers {

	public static function writePhoto($params) {
		$path = $params[OC_Filesystem::signal_param_path];
		if (self::isPhoto($path)) {
			OCP\Util::writeLog('gallery', 'updating thumbnail for ' . $path, OCP\Util::DEBUG);
			\OC\Pictures\ThumbnailsManager::getInstance()->getThumbnail($path);
		}
	}
	
	public static function removePhoto($params) {
		\OC\Pictures\ThumbnailsManager::getInstance()->delete($params[OC_Filesystem::signal_param_path]);
	}

	public static function renamePhoto($params) {
		$oldpath = $params[OC_Filesystem::signal_param_oldpath];
		$newpath = $params[OC_Filesystem::signal_param_newpath];
		//TODO: implement this
	}
	
	private static function isPhoto ($path) {
		$ext = strtolower(substr($path, strrpos($path, '.')+1));
		return $ext=='png' || $ext=='jpeg' || $ext=='jpg' || $ext=='gif';
		//$mimetype = OC_FileSystem::getMimeType($path);
		//return substr($mimetype, 0, 5) === 'image';
	}
}
