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

class Contacts{
	public static function search($str){
		// The API is not active -> nothing to do
		if (!\OCP\Contacts::isEnabled() || strlen($str)<3) {
			return array();
		}

		$result = \OCP\Contacts::search($str, array('FN', 'EMAIL'));
		$receivers = array();
		foreach ($result as $r) {
			$id = $r['id'];
			$fn = $r['FN'];
			$email = $r['EMAIL'];
			if (!is_array($email)) {
				$email = array($email);
			}

			// loop through all email addresses of this contact
			foreach ($email as $e) {
				$displayName = $fn . " <$e>";
				$receivers[] = array(
					'id'    => $id,
					'label' => $displayName,
					'value' => $displayName);
			}
		}
		return $receivers;
	}
}
