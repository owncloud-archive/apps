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
 * @file lib/type.php
 * Type handling, recognition and verification routines
 * @author Christian Reiner
 */

// the general length where list content is chopped by an ellipsis for narrow columns
define ( 'CL', 44 );
// some basic regular expressions to build our catalog further down
define ( '__rx_path',		'(\/($|.+)?)*' );
define ( '__rx_domain_tld',	'(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2})' );
define ( '__rx_domain_ip',	'(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])' );
define ( '__rx_domain_name',__rx_domain_ip.'|localhost|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.'.__rx_domain_tld );
define ( '__rx_file_url',	'file\:\/\/'.__rx_path );

/**
 * @class OC_Shorty_Type
 * @brief Static 'namespace' class offering routines and constants used to handle type recognition and value verification
 * @access public
 * @author Christian Reiner
 */
class OC_Shorty_Type
{
	// the 'types' of values we deal with, actually more something like flavours
	const ID          = 'id';
	const STATUS      = 'status';
	const SORTKEY     = 'sortkey';
	const SORTVAL     = 'sortval';
	const STRING      = 'string';
	const URL         = 'url';
	const PATH        = 'path';
	const INTEGER     = 'integer';
	const FLOAT       = 'float';
	const DATE        = 'date';
	const TIMESTAMP   = 'timestamp';
	const BOOLEAN     = 'boolean';
	// a list of all valid list sorting codes
	static $SORTING = array (
		''  =>'created DESC', // default
		'aa'=>'accessed', 'ad'=>'accessed DESC',
		'ca'=>'created',  'cd'=>'created DESC',
		'da'=>'until',    'dd'=>'until DESC',
		'ha'=>'clicks',   'hd'=>'clicks DESC',
		'ka'=>'id',       'kd'=>'id DESC',
		'sa'=>'status',   'sd'=>'status DESC',
		'ta'=>'title',    'td'=>'title DESC',
		'ua'=>'target',   'ud'=>'target DESC' );
	// a list of all valid user preferences
	static $PREFERENCE = array (
		'backend-type'           => OC_Shorty_Type::STRING,
		'backend-static-base'    => OC_Shorty_Type::URL,
		'backend-bitly-user'     => OC_Shorty_Type::STRING,
		'backend-bitly-key'      => OC_Shorty_Type::STRING,
		'backend-google-key'     => OC_Shorty_Type::STRING,
		'backend-tinycc-user'    => OC_Shorty_Type::STRING,
		'backend-tinycc-key'     => OC_Shorty_Type::STRING,
		'backend-ssl-verify'     => OC_Shorty_Type::INTEGER,
		'sms-control'            => OC_Shorty_Type::STRING,
		'verbosity-control'      => OC_Shorty_Type::STRING,
		'list-sort-code'         => OC_Shorty_Type::SORTKEY,
		'controls-panel-visible' => OC_Shorty_Type::BOOLEAN,
	);
	// valid status for entries
	static $STATUS = array (
		'blocked',
		'private',
		'shared',
		'public',
		'deleted',
	);
	// valid results of requests
	static $RESULT = array (
		'blocked',
		'denied',
		'granted',
	);
	// a list of implemented backends
	static $BACKENDS = array (
		'none'    => ' [ none ] ',
		'static'  => 'static backend',
// 		'bitly'   => 'bitly.com service',
// 		'cligs'   => 'cli.gs service',
		'isgd'    => 'is.gd service',
		'google'  => 'goo.gl service',
// 		'tinycc'  => 'tiny.cc service',
		'tinyurl' => 'ti.ny service',
	);
	// a list of all valid system settings
	static $SETTING = array (
		'backend-static-base' => OC_Shorty_Type::URL,
	);
	static $HTTPCODE = array (
		200 => 'Ok',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);
	// a catalog of regular expressions
	static $RX = array (
		'DOMAIN_TLD'	=>	__rx_domain_tld,
		'DOMAIN_IP'		=>	__rx_domain_ip,
		'DOMAIN_NAME'	=>	__rx_domain_name,
		'NUMBER'		=>	'[0-9]+',
		'SHORTY_ID'		=>	'[a-z0-9]{2,20}',
		'TIMESTAMP'		=>	'[0-9]{10}',
		'URL_SCHEME'	=>	'([a-zA-Z][a-zA-Z][a-zA-Z0-9]+)',
		'PATH'			=>	__rx_path,
		'FILE_URL'		=>	__rx_file_url,
	);

