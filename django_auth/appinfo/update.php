<?php
/**
 * ownCloud - Django Authentification Backend
 *
 * @author Florian Reinhard
 * @copyright 2014 Florian Reinhard <florian.reinhard@googlemail.com>
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

// Get currently installed version
$installedVersion = OCP\Config::getAppValue( 'django_auth', 'installed_version', '' );


// Do configuration migration for versions after 0.3 and before 0.6. Versions 0.4 and 0.5 should be affected
if ( version_compare('0.3', $installedVersion, '<') && version_compare('0.6', $installedVersion, '>'))
{
	OCP\Util::writeLog('Django Authentification Backend', 'Migrating Database settings from oc_appinfo to config.php', \OCP\Util::INFO);

	// Migrate ApPConfig keys to config.php
	OCP\Config::setSystemValue('django_db_host',    OCP\Config::getAppValue('django_auth', 'django_db_host','localhost'));
	OCP\Config::setSystemValue('django_db_name',    OCP\Config::getAppValue('django_auth', 'django_db_name',''));
	OCP\Config::setSystemValue('django_db_driver',  OCP\Config::getAppValue('django_auth', 'django_db_driver', 'mysql'));
	OCP\Config::setSystemValue('django_db_user',    OCP\Config::getAppValue('django_auth', 'django_db_user',''));
	OCP\Config::setSystemValue('django_db_password',OCP\Config::getAppValue('django_auth', 'django_db_password',''));

	// delete old AppConfig keys
	OC_AppConfig::deleteKey('django_auth', 'django_db_host');
	OC_AppConfig::deleteKey('django_auth', 'django_db_name');
	OC_AppConfig::deleteKey('django_auth', 'django_db_driver');
	OC_AppConfig::deleteKey('django_auth', 'django_db_user');
	OC_AppConfig::deleteKey('django_auth', 'django_db_password');
}
