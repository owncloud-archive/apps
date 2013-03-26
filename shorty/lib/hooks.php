<?php
/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401
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
 * @class OC_Shorty_Hooks
 * @brief Static 'namespace' class for api hook population
 * ownCloud propagates to use static classes as namespaces instead of OOP.
 * This 'namespace' defines routines to populate hooks called by other parts of ownCloud
 * @access public
 * @author Christian Reiner
 */
class OC_Shorty_Hooks
{
	/**
	* @method OC_Shorty_Hooks::deleteUser
	* @brief Deletes all Shortys and preferences of a certain user
	* @param array paramters: Array of parameters from postDeleteUser-Hook
	* @return bool
	* @access public
	* @author Christian Reiner
	*/
	public static function deleteUser ( $parameters )
	{
		OCP\Util::writeLog ( 'shorty',sprintf("Wiping all Shortys belonging to user '%s'",$parameters['uid']), OCP\Util::INFO );
		$result = TRUE;
		$param  = array ( ':user' => $parameters['uid'] );
		// wipe preferences
		$query = OCP\DB::prepare ( OC_Shorty_Query::WIPE_PREFERENCES );
		if ( FALSE===$query->execute($param) )
			$result = FALSE;
		// wipe shortys
		$query = OCP\DB::prepare ( OC_Shorty_Query::WIPE_SHORTYS );
		if ( FALSE===$query->execute($param) )
			$result = FALSE;
		// allow further cleanups via registered hooks
		OC_Hook::emit( "OC_Shorty", "post_deleteUser", array("user"=>$param['user']) );
		// report completion success
		return $result;
	}

	/**
	* @method OC_Shorty_Hooks::requestActions
	* @brief Hook that requests any actions plugins may want to register
	* @return array: Array of descriptions of actions
	* @access public
	* @author Christian Reiner
	*/
	public static function requestActions ( )
	{
		OCP\Util::writeLog ( 'shorty', 'Requesting actions to be offered for Shortys by other apps', OCP\Util::DEBUG );
		$actions = array ( 'list'=>array(), 'shorty'=>array() );
		// we hand over a container by reference and expect any app registering into this hook to obey this structure:
		// ... for every action register a new element in the container
		// ... ... such element must be an array holding the entries tested below
		$container = array ( 'list'=>&$actions['list'], 'shorty'=>&$actions['shorty'] );
		OC_Hook::emit ( 'OC_Shorty', 'registerActions', $container );
		// validate and evaluate what was returned in the $container
		if ( ! is_array($container))
		{
			OCP\Util::writeLog ( 'shorty', 'Invalid reply from some app that registered into the registerAction hook, FIX THAT APP !', OCP\Util::WARN );
			return array();
		} // if
		foreach ( $container as $target )
		{
			if ( ! is_array($target) )
			{
				OCP\Util::writeLog ( 'shorty', 'Invalid reply structure from an app that registered into the registerAction hook, FIX THAT APP !', OCP\Util::WARN );
				break;
			}
			foreach ( $target as $action )
			{
				if (  ! is_array($action)
					|| ! array_key_exists('id',   $action) || ! is_string($action['id'])
					|| ! array_key_exists('name', $action) || ! is_string($action['name'])
					|| ! array_key_exists('icon', $action) || ! is_string($action['icon'])
					|| ( array_key_exists('call', $action) && ! is_string($action['call'] ) )
					|| ( array_key_exists('title',$action) && ! is_string($action['title']) )
					|| ( array_key_exists('alt',  $action) && ! is_string($action['alt']  ) ) )
				{
					OCP\Util::writeLog ( 'shorty', 'Invalid reply from an app that registered into the registerAction hook, FIX THAT APP !', OCP\Util::WARN );
					break;
				}
			} // foreach action
		} // foreach target
		return $actions;
	} // function requestActions

	/**
	* @method OC_Shorty_Hooks::requestIncludes
	* @brief Hook that requests any actions plugins may want to register
	* @return array: Array of descriptions of actions
	* @access public
	* @author Christian Reiner
	*/
	public static function requestIncludes ( )
	{
		OCP\Util::writeLog ( 'shorty', 'Requesting includes registered by other apps', OCP\Util::DEBUG );
		OC_Hook::emit ( 'OC_Shorty', 'registerIncludes', array() );
	} // function requestIncludes

	/**
	* @method OC_Shorty_Hooks::requestQueries
	* @brief Hook that requests any queries plugins may want to offer
	* @return array: Array of descriptions of queries
	* @access public
	* @author Christian Reiner
	*/
	public static function requestQueries ( )
	{
		OCP\Util::writeLog ( 'shorty', 'Requesting queries to be offered from other apps', OCP\Util::DEBUG );
		$queries = array ( 'list'=>array(), 'shorty'=>array() );
		// we hand over a container by reference and expect any app registering into this hook to obey this structure:
		// ... for every action register a new element in the container
		// ... ... such element must be an array holding the entries tested below
		$container = array ( 'list'=>&$queries['list'], 'shorty'=>&$queries['shorty'] );
		OC_Hook::emit ( 'OC_Shorty', 'registerQueries', $container );
		// validate and evaluate what was returned in the $container
		if ( ! is_array($container))
		{
			OCP\Util::writeLog ( 'shorty', 'Invalid reply from some app that registered into the registerQueries hook, FIX THAT APP !', OCP\Util::WARN );
			return array();
		} // if
		foreach ( $container as $aspect )
		{
			if ( ! is_array($aspect) )
			{
				OCP\Util::writeLog ( 'shorty', 'Invalid reply structure from an app that registered into the registerQueries hook, FIX THAT APP !', OCP\Util::WARN );
				break;
			}
			foreach ( $aspect as $query )
			{
				if (  ! is_array($query)
					|| ! array_key_exists('id',    $query) || ! is_string($query['id'])
					|| ! array_key_exists('query', $query) || ! is_string($query['query'])
					|| ! array_key_exists('param', $query) || ! is_array($query['param']) )
				{
					OCP\Util::writeLog ( 'shorty', 'Invalid reply from an app that registered into the registerQueries hook, FIX THAT APP !', OCP\Util::WARN );
					break;
				}
			} // foreach query
		} // foreach aspect
		return $queries;
	} // function requestQueries

	/**
	* @method OC_Shorty_Hooks::registerClicks
	* @brief Hook offering informations about each click relayed by this app
	* @access public
	* @author Christian Reiner
	*/
	public static function registerClick ( $shorty, $request, $result )
	{
		OCP\Util::writeLog ( 'shorty', sprintf("Registering click to shorty '%s'",$shorty['id']), OCP\Util::DEBUG );
		// add result to details describing this request (click), important for emitting the event further down
		$request['result'] = $result;
		// save click in the database
		$param = array (
			'id'     => $shorty['id'],
			'time'   => $request['time'],
		);
		$query = OCP\DB::prepare ( OC_Shorty_Query::URL_CLICK );
		$query->execute ( $param );

		// allow further processing IF hooks are registered
		OC_Hook::emit( 'OC_Shorty', 'registerClick', array('shorty'=>$shorty,'request'=>$request) );
	} // function registerClick

	/**
	* @method OC_Shorty_Hooks::registerQueries
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
				'id'    => 'shorty-list',
				'query' => OC_Shorty_Query::QUERY_SHORTY_LIST,
				'param' => array(':sort'),
			);
			$parameters['list'][] = array (
				'id'    => 'shorty-single',
				'query' => OC_Shorty_Query::QUERY_SHORTY_SINGLE,
				'param' => array(':id'),
			);
		}
		return TRUE;
	} // function registerQueries

} // class OC_Shorty_Hooks
?>
