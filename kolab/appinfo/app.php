<?php

/**
* ownCloud - External plugin
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

OC_APP::registerAdmin('kolab','settings');

OC_App::register( array( 'order' => 70, 'id' => 'kolab', 'name' => 'Kolab' ));

OC_App::addNavigationEntry( array( 'id' => 'kolab_mail', 'order' => 73, 'href' => OC_Helper::linkTo( 'kolab', 'index.php' ).'?id=mail', 'icon' => OC_Helper::imagePath( 'kolab', 'kolab.png' ), 'name' => 'Mail'));

OC_App::addNavigationEntry( array( 'id' => 'kolab_calendar', 'order' => 75, 'href' => OC_Helper::linkTo( 'kolab', 'index.php' ).'?id=calendar', 'icon' => OC_Helper::imagePath( 'kolab', 'kolab.png' ), 'name' => 'Calendar'));

OC_App::addNavigationEntry( array( 'id' => 'kolab_contacts', 'order' => 74, 'href' => OC_Helper::linkTo( 'kolab', 'index.php' ).'?id=contacts', 'icon' => OC_Helper::imagePath( 'kolab', 'kolab.png' ), 'name' => 'Contacts'));

