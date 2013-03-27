<?php
/**
* @package shorty-tracking an ownCloud url shortener plugin addition
* @category internet
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty+Tracking?content=152473
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
 * @file lib/hooks.php
 * Static class providing routines to populate hooks called by other parts of ownCloud
 * @author Christian Reiner
 */

/**
 * @class OC_ShortyTracking_Hooks
 * @brief Static 'namespace' class for api hook population
 * ownCloud propagates to use static classes as namespaces instead of OOP.
 * This 'namespace' defines routines to populate hooks called by other parts of ownCloud
 * @access public
 * @author Christian Reiner
 */
class OC_ShortyTracking_Hooks
{
	/**
	* @method OC_ShortyTracking_Hooks::deleteShortyClicks
	* @brief Deletes all alien clicks, clicks that have no corresponding Shorty any more (deleted)
	* @param paramters (array) parameters from emitted signal
	* @return bool
	* @description
	* This is a cleanup routine. It removes all previously recorded clicks from the database table that
	* do not (any more) reference an existing Shorty. Such 'stale entries' typically arise when Shortys
	* get deleted, for example because of the deletion of a user account.
	* Instead of directly targeting recorded clicks to a specific Shorty identified by its ID instead
	* this routine takes a more general approach to simply wipe all stale entries. IN addition to being
	* less complex (no ID required) this also is more stable, since also leftovers from prior operations
	* are cleaned up.
	*/
	public static function deleteShortyClicks ( $parameters )
	{
		OCP\Util::writeLog ( 'shorty_tracking', 'Wiping all clicks without corresponding Shorty', OCP\Util::INFO );
		$result = TRUE;
		// wipe shorty clicks
		$query = OCP\DB::prepare ( OC_ShortyTracking_Query::CLICK_WIPE );
		if ( FALSE===$query->execute() )
			$result = FALSE;
		// report completion success
		return $result;
	} // function OC_ShortyTracking_Hooks::deleteShortyClicks

	/**
	* @method OC_ShortyTracking_Hooks::registerClick
	* @brief Records details of request clicks targeting existing Shortys
	* @param paramters (array) parameters from emitted signal
	* @return bool
	* @description
	* This routine accepts an associative array of attributes that describe a
	* request click to a single shorty. The speicificartion of those details
	* MUST follow a strict syntactical layout that describes as this:
	* two parameters must be present, called 'shorty' and 'request', both are again
	* associative arrays holding these string memebers:
	* shorty: id, ...
	* request: time, address, requester, result, ...
	*/
	public static function registerClick ( $parameters )
	{
		OCP\Util::writeLog ( 'shorty_tracking', sprintf("Recording single click to Shorty '%s' with result '%s'",
														$parameters['shorty']['id'],
														$parameters['request']['result']), OCP\Util::DEBUG );
		$param  = array (
			':shorty'    => $parameters['shorty']['id'],
			':time'      => $parameters['request']['time'],
			':address'   => $parameters['request']['address'],
			':host'      => $parameters['request']['host'],
			':user'      => $parameters['request']['user'],
			':result'    => $parameters['request']['result'],
		);
		$query = OCP\DB::prepare ( OC_ShortyTracking_Query::CLICK_RECORD );
		$query->execute ( $param );
		return TRUE;
	} // function OC_ShortyTracking_Hooks::registerClick

	/**
	* @method OC_ShortyTracking_Hooks::registerIncludes
	* @brief Registers additional includes required by this plugin
	* @param paramters (array) parameters from emitted signal
	* @return bool
	*/
	public static function registerIncludes ( $parameters )
	{
		OCP\Util::writeLog  ( 'shorty_tracking', 'Registering additional include files', OCP\Util::DEBUG );
		OCP\Util::addStyle  ( 'shorty_tracking',          'tracking' );
		OCP\Util::addScript ( 'shorty_tracking',          'tracking' );
		OCP\Util::addScript ( 'shorty_tracking/3rdparty', 'jquery.sparkline.min' );
		return TRUE;
	} // function OC_ShortyTracking_Hooks::registerIncludes

	/**
	* @method OC_ShortyTracking_Hooks::registerActions
	* @brief Registers additional actions as expected by the Shorty app
	* @param paramters (array) parameters from emitted signal
	* @return bool
	*/
	public static function registerActions ( $parameters )
	{
		OCP\Util::writeLog ( 'shorty_tracking', 'Registering additional Shorty actions', OCP\Util::DEBUG );
		if ( ! is_array($parameters) )
		{
			return FALSE;
		}
		if ( array_key_exists('list',$parameters) && is_array($parameters['list']) )
		{
			// action 'tracking-list' in list-of-shortys
			$parameters['list'][] = array (
				'id'    => 'shorty-action-clicks',
				'name'  => 'clicks',
				'icon'  => OCP\Util::imagePath('shorty_tracking','actions/hits.svg'),
				'call'  => 'OC.Shorty.Tracking.control',
				'title' => OC_ShortyTracking_L10n::t("List clicks"),
				'alt'   => OC_ShortyTracking_L10n::t("Clicks"),
			);
		}
		return TRUE;
	} // function OC_ShortyTracking_Hooks::registerActions

	/**
	* @method OC_ShortyTracking_Hooks::registerQueries
	* @brief Registers queries to be offered as expected by the Shorty app
	* @param paramters (array) parameters from emitted signal
	* @return bool
	*/
	public static function registerQueries ( $parameters )
	{
		OCP\Util::writeLog ( 'shorty_tracking', 'Registering additional queries to be offered', OCP\Util::DEBUG );
		if ( ! is_array($parameters) )
		{
			return FALSE;
		}
		if ( array_key_exists('list',$parameters) && is_array($parameters['list']) )
		{
			$parameters['list'][] = array (
				'id'    => 'tracking-single-usage',
				'query' => OC_ShortyTracking_Query::QUERY_TRACKING_SINGLE_USAGE,
				'param' => array(':shorty'),
			);
			$parameters['list'][] = array (
				'id'    => 'tracking-single-list',
				'query' => OC_ShortyTracking_Query::QUERY_TRACKING_SINGLE_LIST,
				'param' => array(':shorty'),
			);
			$parameters['list'][] = array (
				'id'    => 'tracking-total-usage',
				'query' => OC_ShortyTracking_Query::QUERY_TRACKING_TOTAL_USAGE,
				'param' => array(':sort'),
			);
			$parameters['list'][] = array (
				'id'    => 'tracking-total-list',
				'query' => OC_ShortyTracking_Query::QUERY_TRACKING_TOTAL_LIST,
				'param' => array(':sort'),
			);
		}
		return TRUE;
	} // function registerQueries

} // class OC_ShortyTracking_Hooks
?>
