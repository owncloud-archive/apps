<?php

/**
 * ownCloud - user_cas
 *
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @copyright Sixto Martin Garcia. 2012
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

$params = array('cas_server_version', 'cas_server_hostname', 'cas_server_port', 'cas_server_path', 'cas_autocreate', 'cas_update_user_data', 'cas_protected_groups', 'cas_default_group', 'cas_email_mapping', 'cas_group_mapping','cas_password_change_at_creation');

OCP\Util::addscript('user_cas', 'settings');

if ($_POST) {
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

// settings with default values
$tmpl->assign( 'cas_server_version', OCP\Config::getAppValue('user_cas', 'cas_server_version', '2.0'));
$tmpl->assign( 'cas_server_hostname', OCP\Config::getAppValue('user_cas', 'cas_server_hostname', ''));
$tmpl->assign( 'cas_server_port', OCP\Config::getAppValue('user_cas', 'cas_server_port', '443'));
$tmpl->assign( 'cas_server_path', OCP\Config::getAppValue('user_cas', 'cas_server_path', '/cas'));
$tmpl->assign( 'cas_cert_path', OCP\Config::getAppValue('user_cas', 'cas_cer_path', ''));

$tmpl->assign( 'cas_autocreate', OCP\Config::getAppValue('user_cas', 'cas_autocreate', 0));
$tmpl->assign( 'cas_update_user_data', OCP\Config::getAppValue('user_cas', 'cas_update_user_data', 0));
$tmpl->assign( 'cas_protected_groups', OCP\Config::getAppValue('user_cas', 'cas_protected_groups', ''));
$tmpl->assign( 'cas_default_group', OCP\Config::getAppValue('user_cas', 'cas_default_group', ''));
$tmpl->assign( 'cas_email_mapping', OCP\Config::getAppValue('user_cas', 'cas_email_mapping', 'mail'));
$tmpl->assign( 'cas_group_mapping', OCP\Config::getAppValue('user_cas', 'cas_group_mapping', ''));
$tmpl->assign( 'cas_password_change_at_creation', OCP\Config::getAppValue('user_cas', 'cas_password_change_at_creation', ''));

return $tmpl->fetchPage();
