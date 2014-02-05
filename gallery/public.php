<?php
 /**
 * ownCloud
 *
 * @author Thomas Müller
 * @copyright 2013 Thomas Müller deepdiver@owncloud.com
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

\OCP\JSON::checkAppEnabled('gallery');

OCP\Util::addStyle('gallery', 'styles');
OCP\Util::addStyle('gallery', 'mobile');

if (isset($_GET['t'])) {
	$token = $_GET['t'];
	$linkItem = \OCP\Share::getShareByToken($token, false);
	if (is_array($linkItem) && isset($linkItem['uid_owner'])) {
		// seems to be a valid share
		$type = $linkItem['item_type'];
		$fileSource = $linkItem['file_source'];
		$shareOwner = $linkItem['uid_owner'];
		$path = null;
		$rootLinkItem = \OCP\Share::resolveReShare($linkItem);
		$fileOwner = $rootLinkItem['uid_owner'];
		$albumName = trim($linkItem['file_target'], '//');
		$ownerDisplayName = \OC_User::getDisplayName($fileOwner);

		// stupid copy and paste job
		if (isset($linkItem['share_with'])) {
			// Authenticate share_with
			$url = OCP\Util::linkToPublic('gallery') . '&t=' . $token;
			if (isset($_GET['file'])) {
				$url .= '&file=' . urlencode($_GET['file']);
			} else {
				if (isset($_GET['dir'])) {
					$url .= '&dir=' . urlencode($_GET['dir']);
				}
			}
			if (isset($_POST['password'])) {
				$password = $_POST['password'];
				if ($linkItem['share_type'] == OCP\Share::SHARE_TYPE_LINK) {
					// Check Password
					$forcePortable = (CRYPT_BLOWFISH != 1);
					$hasher = new PasswordHash(8, $forcePortable);
					if (!($hasher->CheckPassword($password.OC_Config::getValue('passwordsalt', ''),
						$linkItem['share_with']))) {
						OCP\Util::addStyle('files_sharing', 'authenticate');
						$tmpl = new OCP\Template('files_sharing', 'authenticate', 'guest');
						$tmpl->assign('URL', $url);
						$tmpl->assign('wrongpw', true);
						$tmpl->printPage();
						exit();
					} else {
						// Save item id in session for future requests
						\OC::$session->set('public_link_authenticated', $linkItem['id']);
					}
				} else {
					OCP\Util::writeLog('share', 'Unknown share type '.$linkItem['share_type']
						.' for share id '.$linkItem['id'], \OCP\Util::ERROR);
					header('HTTP/1.0 404 Not Found');
					$tmpl = new OCP\Template('', '404', 'guest');
					$tmpl->printPage();
					exit();
				}

			} else {
				// Check if item id is set in session
				if ( ! \OC::$session->exists('public_link_authenticated')
					|| \OC::$session->get('public_link_authenticated') !== $linkItem['id']
				) {
					// Prompt for password
					OCP\Util::addStyle('files_sharing', 'authenticate');
					$tmpl = new OCP\Template('files_sharing', 'authenticate', 'guest');
					$tmpl->assign('URL', $url);
					$tmpl->printPage();
					exit();
				}
			}
		}


		// render template
		$tmpl = new \OCP\Template('gallery', 'public', 'base');
		OCP\Util::addScript('gallery', 'gallery');
		OCP\Util::addScript('gallery', 'thumbnail');
		OCP\Util::addStyle('gallery', 'public');
		$tmpl->assign('token', $token);
		$tmpl->assign('displayName', $ownerDisplayName);
		$tmpl->assign('albumName', $albumName);

		$tmpl->printPage();
		exit;
	}
}

$tmpl = new OCP\Template('', '404', 'guest');
$tmpl->printPage();