	/**
	* @method OC_Shorty_Type::validate
	* @brief Validates a given value against a type specific re  gular expression
	* Validates a given value according to the claimed type of   the value.
	* Validation is done by matching the value against a type s  pecific regular expression.
	* @param mixed value: Value to be verified according to the   specified type
	* @param OC_Shorty_Type::type type: Type the value is said   to belong to, important for verification
	* @param bool strict: Flag indicating if the verification s  hould be done strict, that is if an exception should be thrown in case of a failure
	* @return mixed|NULL The value itself in case of a positive   validation, NULL or an exception in case of a failure, depending on the flag indication strict mode
	* @throws error Indicating a failed validation in case of s  trict mode
	* @access public
	* @author Christian Reiner
	*/
	static function validate ( $value, $type, $strict=FALSE )
	{
		switch ( $type )
		{
			case self::ID:
				if ( preg_match ( '/^'.self::$RX['SHORTY_ID'].'$/i', $value ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::STATUS:
				if ( in_array($value,OC_Shorty_Type::$STATUS) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::SORTKEY:
				if ( array_key_exists ( trim($value), self::$SORTING ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::SORTVAL:
				if ( in_array ( trim($value), self::$SORTING ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::STRING:
				if ( preg_match ( '/^.*$/x', str_replace("\n","\\n",$value) ) )
					return str_replace("\n","\\n",$value);
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::URL:
				$pattern = '/^'.self::$RX['URL_SCHEME'].'\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*'.self::$RX['DOMAIN_NAME'].'(\:'.self::$RX['NUMBER'].')*(\/($|.+)?)*$/';
				if ( preg_match ( $pattern, $value ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::PATH:
				$pattern = '/^'.self::$RX['PATH'].'$/';
				if ( preg_match ( $pattern, $value ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::INTEGER:
				if ( preg_match ( '/^'.self::$RX['NUMBER'].'$/', $value ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::FLOAT:
				if ( preg_match ( '/^'.self::$RX['NUMBER'].'(\.'.self::$RX['NUMBER'].')?$/', $value ) )
					return $value;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::TIMESTAMP:
				if ( preg_match ( '/^'.self::$RX['TIMESTAMP'].'$/', $value ) )
					return $value;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::DATE:
				if (FALSE!==($time=strtotime($value)))
					return $time;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

			case self::BOOLEAN:
				if ( OC_Shorty_Tools::toBoolean(trim($value),$strict) )
					return TRUE;
				elseif ( ! $strict)
					return FALSE;
				throw new OC_Shorty_Exception ( "invalid value '%s' for type '%s'", array( ((CL<strlen($value))?$value:substr($value,0,(CL-3)).'…'),$type) );

		} // switch $type
		throw new OC_Shorty_Exception ( "unknown request argument type '%s'", array($type) );
	} // function validate

	/**
	* @method OC_Shorty_Type::normalize
	* @brief Cleanup and formal normalization of a given value   according to its type
	* Normalizes a given value according to its claimed type.
	* This typically means trimming of string values, but somet  imes also more specific actions.
	* @param mixed value: Value to be normalized
	* @param OC_Shorty_Type::type type: Supposed type of the va  lue
	* @param bool strict: Flag indicating if the normalization   should be done in a strict way
	* @return mixed: The normalized value
	* @throws error Indicating a parameter violation
	* @access public
	* @author Christian Reiner
	*/
	static function normalize ( $value, $type, $strict=FALSE )
	{
		if (NULL===(self::validate($value,$type,$strict)))
		{
			if ( ! $strict)
				return NULL;
			else
				throw new OC_Shorty_Exception ( "invalid value '%1\$s' for type '%2\$s'", array($value,$type) );
		} // if
		switch ( $type )
		{
			case self::ID:
				return trim ( $value );

			case self::STATUS:
				return trim ( $value );

			case self::SORTKEY:
				return trim ( $value );

			case self::SORTVAL:
				return trim ( $value );

			case self::STRING:
				return trim ( $value );

			case self::URL:
				return trim ( $value );

			case self::PATH:
				return trim ( $value );

			case self::INTEGER:
				return sprintf ( '%d', $value );

			case self::FLOAT:
				return sprintf ( '%f', $value );

			case self::TIMESTAMP:
				return trim ( $value );

			case self::DATE:
				return date ( 'Y-m-d', self::validate($value,OC_Shorty_Type::DATE) );

			case self::BOOLEAN:
				return OC_Shorty_Tools::toBoolean(trim($value)) ? TRUE : FALSE;

		} // switch $type
		throw new OC_Shorty_Exception ( "unknown request argument type '%s'", array($type) );
	} // function normalize

	/**
	* @method OC_Shorty_Type::req_argument
	* @brief Returns checked request argument or throws an erro  r
	* @param string arg: Name of the request argument to get_ar  gument
	* @param bool strict: Controls if an exception will be thrown upon a missing argument
	* @return string: Checked and prepared value of request arg  ument
	* @throws error Indicating a parameter violation
	* @access public
	* @author Christian Reiner
	*/
	static function req_argument ( $arg, $type, $strict=FALSE )
	{
		switch ( $_SERVER['REQUEST_METHOD'] )
		{
			case 'POST':
				if ( isset($_POST[$arg]) )
					return self::normalize ( urldecode($_POST[$arg]), $type ) ;
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "missing mandatory argument '%1s'", array($arg) );

			case 'GET':
				if ( isset($_GET[$arg]) && !empty($_GET[$arg]) )
					return self::normalize ( urldecode(trim($_GET[$arg])), $type, $strict );
				elseif ( ! $strict)
					return NULL;
				throw new OC_Shorty_Exception ( "missing mandatory argument '%1s'", array($arg) );

			default:
				throw new OC_Shorty_Exception ( "unexpected http request method '%1s'", array($_SERVER['REQUEST_METHOD']) );
		}
  } // function req_argument

} // class OC_Shorty_Query
?>
