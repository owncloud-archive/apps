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

require_once('../../lib/base.php');
OC_Util::checkAppEnabled('storage_charts');
OC_Util::checkLoggedIn();

$tmpl = new OC_Template('storage_charts', 'charts', 'user');

// Get data for all users if admin or juste for the current user
$tmpl->assign('pie_rfsus', OC_DLStCharts::getPieFreeUsedSpaceRatio());
$tmpl->assign('lines_usse', OC_DLStCharts::getLinesLastSevenDaysUsedSpace());

$tmpl->printPage();
