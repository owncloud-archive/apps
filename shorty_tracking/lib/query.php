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
 * @file lib/query.php
 * Static catalog of sql queries
 * @author Christian Reiner
 */

/**
 * @class OC_ShortyTracking_Query
 * @brief Static catalog of sql queries
 * These query templates are referenced by a OC_Shorty_Query::URL_...
 * They have to be prapared by adding an array of parameters
 * @access public
 * @author Christian Reiner
 */

// sql officially requires some reserved words to be quoted when used as identifiers (here the column name 'user')
// we define a different set of queries depending on the database engine used
switch ( OCP\Config::getSystemValue('dbtype') )
{
	case 'pgsql':
		class OC_ShortyTracking_Query
		{
			const CLICK_RECORD                = "INSERT INTO *PREFIX*shorty_tracking (shorty,time,address,host,\"user\",result) VALUES (:shorty,:time,:address,:host,:user,:result)";
			const CLICK_LIST_START            = "SELECT id,time,address,host,\"user\",result FROM *PREFIX*shorty_tracking WHERE shorty=:shorty ORDER BY id desc LIMIT :limit";
			const CLICK_LIST_CHUNK            = "SELECT id,time,address,host,\"user\",result FROM *PREFIX*shorty_tracking WHERE shorty=:shorty AND id<:offset ORDER BY id desc LIMIT :limit";
			const CLICK_LIST_STATS            = "SELECT count(*) AS length,MIN(time) AS earliest,MAX(time) AS latest,MIN(id) as first FROM *PREFIX*shorty_tracking WHERE shorty=:shorty";
			const CLICK_WIPE                  = "DELETE FROM *PREFIX*shorty_tracking ..... all clicks where entry in *PREFIX*shorty does NOT exist any more";
			const QUERY_TRACKING_SINGLE_USAGE = "SELECT s.*,count(t.id) AS usage,min(t.time) as first,max(t.time) as last FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) WHERE s.id=:shorty GROUP BY s.id";
			const QUERY_TRACKING_SINGLE_LIST  = "SELECT s.*,t.* FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) WHERE s.id=:shorty";
			const QUERY_TRACKING_TOTAL_USAGE  = "SELECT s.*,count(t.id) AS usage,min(t.time) as first,max(t.time) as last FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) GROUP BY s.id ORDER BY :sort";
			const QUERY_TRACKING_TOTAL_LIST   = "SELECT s.*,t.* FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) ORDER BY :sort";

		} // class OC_ShortyTracking_Query
		break;

	default:
		class OC_ShortyTracking_Query
		{
			const CLICK_RECORD                = "INSERT INTO *PREFIX*shorty_tracking (shorty,time,address,host,user,result) VALUES (:shorty,:time,:address,:host,:user,:result)";
			const CLICK_LIST_START            = "SELECT id,time,address,host,user,result FROM *PREFIX*shorty_tracking WHERE shorty=:shorty ORDER BY id desc LIMIT :limit";
			const CLICK_LIST_CHUNK            = "SELECT id,time,address,host,user,result FROM *PREFIX*shorty_tracking WHERE shorty=:shorty AND id<:offset ORDER BY id desc LIMIT :limit";
			const CLICK_LIST_STATS            = "SELECT count(*) AS length,MIN(time) AS earliest,MAX(time) AS latest,MIN(id) as first FROM *PREFIX*shorty_tracking WHERE shorty=:shorty";
			const CLICK_WIPE                  = "DELETE FROM *PREFIX*shorty_tracking ..... all clicks where entry in *PREFIX*shorty does NOT exist any more";
			const QUERY_TRACKING_SINGLE_USAGE = "SELECT s.*,count(t.id) AS usage,min(t.time) as first,max(t.time) as last FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) WHERE s.id=:shorty GROUP BY s.id";
			const QUERY_TRACKING_SINGLE_LIST  = "SELECT s.*,t.* FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) WHERE s.id=:shorty";
			const QUERY_TRACKING_TOTAL_USAGE  = "SELECT s.*,count(t.id) AS usage,min(t.time) as first,max(t.time) as last FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) GROUP BY s.id ORDER BY :sort";
			const QUERY_TRACKING_TOTAL_LIST   = "SELECT s.*,t.* FROM oc_shorty s LEFT JOIN oc_shorty_tracking t ON (s.id=t.shorty) ORDER BY :sort";

		} // class OC_ShortyTracking_Query

} // switch
?>
