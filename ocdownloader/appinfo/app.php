<?php

/**
* ownCloud - ocDownloader plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
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

$app_id = 'ocdownloader';

OC_Util::checkAppEnabled($app_id);

OC::$CLASSPATH['OC_ocDownloader'] = 'apps/' . $app_id . '/lib/ocDownloader.class.php';
OC::$CLASSPATH['OC_ocDownloaderFile'] = 'apps/' . $app_id . '/lib/ocDownloaderFile.class.php';

if(OC_ocDownloader::isUpToDate(OC_Appconfig::getValue($app_id, 'installed_version'))) {
	OC_ocDownloader::initProviders(dirname(__FILE__) . '/providers.xml');
}

OC_App::register(Array(
	'order' => 30,
	'id' => $app_id,
	'name' => 'ocDownloader'
));

OC_App::addNavigationEntry(Array(
	'id' => $app_id . '_index',
	'order' => 30,
	'href' => OC_Helper::linkTo($app_id, 'downloader.php'),
	'icon' => OC_Helper::imagePath($app_id, 'dl.png'),
	'name' => 'ocDownloader'
));

OC_App::registerPersonal($app_id, 'personalsettings');
