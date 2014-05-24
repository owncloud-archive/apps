<?php

/**
 * ownCloud
 *
 * @author Florian Reinhard
 * @copyright 2013 Florian Reinhard <florian.reinhard@googlemail.com>
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

class DjangoDatabase {
	protected static $db = null;
	protected static $failed = false;

	private function __construct () {}
	private function __clone() {}

	public static function getDatabase () {
		if (self::$failed == false && is_null(self::$db)) {
			$db_host     = OCP\Config::getAppValue('django_auth', 'django_db_host','localhost');
			$db_name     = OCP\Config::getAppValue('django_auth', 'django_db_name','');
			$db_driver   = OCP\Config::getAppValue('django_auth', 'django_db_driver', 'mysql');
			$db_user     = OCP\Config::getAppValue('django_auth', 'django_db_user','');
			$db_password = OCP\Config::getAppValue('django_auth', 'django_db_password','');
			$dsn = "${db_driver}:host=${db_host};dbname=${db_name}";

			try {
				self::$db = new PDO($dsn, $db_user, $db_password);
			} catch (PDOException $e) {
				self::$failed = true;
				OCP\Util::writeLog('OC_User_Django',
					'OC_User_Django, Failed to connect to redmine database: ' . $e->getMessage(),
					\OCP\Util::ERROR);
			}
		}

		if (self::$failed) {
			return false;
		}
		else {
			return self::$db;
		}
	}
}