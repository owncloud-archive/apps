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

require_once 'django_auth/lib/user.php';
require_once 'django_auth/lib/group.php' ;

define('OC_GROUP_BACKEND_DJANGO_STAFF_IS_ADMIN',     true);
define('OC_GROUP_BACKEND_DJANGO_SUPERUSER_IS_ADMIN', true);

OCP\App::registerAdmin('django_auth','settings');

OC_User::useBackend( 'Django' );
OC_Group::useBackend( new OC_GROUP_DJANGO() );
