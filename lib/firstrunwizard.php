<?php

/**
 * ownCloud - firstrunwizard App
 *
 * @author Frank Karlitschek
 * @copyright 2012 Frank Karlitschek frank@owncloud.org
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


namespace OCA_FirstRunWizard;

class Config {

	public static function enable() {
		\OCP\Config::setUserValue( \OCP\User::getUser(), 'firstrunwizard', 'show', 1 );
	}
	
	public static function disable() {
		\OCP\Config::setUserValue( \OCP\User::getUser(), 'firstrunwizard', 'show', 0 );
	}

	public static function show() {
		$_SESSION['firstrunwizard_show']=1;
	}

	public static function hide() {
		$_SESSION['firstrunwizard_show']=0;
	}


	public static function isenabled() {
		$conf=\OCP\CONFIG::getUserValue( \OCP\User::getUser() , 'firstrunwizard' , 'show' , 1 );
		if($conf==1) {
			if(!isset($_SESSION['firstrunwizard_show']) or $_SESSION['firstrunwizard_show']==1) {
				return(true);
			}else{
				return(false);
			}
		}else{
				return(false);
		}
	}



}

?>
