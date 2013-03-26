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
 * @file qrcode.php
 * Generates qrcode barcodes coding a web reference (url) to the shorty
 * @access public
 * @author Christian Reiner
 */

// Session checks
OCP\App::checkAppEnabled ( 'shorty' );

$RUNTIME_NOSETUPFS = true;

require_once ( '3rdparty/php/phpqrcode.php' );

$query = NULL;
$param = array ( );
// we try to guess what the request indicates, it is expected to be one of these:
// - an alphanumerical ID referencing an existing Shorty in the database
// - a _source_ url stored inside an existing Shorty in the database
foreach ($_GET as $key=>$val) // in case there are unexpected, additional arguments like a timestamp added by some stupid proxy
{
	switch ($key)
	{
		default:
			// unrecognized key, we ignore it
			break;

		case 'id':
		case 'shorty':
			// this looks like the request refers to a Shortys ID, lets see if we know that one
			$id     = OC_Shorty_Type::req_argument($key,OC_Shorty_Type::ID,FALSE);
			$param  = array ( ':id' => OC_Shorty_Type::normalize($id,OC_Shorty_Type::ID) );
			$query  = OCP\DB::prepare ( OC_Shorty_Query::URL_BY_ID );
			break 2; // skip switch AND foreach, we have all details we need...
		case 'url':
		case 'uri':
		case 'ref':
		case 'source':
		case 'target':
			// this looks like the request refers to a full url, lets see if we know that one as a Shortys source
			$source = OC_Shorty_Type::req_argument($key,OC_Shorty_Type::URL,FALSE);
			$param  = array ( ':source' => OC_Shorty_Type::normalize($source,OC_Shorty_Type::URL) );
			$query  = OCP\DB::prepare ( OC_Shorty_Query::URL_BY_SOURCE );
			break 2; // skip switch AND foreach, we have all details we need...
	} // switch
} // foreach

// generate qrcode for the specified id/url, IF it exists and has not expired in the database
try
{
	if ( $query )
	{
		$result = $query->execute($param)->FetchAll();
		if ( FALSE===$result )
			throw new OC_Shorty_HttpException ( 500 );
		elseif ( ! is_array($result) )
			throw new OC_Shorty_HttpException ( 500 );
		elseif ( 0==sizeof($result) )
		{
			// no entry found => 404: Not Found
			throw new OC_Shorty_HttpException ( 404 );
		}
		elseif ( 1<sizeof($result) )
		{
			// multiple matches => 409: Conflict
			throw new OC_Shorty_HttpException ( 409 );
		}
		elseif ( (!array_key_exists(0,$result)) || (!is_array($result[0])) || (!array_key_exists('source',$result[0])) )
		{
			// invalid entry => 500: Internal Server Error
			throw new OC_Shorty_HttpException ( 500 );
		}
		elseif ( (!array_key_exists('source',$result[0])) || ('1'==$result[0]['expired']) )
		{
			// entry expired => 410: Gone
			throw new OC_Shorty_HttpException ( 410 );
		}
		// force download / storage of image
		header('Content-Type: image/png');
		if ( FALSE===strpos($_SERVER['HTTP_REFERER'],dirname(OCP\Util::linkToAbsolute('',''))) )
			header ( sprintf('Content-Disposition: inline; filename="qrcode-%s.png"',$result[0]['id']) );
		else
			header ( sprintf('Content-Disposition: attachment; filename="qrcode-%s.png"',$result[0]['id']) );
		// generate qrcode, regardless of who sends the request
		QRcode::png ( $result[0]['source'] );
	} // if $source
	else
	{
		// refuse forwarding => 403: Forbidden
		throw new OC_Shorty_HttpException ( 403 );
	}
} catch ( OC_Shorty_Exception $e ) { header($e->getMessage()); }
?>
