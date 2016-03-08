<?php

/**
 * ownCloud - External app
 *
 * @author Frank Karlitschek
 * @copyright 2012 Frank Karlitschek frank@owncloud.org
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

use OCA\External\External;

OCP\App::registerAdmin('external', 'settings');

$sites = External::getSites();
if (!empty($sites)) {
	$urlGenerator = \OC::$server->getURLGenerator();
	$navigationManager = \OC::$server->getNavigationManager();
	for ($i = 0; $i < sizeof($sites); $i++) {
		$navigationEntry = function () use ($i, $urlGenerator, $sites) {
			return [
				'id'    => 'external_index' . ($i + 1),
				'order' => 80 + $i,
				'href' => $urlGenerator->linkToRoute('external_index', ['id'=> $i + 1]),
				'icon' => $urlGenerator->imagePath('external', !empty($sites[$i][2]) ? $sites[$i][2] : 'external.svg'),
				'name' => $sites[$i][0],
			];
		};
		$navigationManager->add($navigationEntry);
	}
}
