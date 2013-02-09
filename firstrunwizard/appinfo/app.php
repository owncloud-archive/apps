<?php

/**
 * ownCloud - firstrunwizard App
 *
 * @author Frank Karlitschek
 * @copyright 2012 Frank Karlitschek karlitschek@kde.org
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


OC::$CLASSPATH['OCA_FirstRunWizard\Config'] = 'apps/firstrunwizard/lib/firstrunwizard.php';

OCP\Util::addStyle( 'firstrunwizard', 'colorbox');
OCP\Util::addScript( 'firstrunwizard', 'jquery.colorbox');
OCP\Util::addScript( 'firstrunwizard', 'firstrunwizard');

OCP\Util::addStyle('firstrunwizard', 'firstrunwizard');

if(\OCP\User::isLoggedIn() and \OCA_FirstRunWizard\Config::isenabled()){
	OCP\Util::addScript( 'firstrunwizard', 'activate');
}
