<?php

/**
* ownCloud - App Template Example
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

require_once('../../lib/base.php');

// Check if we are a user
if( !OC_User::isLoggedIn()){
	header( "Location: ".OC_Helper::linkTo( '', 'index.php' ));
	exit();
}

// Add JavaScript and CSS files
OC_Util::addScript('mail','mail');
OC_Util::addStyle('mail','mail');


$somesetting=OC_Config::getValue( "somesetting", '' );
OC_App::setActiveNavigationEntry( 'apptemplate');
$tmpl = new OC_Template( 'apptemplate', 'main', 'user' );
$tmpl->assign('somesetting',$somesetting);
$tmpl->printPage();


?>
