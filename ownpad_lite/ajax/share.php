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

$status = true;
$recipients = UrlParam::post(UrlParam::SHARE_WITH);
$source = UrlParam::post(UrlParam::SHARE_WHAT);

if ($source && $recipients) {
	$currentUser = \OCP\User::getUser();
	$nameFrom = \OCP\User::getDisplayName($currentUser);

	$subject = App::$l10n->t('Document was shared');
	$message = App::$l10n->t('User %s shared quick document %s with you.', array($nameFrom, $source));

	$pattern = '/(.*)\s+<(.*)>$/';
	$recipientList = array(
		'name' => array(),
		'email' => array(),
	);
	$sendTo = explode(',', $recipients);
	foreach($sendTo as $recipient) {
		if (preg_match_all($pattern, $recipient, $matches)) {
		        // We have 'John Doe <email@example.org>'
			$recipientList['name'][] = $matches[1][0];
			$recipientList['email'][] = $matches[2][0];
		} else {
			// Name is unknown, we have  email@example.org
			$recipientList['name'][] = '';
			$recipientList['email'][] = $recipient;
		}
	}
	//We only use the first recipient atm. (OC_Mail doesn't support multiple CC)
	$nameTo = array_shift($recipientList['name']);
	$emailTo = array_shift($recipientList['email']);

	try {
		$emailFrom = \OCP\Util::getDefaultEmailAddress('noreply');
		\OCP\Util::sendMail(
			$emailTo, $nameTo, $subject, $message, $emailFrom, $nameFrom
		);
	} catch (Exception $e) {
		$status = false;
	}
} else {
	$status = false;
}

if ($status) {
	\OCP\JSON::success(array());
} else {
	\OCP\JSON::error(array());
}
exit();