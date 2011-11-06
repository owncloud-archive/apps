<?php

/**
* ownCloud - Kolab plugin
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


if(isset($_GET['id'])){

	$id=$_GET['id'];

	$baseurl=OC_Config::getValue( "kolab-url", '' );
        if($id=='mail') {
          $url=$baseurl.'/themailurl';
        }elseif($id=='contacts'){
          $url=$baseurl.'/thecontactsurl';
        }elseif($id=='calendar'){
          $url=$baseurl.'/thecalendarurl';
        }else{
        }
	OC_App::setActiveNavigationEntry( 'kolab_'.$id );

	$tmpl = new OC_Template( 'kolab', 'frame', 'user' );
	$tmpl->assign('url',$url);
	$tmpl->printPage();

}

?>
