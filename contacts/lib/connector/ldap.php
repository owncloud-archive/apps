<?php
/**
 * ownCloud - Addressbook LDAP
 *
 * @author Nicolas Mora
 * @copyright 2013 Nicolas Mora mail@babelouest.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation
 * version 3 of the License
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
 
namespace OCA\Contacts\Connector;
use OCA\Contacts\VObject\VCard;
use Sabre\VObject\Component;

class LDAP {

	public function __construct($xml_config) {
		try {
			//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.', setting xml config', \OCP\Util::DEBUG);
			$this->config_content = new \SimpleXMLElement($xml_config);
		} catch (Exception $e) {
			\OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.', error in setting xml config', \OCP\Util::DEBUG);
		}
	}

	/**
	* @brief transform a ldap entry into an OC_VCard object
	*  for each ldap entry which is like "property: value"
	*  to a VCard entry which is like "PROPERTY[;PARAMETER=param]:value"
	* @param array $ldap_entry
	* @return OC_VCard
	*/
	public function ldapToVCard($ldap_entry) {
		$vcard = Component::create('VCARD');
		$vcard->UID = htmlentities($ldap_entry['dn']);
		//$vcard->add('X-CreateTimestamp', $ldap_entry['createtimestamp'][0]);
		$vcard->REV = $ldap_entry['modifytimestamp'][0];
		for ($i=0; $i<$ldap_entry["count"]; $i++) {
			// ldap property name : $ldap_entry[$i]
			$l_property = $ldap_entry[$i];
			for ($j=0;$j<$ldap_entry[$l_property]["count"];$j++) {
				// What to do :
				// convert the ldap property into vcard property, type and position (if needed)
				// $v_params format: array('property' => property, 'type' => array(types), 'position' => position)
				$v_params = $this->getVCardProperty($l_property);

				for ($k=0; $k<count($v_params); $k++) {
					//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.', vcard property : '.$ldap_entry[$i].' => '.$v_params[$k]['property'], \OCP\Util::DEBUG);
					// Checks if a same kind of property already exists in the VCard (property and parameters)
					// if so, sets a property variable with the current data
					// else, creates a property variable
					$v_property = $this->getOrCreateVCardProperty($vcard, $v_params[$k], $j);

					// modify the property with the new data
					if (strcasecmp($v_params[$k]['image'], 'true') == 0) {
									$this->updateVCardImageProperty($v_property, $ldap_entry[$l_property][$j], $vcard->VERSION);
					} else {
						$this->updateVCardProperty($v_property, $ldap_entry[$l_property][$j], $v_params[$k]['position']);
					}
				}
			}
		}
		//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' vcard is '.$vcard->serialize(), \OCP\Util::DEBUG);
		$vcard->validate(VCard::REPAIR);
		return $vcard;
	}

	/**
	* @brief returns the vcard property corresponding to the ldif parameter
	* creates the property if it doesn't exists yet
	* @param VCard $vcard reference to the vcard to get or create the properties with
	* @param string $v_param the parameter the find
	* @param int $index the position of the property in the vcard to find
	*/
	public function getOrCreateVCardProperty(&$vcard, $v_param, $index) {

		// looking for one
		//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' entering '.$vcard->serialize(), \OCP\Util::DEBUG);
		$properties = $vcard->select($v_param['property']);
		$counter = 0;
		foreach ($properties as $property) {
			if ($v_param['type'] == null) {
				//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' property '.$v_param['type'].' found', \OCP\Util::DEBUG);
				return $property;
			}
			foreach ($property->parameters as $parameter) {
				//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' parameter '.$parameter->value.' <> '.$v_param['type'], \OCP\Util::DEBUG);
				if (!strcmp($parameter->value, $v_param['type'])) {
					//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' parameter '.$parameter->value.' found', \OCP\Util::DEBUG);
					if ($counter==$index) {
						return $property;
					}
					$counter++;
				}
			}
		}

		// Property not found, creating one
		//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.', create one '.$v_param['property'].';TYPE='.$v_param['type'], \OCP\Util::DEBUG);
		$line = count($vcard->children) - 1;
		$property = \Sabre\VObject\Property::create($v_param['property']);
		$vcard->add($property);
		if ($v_param['type']!=null) {
			//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.', creating one '.$v_param['property'].';TYPE='.$v_param['type'], \OCP\Util::DEBUG);
			//error_log('ldap_vcard_connector'.__METHOD__.', creating one '.$v_param['property'].';TYPE='.$v_param['type']);
			$property->parameters[] = new  \Sabre\VObject\Parameter('TYPE', ''.$v_param['type']);
			switch ($v_param['property']) {
				case "ADR":
					//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.', we have an address '.$v_param['property'].';TYPE='.$v_param['type'], \OCP\Util::DEBUG);
					$property->setValue(";;;;;;");
					break;
				case "FN":
					$property->setValue(";;;;");
					break;
			}
		}
		//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' exiting '.$vcard->serialize(), \OCP\Util::DEBUG);
		return $property;
	}

	/**
	* @brief modifies a vcard property array with the ldap_entry given in parameter at the given position
	*/
	public function updateVCardProperty(&$v_property, $ldap_entry, $position=null) {
		for ($i=0; $i<count($v_property); $i++) {
			if ($position != null) {
				$v_array = explode(";", $v_property[$i]);
				//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' v_array before '.print_r($v_array, true), \OCP\Util::DEBUG);
				//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' '.$v_property[$i].' size '.count($v_array).' pos '.$position, \OCP\Util::DEBUG);
				$v_array[intval($position)] = $ldap_entry;
				//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' setting '.$ldap_entry.' at position '.$position, \OCP\Util::DEBUG);
				//OCP\Util::writeLog('ldap_vcard_connector', __METHOD__.' v_array then '.print_r($v_array, true), \OCP\Util::DEBUG);
				$v_property[$i]->setValue(implode(";", $v_array));
			} else {
				$v_property[$i]->setValue($ldap_entry);
			}
		}
	}
	
	/**
	* @brief modifies a vcard property array with the ldap_entry given in parameter at the given position
	*/
	public function updateVCardImageProperty(&$v_property, $ldap_entry, $version) {
		for ($i=0; $i<count($v_property); $i++) {
			$image = new \OC_Image();
			$image->loadFromData($ldap_entry);
			if (strcmp($version, '4.0') == 0) {
				$type = $image->mimeType();
			} else {
				$arrayType = explode('/', $image->mimeType());
				$type = strtoupper(array_pop($arrayType));
			}
			$v_property[$i]->add('ENCODING', 'b');
			$v_property[$i]->add('TYPE', $type);
			$v_property[$i]->setValue($image->__toString());
		}
	}

	/**
	* @brief gets the vcard property values from an ldif entry name
	* @param $l_property the ldif property name
	* @return array('property' => property, 'type' => type, 'position' => position)
	*/
	public function getVCardProperty($l_property) {
		$properties = array();
		foreach ($this->config_content->ldap_entries->ldif_entry as $ldif_entry) {
			if ($l_property == $ldif_entry['name']) {
				// $ldif_entry['name'] is the right config xml
				foreach ($ldif_entry->vcard_entry as $vcard_entry) {
					$type=isset($vcard_entry['type'])?$vcard_entry['type']:"";
					$position=isset($vcard_entry['position'])?$vcard_entry['position']:"";
					$image=isset($vcard_entry['image'])?$vcard_entry['image']:"";
					$properties[] = Array('property' => $vcard_entry['property'], 'type' => $type, 'position' => $position, 'image' => $image);
				}
			}
		}
		return $properties;
	}

	/**
	* @brief transform a vcard into a ldap entry
	* @param array $vcard
	* @return array
	*/
	public function VCardToLdap($vcard_entry) {
	}

	/**
	* @brief transform a vcard into a ldap entry
	* @param array $vcard
	* @return array
	*/
	public function getLdapEntry($vcard_entry_unit, $type_entry=null) {
	}

	/**
	* @brief returns all the ldap entries managed
	* @return array
	*/
	public function getLdapEntries() {
		$to_return = array('createtimestamp','modifytimestamp');
		foreach ($this->config_content->ldap_entries[0]->ldif_entry as $ldif_entry) {
			$to_return[] = $ldif_entry['name'];
		}
		return $to_return;
	}

}

?>
