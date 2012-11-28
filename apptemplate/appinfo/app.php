<?php

/**
* ownCloud - App Template plugin
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

OCP\App::registerAdmin( 'apptemplate', 'settings' );

OCP\App::addNavigationEntry( array( 
	'id' => 'apptemplate',
	'order' => 74,
	'href' => OCP\Util::linkTo( 'apptemplate', 'index.php' ),
	'icon' => OCP\Util::imagePath( 'apptemplate', 'example.png' ),
	'name' => 'App Template'
));
