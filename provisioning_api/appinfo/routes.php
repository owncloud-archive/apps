<?php

/**
* ownCloud - Provisioning API
*
* @author Tom Needham
* @copyright 2012 Tom Needham tom@owncloud.com
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

// users
OCP\API::register('get', '/cloud/users', array('OC_Provisioning_API_Users', 'getUsers'), 'provisioning_api', OC_API::ADMIN_AUTH);
OCP\API::register('post', '/cloud/users', array('OC_Provisioning_API_Users', 'addUser'), 'provisioning_api', OC_API::ADMIN_AUTH);
OCP\API::register('get', '/cloud/users/{userid}', array('OC_Provisioning_API_Users', 'getUser'), 'provisioning_api', OC_API::USER_AUTH);
OCP\API::register('put', '/cloud/users/{userid}', array('OC_Provisioning_API_Users', 'editUser'), 'provisioning_api', OC_API::USER_AUTH);
OCP\API::register('delete', '/cloud/users/{userid}', array('OC_Provisioning_API_Users', 'deleteUser'), 'provisioning_api', OC_API::ADMIN_AUTH);
OCP\API::register('get', '/cloud/users/{userid}/groups', array('OC_Provisioning_API_Users', 'getUsersGroups'), 'provisioning_api', OC_API::USER_AUTH);
OCP\API::register('post', '/cloud/users/{userid}/groups', array('OC_Provisioning_API_Users', 'addToGroup'), 'provisioning_api', OC_API::SUBADMIN_AUTH);
OCP\API::register('delete', '/cloud/users/{userid}/groups', array('OC_Provisioning_API_Users', 'removeFromGroup'), 'provisioning_api', OC_API::SUBADMIN_AUTH);
// groups
OCP\API::register('get', '/cloud/groups', array('OC_Provisioning_API_Groups', 'getGroups'), 'provisioning_api', OC_API::SUBADMIN_AUTH);
OCP\API::register('post', '/cloud/groups', array('OC_Provisioning_API_Groups', 'addGroup'), 'provisioning_api', OC_API::SUBADMIN_AUTH);
OCP\API::register('get', '/cloud/groups/{groupid}', array('OC_Provisioning_API_Groups', 'getGroup'), 'provisioning_api', OC_API::USER_AUTH);
OCP\API::register('delete', '/cloud/groups/{groupid}', array('OC_Provisioning_API_Groups', 'deleteGroup'), 'provisioning_api', OC_API::SUBADMIN_AUTH);
// apps
OCP\API::register('get', '/cloud/apps', array('OC_Provisioning_API_Apps', 'getApps'), 'provisioning_api', OC_API::ADMIN_AUTH);
OCP\API::register('get', '/cloud/apps/{appid}', array('OC_Provisioning_API_Apps', 'getAppInfo'), 'provisioning_api', OC_API::ADMIN_AUTH);
OCP\API::register('post', '/cloud/apps/{appid}', array('OC_Provisioning_API_Apps', 'enable'), 'provisioning_api', OC_API::ADMIN_AUTH);
OCP\API::register('delete', '/cloud/apps/{appid}', array('OC_Provisioning_API_Apps', 'disable'), 'provisioning_api', OC_API::ADMIN_AUTH);
