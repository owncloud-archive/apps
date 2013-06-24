<?php
/**
 * Copyright (c) 2007-2011, Servigistics, Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of Servigistics, Inc. nor the names of
 *    its contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright Copyright 2007-2011 Servigistics, Inc. (http://servigistics.com)
 * @license http://solr-php-client.googlecode.com/svn/trunk/COPYING New BSD
 * @version $Id: $
 *
 * @package Apache
 * @subpackage Solr
 * @author Donovan Jimenez <djimenez@conduit-it.com>
 */

// Require Apache_Solr_HttpTransport_Abstract
require_once(dirname(__FILE__) . '/Abstract.php');

/**
 * HTTP Transport implemenation that uses the builtin http URL wrappers and file_get_contents
 */
class Apache_Solr_HttpTransport_FileGetContents extends Apache_Solr_HttpTransport_Abstract
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision:  $';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id:  $';
		
	/**
	 * Reusable stream context resources for GET and POST operations
	 *
	 * @var resource
	 */
	private $_getContext, $_headContext, $_postContext;
	
	/**
	 * Initializes our reuseable get and post stream contexts
	 */
	public function __construct()
	{
		$this->_getContext = stream_context_create();
		$this->_headContext = stream_context_create();
		$this->_postContext = stream_context_create();
	}

	public function performGetRequest($url, $timeout = false)
	{
		// set the timeout if specified
		if ($timeout !== FALSE && $timeout > 0.0)
		{
			// timeouts with file_get_contents seem to need
			// to be halved to work as expected
			$timeout = (float) $timeout / 2;

			stream_context_set_option($this->_getContext, 'http', 'timeout', $timeout);
		}
		else
		{
			// use the default timeout pulled from default_socket_timeout otherwise
			stream_context_set_option($this->_getContext, 'http', 'timeout', $this->getDefaultTimeout());
		}

		// $http_response_headers will be updated by the call to file_get_contents later
		// see http://us.php.net/manual/en/wrappers.http.php for documentation
		// Unfortunately, it will still create a notice in analyzers if we don't set it here
		$http_response_header = null;
		$responseBody = @file_get_contents($url, false, $this->_getContext);
		
		return $this->_getResponseFromParts($responseBody, $http_response_header);
	}

	public function performHeadRequest($url, $timeout = false)
	{
		stream_context_set_option($this->_headContext, array(
				'http' => array(
					// set HTTP method
					'method' => 'HEAD',

					// default timeout
					'timeout' => $this->getDefaultTimeout()
				)
			)
		);

		// set the timeout if specified
		if ($timeout !== FALSE && $timeout > 0.0)
		{
			// timeouts with file_get_contents seem to need
			// to be halved to work as expected
			$timeout = (float) $timeout / 2;

			stream_context_set_option($this->_headContext, 'http', 'timeout', $timeout);
		}
		
		// $http_response_headers will be updated by the call to file_get_contents later
		// see http://us.php.net/manual/en/wrappers.http.php for documentation
		// Unfortunately, it will still create a notice in analyzers if we don't set it here
		$http_response_header = null;
		$responseBody = @file_get_contents($url, false, $this->_headContext);

		return $this->_getResponseFromParts($responseBody, $http_response_header);
	}
	
	public function performPostRequest($url, $rawPost, $contentType, $timeout = false)
	{
		stream_context_set_option($this->_postContext, array(
				'http' => array(
					// set HTTP method
					'method' => 'POST',

					// Add our posted content type
					'header' => "Content-Type: $contentType",

					// the posted content
					'content' => $rawPost,

					// default timeout
					'timeout' => $this->getDefaultTimeout()
				)
			)
		);

		// set the timeout if specified
		if ($timeout !== FALSE && $timeout > 0.0)
		{
			// timeouts with file_get_contents seem to need
			// to be halved to work as expected
			$timeout = (float) $timeout / 2;

			stream_context_set_option($this->_postContext, 'http', 'timeout', $timeout);
		}

		// $http_response_header will be updated by the call to file_get_contents later
		// see http://us.php.net/manual/en/wrappers.http.php for documentation
		// Unfortunately, it will still create a notice in analyzers if we don't set it here
		$http_response_header = null;
		$responseBody = @file_get_contents($url, false, $this->_postContext);
		
		// reset content of post context to reclaim memory
		stream_context_set_option($this->_postContext, 'http', 'content', '');
		
		return $this->_getResponseFromParts($responseBody, $http_response_header);
	}
	
	private function _getResponseFromParts($rawResponse, $httpHeaders)
	{
		//Assume 0, false as defaults
		$status = 0;
		$contentType = false;

		//iterate through headers for real status, type, and encoding
		if (is_array($httpHeaders) && count($httpHeaders) > 0)
		{
			//look at the first headers for the HTTP status code
			//and message (errors are usually returned this way)
			//
			//HTTP 100 Continue response can also be returned before
			//the REAL status header, so we need look until we find
			//the last header starting with HTTP
			//
			//the spec: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.1
			//
			//Thanks to Daniel Andersson for pointing out this oversight
			while (isset($httpHeaders[0]) && substr($httpHeaders[0], 0, 4) == 'HTTP')
			{
				// we can do a intval on status line without the "HTTP/1.X " to get the code
				$status = intval(substr($httpHeaders[0], 9));

				// remove this from the headers so we can check for more
				array_shift($httpHeaders);
			}

			//Look for the Content-Type response header and determine type
			//and encoding from it (if possible - such as 'Content-Type: text/plain; charset=UTF-8')
			foreach ($httpHeaders as $header)
			{
				// look for the header that starts appropriately
				if (strncasecmp($header, 'Content-Type:', 13) == 0)
				{
					$contentType = substr($header, 13);
					break;
				}
			}
		}
		
		return new Apache_Solr_HttpTransport_Response($status, $contentType, $rawResponse);
	}
}