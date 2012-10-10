<?php

/**
* ownCloud - App Template plugin
*
* @author Florian Jacob
* @copyright 2012 Florian Jacob fjacob@lavabit.com
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
*
* This uses the Zend OpenID implementation, find a tutorial about it at http://framework.zend.com/manual/en/zend.openid.html .
*
*/

OC::$CLASSPATH['OC_OpenIdProviderUserSession'] = 'user_openid_provider/lib/OpenIdProviderUserSession.php';
OC::$CLASSPATH['OC_OpenIdProviderStorage'] = 'user_openid_provider/lib/OpenIdProviderStorage.php';

$userName='';
if(strpos($_SERVER["REQUEST_URI"],'?') and !strpos($_SERVER["REQUEST_URI"],'=')){
	if(strpos($_SERVER["REQUEST_URI"],'/?') !== false){
		$userName=substr($_SERVER["REQUEST_URI"],strpos($_SERVER["REQUEST_URI"],'/?')+2);
	}elseif(strpos($_SERVER["REQUEST_URI"],'.php?') !== false){
		$userName=substr($_SERVER["REQUEST_URI"],strpos($_SERVER["REQUEST_URI"],'.php?')+5);
	}
}
$remote_token = 'openid_provider';
if (($pos = strpos($_SERVER["REQUEST_URI"],$remote_token)) !== false) {
	$pos += strlen($remote_token)+1;
	$userName = substr($_SERVER['REQUEST_URI'],$pos);
}
//die('username: ' . $userName);
if ($userName != '') {
	OCP\Util::addHeader('link',array('rel'=>'openid.server', 'href'=>OCP\Util::linkToRemote( $remote_token ).$userName));
	OCP\Util::addHeader('link',array('rel'=>'openid.delegate', 'href'=>OCP\Util::linkToAbsolute('', '?').$userName));
}

OCP\App::registerPersonal('user_openid_provider', 'settings');
