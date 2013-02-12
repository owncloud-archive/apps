<?php

/**
 * ownCloud - ownpad_lite plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2013 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\ownpad_lite;

\OCP\JSON::checkLoggedIn();
\OCP\JSON::callCheck();

$search = UrlParam::post(UrlParam::SHARE_SEARCH);
\OCP\JSON::success(array(
	'data' => Contacts::search($search)
));
exit();