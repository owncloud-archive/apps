<?php

/**
 * ownCloud - user_cas
 *
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @copyright Sixto Martin Garcia. 2012
 * @copyright Leonis. 2014 <devteam@leonis.at>
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

OC_Util::checkAdminUser();

$params = array('cas_server_version', 'cas_server_hostname', 'cas_server_port', 'cas_server_path', 'cas_autocreate', 'cas_update_user_data',
 'cas_protected_groups', 'cas_default_group', 'cas_email_mapping', 'cas_displayName_mapping','cas_group_mapping','cas_cert_path');

OCP\Util::addscript('user_cas', 'settings');

if ($_POST) {
	// CSRF check
	OCP\JSON::callCheck();

	foreach($params as $param) {
		if (isset($_POST[$param])) {
			OCP\Config::setAppValue('user_cas', $param, $_POST[$param]);
		}  
		elseif ('cas_autocreate' == $param) {
			// unchecked checkboxes are not included in the post paramters
			OCP\Config::setAppValue('user_cas', $param, 0);
		}
		elseif ('cas_update_user_data' == $param) {
			OCP\Config::setAppValue('user_cas', $param, 0);
		}
	}
}

// fill template
$tmpl = new OCP\Template( 'user_cas', 'settings');
foreach ($params as $param) {
		$value = htmlentities(OCP\Config::getAppValue('user_cas', $param,''));
		$tmpl->assign($param, $value);
}

return $tmpl->fetchPage();
