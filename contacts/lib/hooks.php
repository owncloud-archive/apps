<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
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

/**
 * The following signals are being emitted:
 *
 * OCA\Contacts\VCard::post_moveToAddressbook(array('aid' => $aid, 'id' => $id))
 * OCA\Contacts\VCard::pre_deleteVCard(array('aid' => $aid, 'id' => $id, 'uri' = $uri)); (NOTE: the values can be null depending on which method emits them)
 * OCA\Contacts\VCard::post_updateVCard($id)
 * OCA\Contacts\VCard::post_createVCard($newid)
 */

namespace OCA\Contacts;

use \Sabre\VObject;

/**
 * This class contains all hooks.
 */
class Hooks{
	/**
	 * @brief Add default Addressbook for a certain user
	 * @param paramters parameters from postCreateUser-Hook
	 * @return array
	 */
	static public function createUser($parameters) {
		Addressbook::addDefault($parameters['uid']);
		return true;
	}

	/**
	 * @brief Deletes all Addressbooks of a certain user
	 * @param paramters parameters from postDeleteUser-Hook
	 * @return array
	 */
	static public function deleteUser($parameters) {
		$addressbooks = Addressbook::all($parameters['uid']);

		foreach($addressbooks as $addressbook) {
			Addressbook::delete($addressbook['id']);
		}

		return true;
	}

	static public function getCalenderSources($parameters) {
		$base_url = \OCP\Util::linkTo('calendar', 'ajax/events.php').'?calendar_id=';
		foreach(Addressbook::all(\OCP\USER::getUser()) as $addressbook) {
			$parameters['sources'][]
				= array(
					'url' => $base_url.'birthday_'. $addressbook['id'],
					'backgroundColor' => '#cccccc',
					'borderColor' => '#888',
					'textColor' => 'black',
					'cache' => true,
					'editable' => false,
				);
		}
	}

	static public function getBirthdayEvents($parameters) {
		$name = $parameters['calendar_id'];
		if (strpos($name, 'birthday_') != 0) {
			return;
		}
		$info = explode('_', $name);
		$aid = $info[1];
		Addressbook::find($aid);
		foreach(VCard::all($aid) as $contact) {
			try {
				$vcard = VObject\Reader::read($contact['carddata']);
			} catch (Exception $e) {
				continue;
			}
			$birthday = $vcard->BDAY;
			if ((string)$birthday) {
				$title = str_replace('{name}',
					$vcard->FN,
					App::$l10n->t('{name}\'s Birthday'));
				
				$date = new \DateTime($birthday);
				$vevent = VObject\Component::create('VEVENT');
				//$vevent->setDateTime('LAST-MODIFIED', new DateTime($vcard->REV));
				$vevent->add('DTSTART');
				$vevent->DTSTART->setDateTime($date,
					VObject\Property\DateTime::DATE);
				$vevent->add('DURATION', 'P1D');
				$vevent->{'UID'} = substr(md5(rand().time()), 0, 10);
				// DESCRIPTION?
				$vevent->{'RRULE'} = 'FREQ=YEARLY';
				$vevent->{'SUMMARY'} = $title;
				$parameters['events'][] = array(
					'id' => 0,//$card['id'],
					'vevent' => $vevent,
					'repeating' => true,
					'summary' => $title,
					'calendardata' => "BEGIN:VCALENDAR\nVERSION:2.0\n"
						. "PRODID:ownCloud Contacts "
						. \OCP\App::getAppVersion('contacts') . "\n"
						. $vevent->serialize() .  "END:VCALENDAR"
					);
			}
		}
	}
}
