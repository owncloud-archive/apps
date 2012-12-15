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

	$casVersion = OCP\Config::getAppValue('user_cas', 'cas_server_version', '1.0');
	$casHostname = OCP\Config::getAppValue('user_cas', 'cas_server_hostname', '');
	$casPort = OCP\Config::getAppValue('user_cas', 'cas_server_port', '443');
	$casPath = OCP\Config::getAppValue('user_cas', 'cas_server_path', '/cas');
        $casCertPath = OCP\Config::getAppValue('user_cas', 'cas_cert_path', '');

	if (!empty($casHostname)) {

		include_once('CAS.php');

		# phpCAS::setDebug();

		phpCAS::client($casVersion,$casHostname,(int)$casPort,$casPath);

		if(!empty($casCertPath)) {
			phpCAS::setCasServerCACert($casCertPath);
		}
		else {
			phpCAS::setNoCasServerValidation();
		}

		phpCAS::setNoClearTicketsFromUrl();

		phpCAS::forceAuthentication();
	}
