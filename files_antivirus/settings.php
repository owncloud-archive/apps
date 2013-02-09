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

OCP\User::checkAdminUser();

$params = array(
	'av_mode' => 'executable',
	'av_host' => '',
	'av_port' => '',
	'av_chunk_size' => '1024',
	'av_path' => '/usr/bin/clamscan',
	'infected_action' => 'only_log',
	);

if($_POST){
	OCP\Util::callCheck();
	foreach($params as $param => $default){
		if(isset($_POST[$param])){
			OCP\Config::setAppValue('files_antivirus', $param, $_POST[$param]);
		}
	}
}

// fill template
$tmpl = new OC_Template( 'files_antivirus', 'settings');
$tmpl->assign('requesttoken', OCP\Util::callRegister());
OCP\Util::addScript('files_antivirus', 'settings');
foreach($params as $param => $default){
		$value = OCP\Config::getAppValue('files_antivirus', $param, $default);
		$tmpl->assign($param, $value);
}

return $tmpl->fetchPage();
