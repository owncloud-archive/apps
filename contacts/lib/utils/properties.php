<?php
/**
 * ownCloud - Interface for PIM object
 *
 * @author Thomas Tanghus
 * @copyright 2013 Thomas Tanghus (thomas@tanghus.net)
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

namespace OCA\Contacts\Utils;

Properties::$l10n = \OC_L10N::get('contacts');

Class Properties {

	private static $deleteindexstmt;
	private static $updateindexstmt;
	protected static $cardsTableName = '*PREFIX*contacts_cards';
	protected static $indexTableName = '*PREFIX*contacts_cards_properties';

	/**
	 * @brief language object for calendar app
	 *
	 * @var OC_L10N
	 */
	public static $l10n;

	/**
	 * Properties there can be more than one of.
	 *
	 * @var array
	 */
	public static $multi_properties = array('EMAIL', 'TEL', 'IMPP', 'ADR', 'URL');

	/**
	 * Properties to index.
	 *
	 * @var array
	 */
	public static $index_properties = array(
		'BDAY', 'UID', 'N', 'FN', 'TITLE', 'ROLE', 'NOTE', 'NICKNAME',
		'ORG', 'CATEGORIES', 'EMAIL', 'TEL', 'IMPP', 'ADR', 'URL', 'GEO', 'PHOTO');

	/**
	 * Get options for IMPP properties
	 * @param string $im
	 * @return array of vcard prop => label
	 */
	public static function getIMOptions($im = null) {
		$l10n = self::$l10n;
		$ims = array(
				'jabber' => array(
					'displayname' => (string)$l10n->t('Jabber'),
					'xname' => 'X-JABBER',
					'protocol' => 'xmpp',
				),
				'aim' => array(
					'displayname' => (string)$l10n->t('AIM'),
					'xname' => 'X-AIM',
					'protocol' => 'aim',
				),
				'msn' => array(
					'displayname' => (string)$l10n->t('MSN'),
					'xname' => 'X-MSN',
					'protocol' => 'msn',
				),
				'twitter' => array(
					'displayname' => (string)$l10n->t('Twitter'),
					'xname' => 'X-TWITTER',
					'protocol' => 'twitter',
				),
				'googletalk' => array(
					'displayname' => (string)$l10n->t('GoogleTalk'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'facebook' => array(
					'displayname' => (string)$l10n->t('Facebook'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'xmpp' => array(
					'displayname' => (string)$l10n->t('XMPP'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'icq' => array(
					'displayname' => (string)$l10n->t('ICQ'),
					'xname' => 'X-ICQ',
					'protocol' => 'icq',
				),
				'yahoo' => array(
					'displayname' => (string)$l10n->t('Yahoo'),
					'xname' => 'X-YAHOO',
					'protocol' => 'ymsgr',
				),
				'skype' => array(
					'displayname' => (string)$l10n->t('Skype'),
					'xname' => 'X-SKYPE',
					'protocol' => 'skype',
				),
				'qq' => array(
					'displayname' => (string)$l10n->t('QQ'),
					'xname' => 'X-SKYPE',
					'protocol' => 'x-apple',
				),
				'gadugadu' => array(
					'displayname' => (string)$l10n->t('GaduGadu'),
					'xname' => 'X-SKYPE',
					'protocol' => 'x-apple',
				),
		);
		if(is_null($im)) {
			return $ims;
		} else {
			$ims['ymsgr'] = $ims['yahoo'];
			$ims['gtalk'] = $ims['googletalk'];
			return isset($ims[$im]) ? $ims[$im] : null;
		}
	}

	/**
	 * Get standard set of TYPE values for different properties.
	 *
	 * @param string $prop
	 * @return array Type values for property $prop
	 */
	public static function getTypesForProperty($prop) {
		$l = self::$l10n;
		switch($prop) {
			case 'LABEL':
			case 'ADR':
			case 'IMPP':
				return array(
					'WORK' => (string)$l->t('Work'),
					'HOME' => (string)$l->t('Home'),
					'OTHER' => (string)$l->t('Other'),
				);
			case 'TEL':
				return array(
					'HOME'  =>  (string)$l->t('Home'),
					'CELL'  =>  (string)$l->t('Mobile'),
					'WORK'  =>  (string)$l->t('Work'),
					'TEXT'  =>  (string)$l->t('Text'),
					'VOICE' =>  (string)$l->t('Voice'),
					'MSG'   =>  (string)$l->t('Message'),
					'FAX'   =>  (string)$l->t('Fax'),
					'VIDEO' =>  (string)$l->t('Video'),
					'PAGER' =>  (string)$l->t('Pager'),
					'OTHER' =>  (string)$l->t('Other'),
				);
			case 'EMAIL':
				return array(
					'WORK' => (string)$l->t('Work'),
					'HOME' => (string)$l->t('Home'),
					'INTERNET' => (string)$l->t('Internet'),
					'OTHER' =>  (string)$l->t('Other'),
				);
		}
	}

	/**
	 * @brief returns the default categories of ownCloud
	 * @return (array) $categories
	 */
	public static function getDefaultCategories() {
		$l10n = self::$l10n;
		return array(
			(string)$l10n->t('Friends'),
			(string)$l10n->t('Family'),
			(string)$l10n->t('Work'),
			(string)$l10n->t('Other'),
		);
	}

	/**
	 * Update the contact property index.
	 *
	 * If vcard is null the properties for that contact will be purged.
	 * If it is a valid object the old properties will first be purged
	 * and the current properties indexed.
	 *
	 * @param string $contactid
	 * @param \OCA\VObject\VCard|null $vcard
	 */
	public static function updateIndex($contactid, $vcard = null) {
		if(!isset(self::$deleteindexstmt)) {
			self::$deleteindexstmt
				= \OCP\DB::prepare('DELETE FROM `' . self::$indexTableName . '`'
					. ' WHERE `contactid` = ?');
		}
		try {
			self::$deleteindexstmt->execute(array($contactid));
		} catch(\Exception $e) {
			\OCP\Util::writeLog('contacts', __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog('contacts', __METHOD__.', id: '
				. $id, \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'There was an error deleting properties for this contact.'
				)
			);
		}

		if(is_null($vcard)) {
			return;
		}

		if(!isset(self::$updateindexstmt)) {
			self::$updateindexstmt = \OCP\DB::prepare( 'INSERT INTO `' . self::$indexTableName . '` '
				. '(`userid`, `contactid`,`name`,`value`,`preferred`) VALUES(?,?,?,?,?)' );
		}
		foreach($vcard->children as $property) {
			if(!in_array($property->name, self::$index_properties)) {
				continue;
			}
			$preferred = 0;
			foreach($property->parameters as $parameter) {
				if($parameter->name == 'TYPE' && strtoupper($parameter->value) == 'PREF') {
					$preferred = 1;
					break;
				}
			}
			try {
				$result = self::$updateindexstmt->execute(
					array(
						\OCP\User::getUser(),
						$contactid,
						$property->name,
						$property->value,
						$preferred,
					)
				);
				if (\OC_DB::isError($result)) {
					\OCP\Util::writeLog('contacts', __METHOD__. 'DB error: '
						. \OC_DB::getErrorMessage($result), \OC_Log::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog('contacts', __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}
	}
}
