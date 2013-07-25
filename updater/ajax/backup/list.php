<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Updater;

\OCP\JSON::checkAdminUser();
\OCP\JSON::callCheck();

try {
	$list = Helper::scandir(App::getBackupBase());
} catch (\Exception $e) {
	$list = array();
}
clearstatcache();
$result = array();
foreach ($list as $item){
	if ($item=='.' || $item=='..'){
		continue;
	}
	$result[] = array(
		'title' => $item,
		'date' => date ("F d Y H:i:s", filectime(App::getBackupBase() . '/' . $item))
	);
}

\OCP\JSON::success(array('data' => $result));

exit();