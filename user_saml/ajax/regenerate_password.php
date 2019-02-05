<?php

/**
*
*/

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('user_saml');
\OCP\JSON::callCheck();

$l = \OCP\Util::getL10N('user_saml');

$password = \OCP\Util::generateRandomBytes(8);
$username = \OC_User::getUser();

if (\OC_User::setPassword($username, $password)) {
    \OCP\JSON::success(array("data" => $password));
} else {
    \OC_JSON::error();
}


