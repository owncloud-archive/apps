<?php

/**
* ownCloud - open web apps plugin
*
* @author Frank Karlitschek
* @author Florian Hülsmann
* @copyright 2011 Frank Karlitschek karlitschek@kde.org
* @copyright 2012 Florian Hülsmann fh@cbix.de
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

OCP\App::registerAdmin( 'open_web_apps', 'settings' );

OCP\App::addNavigationEntry( array(
	'id' => 'open_web_apps',
	'order' => 74,
	'href' => OCP\Util::linkTo( 'open_web_apps', 'main.php' ),
	'icon' => OCP\Util::imagePath( 'open_web_apps', 'mozilla-rocket-small.png' ),
	'name' => 'Open web apps'
));
