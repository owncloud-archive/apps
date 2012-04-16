<?php

/**
* ownCloud - DjazzLab Storage Charts plugin
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

OC_Util::checkAppEnabled('storage_charts');

OC::$CLASSPATH['OC_DLStCharts'] = "apps/storage_charts/lib/dlstcharts.class.php";

OC_App::register(Array(
	'order' => 60,
	'id' => 'storage_charts',
	'name' => 'Storage Charts'
));

OC_App::addNavigationEntry(Array(
	'id' => 'storage_charts',
	'order' => 60,
	'href' => OC_Helper::linkTo('storage_charts', 'charts.php'),
	'icon' => OC_Helper::imagePath('storage_charts', 'chart.png'),
	'name' => 'DL Charts'
));

if(OC_User::getUser()){
	$path = substr(OC::$CONFIG_DATADIRECTORY, 0, strrpos(OC::$CONFIG_DATADIRECTORY, '/'));
	$path = substr($path, 0, strrpos($path, '/')+1);
	
	$used = OC_DLStCharts::getTotalDataSize(OC::$CONFIG_DATADIRECTORY);
	$total = OC_DLStCharts::getTotalDataSize($path) + OC_Filesystem::free_space();
	OC_DLStCharts::update($used, $total);
}
