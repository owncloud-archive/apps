<?php

/**
* ownCloud - files_antivirus
*
* @author Manuel Deglado
* @copyright 2012 Manuel Deglado manuel.delgado@ucr.ac.cr
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

OC::$CLASSPATH['OC_Files_Antivirus'] = OC_App::getAppPath('files_antivirus').'/lib/clamav.php';
OC::$CLASSPATH['OC_Files_Antivirus_BackgroundScanner'] = OC_App::getAppPath('files_antivirus').'/lib/scanner.php';

OC_APP::registerAdmin('files_antivirus', 'settings');
OC_Hook::connect('OC_Filesystem', 'post_write', 'OC_Files_Antivirus', 'av_scan');

OCP\BackgroundJob::AddRegularTask('OC_Files_Antivirus_BackgroundScanner', 'check');
