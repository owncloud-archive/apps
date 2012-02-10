<?php

/**
* ownCloud - App Template plugin
*
* @author Frank Karlitschek
* @copyright 2011 Frank Karlitschek karlitschek@kde.org
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

OC_APP::registerAdmin('apptemplate','settings');

OC_App::register( array( 'order' => 70, 'id' => 'apptemplate', 'name' => 'App Template' ));

OC_App::addNavigationEntry( array( 'id' => 'apptemplate', 'order' => 74, 'href' => OC_Helper::linkTo( 'apptemplate', 'index.php' ), 'icon' => OC_Helper::imagePath( 'apptemplate', 'example.png' ), 'name' => 'App Template'));

