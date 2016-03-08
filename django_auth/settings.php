<?php
/**
 * ownCloud - Django Authentification Backend
 *
 * @author Florian Reinhard
 * @copyright 2012 Florian Reinhard <florian.reinhard@googlemail.com>
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

$params = array('staff_is_admin', 'superuser_is_admin');

if ($_POST) {
	foreach($params as $param) {
		if(isset($_POST[$param])) {
			OCP\Config::setAppValue('django_auth', $param, $_POST[$param]);
		}
		else {
			// unchecked checkboxes are not included in the post paramters
			OCP\Config::setAppValue('django_auth', $param, 0);
		}
	}
}

// fill template
$tmpl = new OCP\Template( 'django_auth', 'settings');
$tmpl->assign('staff_is_admin',    OCP\Config::getAppValue( 'django_auth', 'staff_is_admin',     OC_GROUP_BACKEND_DJANGO_STAFF_IS_ADMIN ));
$tmpl->assign('superuser_is_admin',OCP\Config::getAppValue( 'django_auth', 'superuser_is_admin', OC_GROUP_BACKEND_DJANGO_SUPERUSER_IS_ADMIN ));

return $tmpl->fetchPage();
