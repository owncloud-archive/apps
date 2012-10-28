<?php

/**
 * ownCloud - Impressionist & Impress App
 *
 * @author Raghu Nayyar & Frank Karlitschek
 * @copyright 2012  me@iraghu.com Frank Karlitschek karlitschek@kde.org
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

OCP\Util::addStyle('impressionist', 'style'); 

OCP\App::addNavigationEntry( array(
	'id' => 'impressionist_index',
	'order' => 74,
	'href' => OCP\Util::linkTo( 'impressionist', 'index.php' ),
	'icon' => OCP\Util::imagePath( 'impressionist', 'impress.png' ),
	'name' => 'Impressionist'
));
