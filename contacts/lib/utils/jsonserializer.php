<?php
/**
 * ownCloud - JSONSerializer
 *
 * @author Thomas Tanghus, Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
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

use OCA\Contacts\VObject;
use OCA\Contacts\Contact;

/**
 * This class serializes properties, components an
 * arrays of components into a format suitable for
 * passing to a JSON response.
 * TODO: Return jCard (almost) compliant data, but still omitting unneeded data.
 * http://tools.ietf.org/html/draft-kewisch-vcard-in-json-01
 */

class JSONSerializer {

	/**
	 * General method serialize method. Use this for arrays
	 * of contacts.
	 *
	 * @param Contact[] $input
	 * @return array
	 */
	 public static function serialize($input) {
		$response = array();
		if(is_array($input)) {
			foreach($input as $object) {
				if($object instanceof Contact) {
					\OCP\Util::writeLog('contacts', __METHOD__.' serializing: ' . print_r($object, true), \OCP\Util::DEBUG);
					$tmp = self::serializeContact($object);
					if($tmp !== null) {
						$response[] = $tmp;
					}
				} else {
					throw new \Exception(
						'Only arrays of OCA\\Contacts\\VObject\\VCard '
						. 'and Sabre\VObject\Property are accepted.'
					);
				}
			}
		} else {
			if($input instanceof VObject\VCard) {
				return self::serializeContact($input);
			} elseif($input instanceof Sabre\VObject\Property) {
				return self::serializeProperty($input);
			} else {
				throw new \Exception(
					'Only instances of OCA\\Contacts\\VObject\\VCard '
					. 'and Sabre\VObject\Property are accepted.'
				);
			}
		}
		return $response;
	 }

	/**
	 * @brief Data structure of vCard
	 * @param VObject\VCard $contact
	 * @return associative array|null
	 */
	public static function serializeContact(Contact $contact) {
		//\OCP\Util::writeLog('contacts', __METHOD__, \OCP\Util::DEBUG);

		if(!$contact->retrieve()) {
			\OCP\Util::writeLog('contacts', __METHOD__.' error reading: ' . print_r($contact, true), \OCP\Util::DEBUG);
			return null;
		}

		$details = array();

		foreach($contact->children() as $property) {
			//\OCP\Util::writeLog('contacts', __METHOD__.' property: '.$property->name, \OCP\Util::DEBUG);
			$pname = $property->name;
			$temp = self::serializeProperty($property);
			if(!is_null($temp)) {
				// Get Apple X-ABLabels
				if(isset($contact->{$property->group . '.X-ABLABEL'})) {
					$temp['label'] = $contact->{$property->group . '.X-ABLABEL'}->value;
					if($temp['label'] == '_$!<Other>!$_') {
						$temp['label'] = Properties::$l10n->t('Other');
					}
					if($temp['label'] == '_$!<HomePage>!$_') {
						$temp['label'] = Properties::$l10n->t('HomePage');
					}
				}
				if(array_key_exists($pname, $details)) {
					$details[$pname][] = $temp;
				}
				else{
					$details[$pname] = array($temp);
				}
			}
		}
		return array('data' =>$details, 'metadata' => $contact->getMetaData());
	}

	/**
	 * @brief Get data structure of property.
	 * @param \Sabre\VObject\Property $property
	 * @return associative array
	 *
	 * returns an associative array with
	 * ['name'] name of property
	 * ['value'] htmlspecialchars escaped value of property
	 * ['parameters'] associative array name=>value
	 * ['checksum'] checksum of whole property
	 * NOTE: $value is not escaped anymore. It shouldn't make any difference
	 * but we should look out for any problems.
	 */
	public static function serializeProperty(\Sabre\VObject\Property $property) {
		if(!in_array($property->name, Properties::$index_properties)) {
			return;
		}
		$value = $property->value;
		if($property->name == 'ADR' || $property->name == 'N' || $property->name == 'ORG' || $property->name == 'CATEGORIES') {
			$value = $property->getParts();
			$value = array_map('trim', $value);
		}
		elseif($property->name == 'BDAY') {
			if(strpos($value, '-') === false) {
				if(strlen($value) >= 8) {
					$value = substr($value, 0, 4).'-'.substr($value, 4, 2).'-'.substr($value, 6, 2);
				} else {
					return null; // Badly malformed :-(
				}
			}
		} elseif($property->name == 'PHOTO') {
			$value = true;
		}
		elseif($property->name == 'IMPP') {
			if(strpos($value, ':') !== false) {
				$value = explode(':', $value);
				$protocol = array_shift($value);
				if(!isset($property['X-SERVICE-TYPE'])) {
					$property['X-SERVICE-TYPE'] = strtoupper($protocol);
				}
				$value = implode('', $value);
			}
		}
		if(is_string($value)) {
			$value = strtr($value, array('\,' => ',', '\;' => ';'));
		}
		$temp = array(
			//'name' => $property->name,
			'value' => $value,
			'parameters' => array()
		);

		// This cuts around a 3rd off of the json response size.
		if(in_array($property->name, Properties::$multi_properties)) {
			$temp['checksum'] = substr(md5($property->serialize()), 0, 8);
		}
		foreach($property->parameters as $parameter) {
			// Faulty entries by kaddressbook
			// Actually TYPE=PREF is correct according to RFC 2426
			// but this way is more handy in the UI. Tanghus.
			if($parameter->name == 'TYPE' && strtoupper($parameter->value) == 'PREF') {
				$parameter->name = 'PREF';
				$parameter->value = '1';
			}
			// NOTE: Apparently Sabre_VObject_Reader can't always deal with value list parameters
			// like TYPE=HOME,CELL,VOICE. Tanghus.
			// TODO: Check if parameter is has commas and split + merge if so.
			if ($parameter->name == 'TYPE') {
				$pvalue = $parameter->value;
				if(is_string($pvalue) && strpos($pvalue, ',') !== false) {
					$pvalue = array_map('trim', explode(',', $pvalue));
				}
				$pvalue = is_array($pvalue) ? $pvalue : array($pvalue);
				if (isset($temp['parameters'][$parameter->name])) {
					$temp['parameters'][$parameter->name][] = \OCP\Util::sanitizeHTML($pvalue);
				}
				else {
					$temp['parameters'][$parameter->name] = \OCP\Util::sanitizeHTML($pvalue);
				}
			}
			else{
				$temp['parameters'][$parameter->name] = \OCP\Util::sanitizeHTML($parameter->value);
			}
		}
		return $temp;
	}
}
