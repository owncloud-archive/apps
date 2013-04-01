<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
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

OCP\JSON::setContentTypeHeader('text/javascript');
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

echo 'var contacts_groups_sortorder=[' . OCP\Config::getUserValue(OCP\USER::getUser(), 'contacts', 'groupsort', '') . '],';
echo 'contacts_properties_indexed = '
	. (OCP\Config::getUserValue(OCP\USER::getUser(), 'contacts', 'contacts_properties_indexed', 'no') === 'no'
	? 'false' : 'true') . ',';
echo 'contacts_categories_indexed = '
	. (OCP\Config::getUserValue(OCP\USER::getUser(), 'contacts', 'contacts_categories_indexed', 'no') === 'no'
	? 'false' : 'true') . ',';
echo 'lang=\'' . OCP\Config::getUserValue(OCP\USER::getUser(), 'core', 'lang', 'en') . '\';';
