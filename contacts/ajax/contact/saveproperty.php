<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus <thomas@tanghus.net>
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

require_once __DIR__.'/../loghandler.php';

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');
OCP\JSON::callCheck();
$id = isset($_POST['id'])?$_POST['id']:null;
$name = isset($_POST['name'])?$_POST['name']:null;
$value = isset($_POST['value'])?$_POST['value']:null;
$parameters = isset($_POST['parameters'])?$_POST['parameters']:null;
$checksum = isset($_POST['checksum'])?$_POST['checksum']:null;

$multi_properties = array('EMAIL', 'TEL', 'IMPP', 'ADR', 'URL');

if(!$name) {
	bailOut(OCA\Contacts\App::$l10n->t('element name is not set.'));
}
if(!$id) {
	bailOut(OCA\Contacts\App::$l10n->t('id is not set.'));
}
if(!$checksum && in_array($name, $multi_properties)) {
	bailOut(OCA\Contacts\App::$l10n->t('checksum is not set.'));
}
if(is_array($value)) {
	$value = array_map('strip_tags', $value);
	// NOTE: Important, otherwise the compound value will be
	// set in the order the fields appear in the form!
	ksort($value);
	//if($name == 'CATEGORIES') {
	//	$value = OCA\Contacts\VCard::escapeDelimiters($value, ',');
	//} else {
		$value = OCA\Contacts\VCard::escapeDelimiters($value, ';');
	//}
} else {
	$value = trim(strip_tags($value));
}

$vcard = OCA\Contacts\App::getContactVCard($id);
$property = null;

if(in_array($name, $multi_properties)) {
	if($checksum !== 'new') {
		$line = OCA\Contacts\App::getPropertyLineByChecksum($vcard, $checksum);
		if(is_null($line)) {
			bailOut(OCA\Contacts\App::$l10n->t(
				'Information about vCard is incorrect. Please reload the page: ').$checksum
			);
		}
		$property = $vcard->children[$line];
		$element = $property->name;

		if($element != $name) {
			bailOut(OCA\Contacts\App::$l10n->t(
				'Something went FUBAR. ').$name.' != '.$element
			);
		}
	} else {
		$element = $name;
		$property = $vcard->addProperty($name, $value);
	}
} else {
	$element = $name;
}

/* preprocessing value */
switch($element) {
	case 'BDAY':
		$date = New DateTime($value);
		$value = $date->format('Y-m-d');
		break;
	case 'FN':
		if(!$value) {
			// create a method thats returns an alternative for FN.
			//$value = getOtherValue();
		}
		break;
	case 'NOTE':
		$value = str_replace('\n', '\\n', $value);
		break;
	case 'EMAIL':
		$value = strtolower($value);
		break;
	case 'IMPP':
		if(is_null($parameters) || !isset($parameters['X-SERVICE-TYPE'])) {
			bailOut(OCA\Contacts\App::$l10n->t('Missing IM parameter.'));
		}
		$impp = OCA\Contacts\App::getIMOptions($parameters['X-SERVICE-TYPE']);
		if(is_null($impp)) {
			bailOut(OCA\Contacts\App::$l10n->t('Unknown IM: '.$parameters['X-SERVICE-TYPE']));
		}
		$value = $impp['protocol'] . ':' . $value;
		break;
}

// If empty remove the property
if(!$value) {
	if(in_array($name, $multi_properties)) {
		unset($vcard->children[$line]);
		$checksum = '';
	} else {
		unset($vcard->{$name});
	}
} else {
	/* setting value */
	switch($element) {
		case 'BDAY':
			$vcard->BDAY = $value;

			if(!isset($vcard->BDAY['VALUE'])) {
				$vcard->BDAY->add('VALUE', 'DATE');
			} else {
				$vcard->BDAY->VALUE = 'DATE';
			}
			break;
		case 'EMAIL':
		case 'TEL':
		case 'ADR':
		case 'IMPP':
		case 'URL':
			debug('Setting element: (EMAIL/TEL/ADR)'.$element);
			$property->setValue($value);
			// FIXME: Don't replace parameters, but update instead.
			$property->parameters = array();
			if(!is_null($parameters)) {
				debug('Setting parameters: '.$parameters);
				foreach($parameters as $key => $parameter) {
					debug('Adding parameter: '.$key);
					if(is_array($parameter)) {
						foreach($parameter as $val) {
							if(trim($val)) {
								debug('Adding parameter: '.$key.'=>'.$val);
								$property->add(new Sabre\VObject\Parameter(
									$key,
									strtoupper(strip_tags($val)))
								);
							}
						}
					} else {
						if(trim($parameter)) {
							$property->add(new Sabre\VObject\Parameter(
								$key,
								strtoupper(strip_tags($parameter)))
							);
						}
					}
				}
			}
			break;
		default:
			$vcard->setString($name, $value);
			break;
	}
	// Do checksum and be happy
	if(in_array($name, $multi_properties)) {
		$checksum = substr(md5($property->serialize()), 0, 8);
	}
}
//debug('New checksum: '.$checksum);
//$vcard->children[$line] = $property; ???
try {
	OCA\Contacts\VCard::edit($id, $vcard);
} catch(Exception $e) {
	bailOut($e->getMessage());
}

if(in_array($name, $multi_properties)) {
	OCP\JSON::success(array('data' => array(
		'line' => $line,
		'checksum' => $checksum,
		'oldchecksum' => $_POST['checksum'],
		'lastmodified' => OCA\Contacts\App::lastModified($vcard)->format('U'),
	)));
} else {
	OCP\JSON::success(array('data' => array(
		'lastmodified' => OCA\Contacts\App::lastModified($vcard)->format('U'),
	)));
}
