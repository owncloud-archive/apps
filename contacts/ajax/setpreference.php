<?php
/**
 * ownCloud - Contacts
 *
 * @copyright 2012 Thomas Tanghus <thomas@tanghus.net>
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

/**
 * @brief Set user preference.
 * @param $key
 * @param $value
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::callCheck();

require_once 'loghandler.php';

$key = isset($_POST['key'])?$_POST['key']:null;
$value = isset($_POST['value'])?$_POST['value']:null;
if(is_null($key)) {
	bailOut(OCA\Contacts\App::$l10n->t('Key is not set for: '.$value));
}

if(is_null($value)) {
	bailOut(OCA\Contacts\App::$l10n->t('Value is not set for: '.$key));
}

if(OCP\Config::setUserValue(OCP\USER::getUser(), 'contacts', $key, $value)) {
	OCP\JSON::success(array(
		'data' => array(
			'key' => $key,
			'value' => $value)
		)
	);
} else {
	bailOut(OCA\Contacts\App::$l10n->t(
		'Could not set preference: ' . $key . ':' . $value)
	);
}
