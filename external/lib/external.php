<?php

/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

class OC_External {

	public static function getSites() {
		if (($sites = json_decode(OCP\Config::getUserValue(OCP\User::getUser(), "external", "sites", ''))) != null) {
			return $sites;
		}

		return array();
	}

	public static function getGlobalSites() {
		if (($sites = json_decode(OCP\Config::getAppValue("external", "globalSites"))) != null) {
			return $sites;
		}

		return array();
	}


}
