<?php

/**
* ownCloud - gallery application
*
* @author Bartek Przybylski
* @copyright 2012 Bartek Przybylski bartek@alefzero.eu
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



OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('gallery');
OCP\App::setActiveNavigationEntry( 'gallery_index' );

OCP\Util::addStyle('files', 'files');
OCP\Util::addStyle('gallery', 'styles');
OCP\Util::addScript('gallery', 'pictures');
OCP\Util::addStyle( 'gallery', 'supersized' );
OCP\Util::addStyle( 'gallery', 'supersized.shutter' );
OCP\Util::addScript('gallery', 'slideshow');
OCP\Util::addScript('gallery', 'jquery.easing.min');
OCP\Util::addScript('gallery', 'supersized.3.2.7.min');
OCP\Util::addScript('gallery', 'supersized.shutter.min');

if (!OCP\App::isEnabled('files_imageviewer')) {
	OCP\Template::printUserPage('gallery', 'no-image-app');
	exit;
}

$root = !empty($_GET['root']) ? $_GET['root'] : '/';
$files = \OC_Files::getDirectoryContent($root, 'image');

$tl = new \OC\Pictures\TilesLine();
$ts = new \OC\Pictures\TileStack(array(), '');

$root_images = array();

foreach($files as $file) {
	$filename = $root.$file['name'];
	if ($file['type'] == 'file') {
		$root_images[] = $filename;
	}
	else {
		// it is a dir, look for images in subdirs. We keep trying till
		// we find some images or there are no subdirs anymore to check.
		$name = $file['name'];
		$second_level_images = array();
		$dirs_to_check = array($filename);
		while (!empty($dirs_to_check)) {
			// get next subdir to check
			$subdir = array_pop($dirs_to_check);
			$subdir_files = \OC_Files::getDirectoryContent($subdir, 'image');
			foreach($subdir_files as $file) {
				if ($file['type'] == 'file') {
					$second_level_images[] = $subdir.'/'.$file['name'];
				}
				else {
					$dirs_to_check[] = $subdir.'/'.$file['name'];
				}
			}
			if(count($second_level_images) != 0) {
				// if we collected images for this directory
				$tl->addTile(new \OC\Pictures\TileStack($second_level_images, $name));
				break;
			}
		}
	}
}

// and finally our images actually stored in the root folder
for($i = 0; $i<count($root_images); $i++) {
	$tl->addTile(new \OC\Pictures\TileSingle($root_images[$i]));
}

$tmpl = new OCP\Template( 'gallery', 'index', 'user' );
$tmpl->assign('root', $root, false);
$tmpl->assign('tl', $tl, false);
$tmpl->printPage();
