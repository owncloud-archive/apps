<?php
/**
 * ownCloud - VCard component
 *
 * This component represents the BEGIN:VCARD and END:VCARD found in every
 * vcard.
 *
 * @author Thomas Tanghus
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
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

namespace OCA\Contacts\VObject;

use OCA\Contacts;
use Sabre\VObject;

/**
 * This class overrides \Sabre\VObject\Component\VCard::validate() to be add
 * to import partially invalid vCards by ignoring invalid lines and to
 * validate and upgrade using ....
*/
class VCard extends VObject\Component\VCard {

	/**
	* The following constants are used by the validate() method.
	*/
    const REPAIR = 1;
	const UPGRADE = 2;

	/**
	* VCards with version 2.1, 3.0 and 4.0 are found.
	*
	* If the VCARD doesn't know its version, 3.0 is assumed and if
	* option UPGRADE is given it will be upgraded to version 3.0.
	*/
	const DEFAULT_VERSION = '3.0';

	/**
	* @brief Format property TYPE parameters for upgrading from v. 2.1
	* @param $property Reference to a \Sabre\VObject\Property.
	* In version 2.1 e.g. a phone can be formatted like: TEL;HOME;CELL:123456789
	* This has to be changed to either TEL;TYPE=HOME,CELL:123456789 or TEL;TYPE=HOME;TYPE=CELL:123456789 - both are valid.
	*/
	protected function formatPropertyTypes(&$property) {
		foreach($property->parameters as $key=>&$parameter) {
			$types = Contacts\Utils\Properties::getTypesForProperty($property->name);
			if(is_array($types) && in_array(strtoupper($parameter->name), array_keys($types))
				|| strtoupper($parameter->name) == 'PREF') {
				unset($property->parameters[$key]);
				$property->add('TYPE', $parameter->name);
			}
		}
	}

	/**
	* @brief Decode properties for upgrading from v. 2.1
	* @param $property Reference to a \Sabre\VObject\Property.
	* The only encoding allowed in version 3.0 is 'b' for binary. All encoded strings
	* must therefore be decoded and the parameters removed.
	*/
	protected function decodeProperty(&$property) {
		// Check out for encoded string and decode them :-[
		foreach($property->parameters as $key=>&$parameter) {
			if(strtoupper($parameter->name) == 'ENCODING') {
				if(strtoupper($parameter->value) == 'QUOTED-PRINTABLE') {
					// Decode quoted-printable and strip any control chars
					// except \n and \r
					$property->value = str_replace(
						"\r\n", "\n",
						VObject\StringUtil::convertToUTF8(
							quoted_printable_decode($property->value)
						)
					);
					unset($property->parameters[$key]);
				} else if(strtoupper($parameter->value) == 'BASE64') {
					$parameter->value = 'b';
				}
			} elseif(strtoupper($parameter->name) == 'CHARSET') {
				unset($property->parameters[$key]);
			}
		}
	}

	/**
	* Validates the node for correctness.
	*
	* The following options are supported:
	*   - VCard::REPAIR - If something is broken, and automatic repair may
	*                    be attempted.
	*   - VCard::UPGRADE - If needed the vCard will be upgraded to version 3.0.
	*
	* An array is returned with warnings.
	*
	* Every item in the array has the following properties:
	*    * level - (number between 1 and 3 with severity information)
	*    * message - (human readable message)
	*    * node - (reference to the offending node)
	*
	* @param int $options
	* @return array
	*/
	public function validate($options = 0) {

		$warnings = array();

		$version = $this->select('VERSION');
		if (count($version) !== 1) {
			$warnings[] = array(
				'level' => 1,
				'message' => 'The VERSION property must appear in the VCARD component exactly 1 time',
				'node' => $this,
			);
			if ($options & self::REPAIR) {
				$this->VERSION = self::DEFAULT_VERSION;
				if (!$options & self::UPGRADE) {
					$options |= self::UPGRADE;
				}
			}
		} else {
			$version = (string)$this->VERSION;
			if ($version!=='2.1' && $version!=='3.0' && $version!=='4.0') {
				$warnings[] = array(
					'level' => 1,
					'message' => 'Only vcard version 4.0 (RFC6350), version 3.0 (RFC2426) or version 2.1 (icm-vcard-2.1) are supported.',
					'node' => $this,
				);
				if ($options & self::REPAIR) {
					$this->VERSION = self::DEFAULT_VERSION;
					if (!$options & self::UPGRADE) {
						$options |= self::UPGRADE;
					}
				}
			}

		}
		$fn = $this->select('FN');
		if (count($fn) !== 1) {
			$warnings[] = array(
				'level' => 1,
				'message' => 'The FN property must appear in the VCARD component exactly 1 time',
				'node' => $this,
			);
			if (($options & self::REPAIR) && count($fn) === 0) {
				// We're going to try to see if we can use the contents of the
				// N property.
				if (isset($this->N)) {
					$value = explode(';', (string)$this->N);
					if (isset($value[1]) && $value[1]) {
						$this->FN = $value[1] . ' ' . $value[0];
					} else {
						$this->FN = $value[0];
					}
				// Otherwise, the ORG property may work
				} elseif (isset($this->ORG)) {
					$this->FN = (string)$this->ORG;
				} elseif (isset($this->EMAIL)) {
					$this->FN = (string)$this->EMAIL;
				}

			}
		}

		$n = $this->select('N');
		if (count($n) !== 1) {
			$warnings[] = array(
				'level' => 1,
				'message' => 'The N property must appear in the VCARD component exactly 1 time',
				'node' => $this,
			);
			// TODO: Make a better effort parsing FN.
			if (($options & self::REPAIR) && count($n) === 0) {
				// Take 2 first name parts of 'FN' and reverse.
				$slice = array_reverse(array_slice(explode(' ', (string)$this->FN), 0, 2));
				if(count($slice) < 2) { // If not enought, add one more...
					$slice[] = "";
				}
				$this->N = implode(';', $slice).';;;';
			}
		}

		if (!isset($this->UID)) {
			$warnings[] = array(
				'level' => 1,
				'message' => 'Every vCard must have a UID',
				'node' => $this,
			);
			if ($options & self::REPAIR) {
				$this->UID = substr(md5(rand().time()), 0, 10);
			}
		}

		if ($options & self::UPGRADE) {
			$this->VERSION = self::DEFAULT_VERSION;
			foreach($this->children as &$property) {
				$this->decodeProperty($property);
				$this->formatPropertyTypes($property);
				//\OCP\Util::writeLog('contacts', __METHOD__.' upgrade: '.$property->name, \OCP\Util::DEBUG);
				switch((string)$property->name) {
					case 'LOGO':
					case 'SOUND':
					case 'PHOTO':
						if(isset($property['TYPE']) && strpos((string)$property['TYPE'], '/') === false) {
							$property['TYPE'] = 'image/' . strtolower($property['TYPE']);
						}
				}
			}
		}

		return array_merge(
			parent::validate($options),
			$warnings
		);

	}
}