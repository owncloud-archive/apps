<?php
/**
 * 2013 Tobia De Koninck tobia@ledfan.be
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

require_once 'lib/external.php';

OCP\User::checkLoggedIn();

if (isset($_GET['id'])) {

	$id = $_GET['id'];
	$id = (int) $id;

	$sites = OC_External::getSites();
	if (sizeof($sites) >= $id) {
		$url = $sites[$id - 1][1];
		OCP\App::setActiveNavigationEntry('external_index' . $id);
		
		$tmpl = new OCP\Template('external', 'frame', 'user');
		//overwrite x-frame-options
		$tmpl->addHeader('X-Frame-Options', 'ALLOW-FROM *');
		
		$tmpl->assign('url', $url);
		$tmpl->printPage();
	}
}
