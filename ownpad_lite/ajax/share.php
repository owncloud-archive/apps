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

$source = UrlParam::post(UrlParam::SHARE_WHAT);
$recipient = UrlParam::post(UrlParam::SHARE_WITH);

//TODO: Translate!
if (0 && $source && $recipient){
	$emailTo = $recipient;
	$nameTo = '';
	$subject = 'Document was shared';
	$mailtext = ' shared ' . $source . ' with you.';
	$fromaddress = '';
	$fromname = '';
	\OC_Mail::send(
		$emailTo, $nameTo, $subject, $mailtext, $fromaddress, $fromname
	);

}

\OCP\JSON::success(array());
exit();