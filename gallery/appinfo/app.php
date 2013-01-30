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

OC::$CLASSPATH['OCA\Gallery\Thumbnail'] = 'gallery/lib/thumbnail.php';
OC::$CLASSPATH['OCA\Gallery\AlbumThumbnail'] = 'gallery/lib/thumbnail.php';
OC::$CLASSPATH['OCA\Gallery\Share\Picture'] = 'gallery/lib/share.php';
OC::$CLASSPATH['OCA\Gallery\Share\Gallery'] = 'gallery/lib/share.php';

$l = OC_L10N::get('gallery');

OCP\App::addNavigationEntry(array(
		'id' => 'gallery_index',
		'order' => 20,
		'href' => OCP\Util::linkTo('gallery', 'index.php'),
		'icon' => OCP\Util::imagePath('core', 'places/picture.svg'),
		'name' => $l->t('Pictures'))
);


OCP\Util::connectHook('OC_Filesystem', 'post_write', 'OCA\Gallery\Thumbnail', 'writeHook');
OCP\Util::connectHook('OC_Filesystem', 'post_delete', 'OCA\Gallery\Thumbnail', 'removeHook');

OCP\Share::registerBackend('picture', 'OCA\Gallery\Share\Picture', null, array('gif', 'jpeg', 'jpg', 'png', 'svg', 'svgz'));
OCP\Share::registerBackend('gallery', 'OCA\Gallery\Share\Gallery', 'picture');
