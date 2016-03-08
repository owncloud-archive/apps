<?php
/**
* @package fluxx-compensator an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php?content=157091
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * @file ajax/preferences.php
 * @brief Ajax method to store one and query a personal preference
 * @author Christian Reiner
 */

$validPreferences = array(
	'fluxx-status-H'	=> array(
		'validate'  => create_function('$v','return in_array($v,array("hidden","shown"));'),
		'normalize' => create_function('$v,$d','return in_array($v,array("hidden","shown"))?$v:$d;') ),
	'fluxx-status-N'	=> array(
		'validate'  => create_function('$v','return in_array($v,array("hidden","shown"));'),
		'normalize' => create_function('$v,$d','return in_array($v,array("hidden","shown"))?$v:$d;') ),
	'fluxx-position-H'	=> array(
		'validate'  => create_function('$v','return is_numeric($v) && ($v>=0);'),
		'normalize' => create_function('$v,$d','return is_numeric($v) && ($v>=0)?floatval($v):floatval($d);') ),
	'fluxx-position-N'	=> array(
		'validate'  => create_function('$v','return is_numeric($v) && ($v>0);'),
		'normalize' => create_function('$v,$d','return is_numeric($v) && ($v>=0)?floatval($v):floatval($d);') ) );

//no apps or filesystem
$RUNTIME_NOSETUPFS = true;

// Sanity checks
OCP\JSON::callCheck ( );
OCP\JSON::checkLoggedIn ( );
OCP\JSON::checkAppEnabled ( 'fluxx_compensator' );

try
{
	// test for valid key
	switch ( $_SERVER['REQUEST_METHOD'] )
	{
		case 'POST':
			// check if a valid key has been specified
			if ( ! array_key_exists('key', $_POST) )
				throw new Exception ( "Missing key in ajax method." );
			if ( ! array_key_exists($_POST['key'], $validPreferences) )
				throw new Exception ( "Unknown key in ajax method." );
			// check for a valid value
			if ( ! array_key_exists('value', $_POST) )
				throw new Exception ( "Missing value in ajax method." );
			if ( ! $validPreferences[$_POST['key']]['validate']($_POST['value']) )
				throw new Exception ( "Unknown type of value in ajax method." );
			// normalize value
			$value = $validPreferences[$_POST['key']]['normalize']($_POST['value'],0);
			// store preference
			OCP\Config::setUserValue ( OCP\User::getUser(), 'fluxx_compensator', $_POST['key'], $value );
			// return success
			OCP\JSON::success ( array('value'=>$value) );
			break;

		case 'GET':
			// check if a valid key has been specified
			if ( ! array_key_exists('key', $_GET) )
				throw new Exception ( "Missing key in ajax method." );
			if ( ! array_key_exists($_GET['key'], $validPreferences) )
				throw new Exception ( "Unknown key in ajax method." );
			// check for a valid default value
			if ( ! array_key_exists('value', $_GET) )
				throw new Exception ( "Missing value in ajax method." );
			if ( ! $validPreferences[$_GET['key']]['validate']($_GET['value']) )
				throw new Exception ( "Unknown type of value in ajax method." );
			// retrieve value from database, normalize and return it
			$value = OCP\Config::getUserValue ( OCP\User::getUser(), 'fluxx_compensator', $_GET['key'] );
			$value = $validPreferences[$_GET['key']]['normalize']($value,$_GET['value']);
			OCP\JSON::success ( array('value'=>$value) );
			break;

		default:
			throw new Exception ( "Unexpected request method '%s'", $_SERVER['REQUEST_METHOD'] );
	} // switch

} catch ( Exception $e ) { OCP\JSON::error($e->getMessage()); }
?>
