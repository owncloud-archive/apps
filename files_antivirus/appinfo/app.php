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

OCP\App::registerAdmin('files_antivirus', 'settings');

OC::$CLASSPATH['OCA\Files_Antivirus\Scanner_Local'] = 'files_antivirus/lib/scanner/local.php';
OC::$CLASSPATH['OCA\Files_Antivirus\Scanner_External'] = 'files_antivirus/lib/scanner/external.php';

OCP\Util::connectHook('OC_Filesystem', 'post_write', '\OCA\Files_Antivirus\Scanner', 'av_scan');
OCP\BackgroundJob::AddRegularTask('OCA\Files_Antivirus\BackgroundScanner', 'check');
