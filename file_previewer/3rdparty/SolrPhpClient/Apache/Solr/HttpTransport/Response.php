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

/**
 * Represents the required pieces of an HTTP response provided by HTTP transport
 * implementations and then consumed by the Apache_Solr_Response class which provides
 * decoding
 */
class Apache_Solr_HttpTransport_Response
{
	/**
	 * Status Messages indexed by Status Code
	 * Obtained from: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 *
	 * @var array
	 */
	static private $_defaultStatusMessages = array(
		// Specific to PHP Solr Client
		0 => "Communication Error",
		
		// Informational 1XX
		100 => "Continue",
		101 => "Switching Protocols",
		
		// Successful 2XX
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		
		// Redirection 3XX
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		307 => "Temporary Redirect",
		
		// Client Error 4XX
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Request Range Not Satisfiable",
		417 => "Expectation Failed",
		
		// Server Error 5XX
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported"
	);
	
	/**
	 * Get the HTTP status message based on status code
	 *
	 * @return string
	 */
	public static function getDefaultStatusMessage($statusCode)
	{
		$statusCode = (int) $statusCode;
		
		if (isset(self::$_defaultStatusMessages[$statusCode]))
		{
			return self::$_defaultStatusMessages[$statusCode];
		}
		
		return "Unknown Status";
	}
	
	/**
	 * The response's HTTP status code
	 *
	 * @var integer
	 */
	private $_statusCode;
	
	/**
	 * The response's HTTP status message
	 *
	 * @var string
	 */
	private $_statusMessage;
	
	/**
	 * The response's mime type
	 *
	 * @var string
	 */
	private $_mimeType;
	
	/**
	 * The response's character encoding
	 *
	 * @var string
	 */
	private $_encoding;
	
	/**
	 * The response's data
	 *
	 * @var string
	 */
	private $_responseBody;
	
	/**
	 * Construct a HTTP transport response
	 * 
	 * @param integer $statusCode The HTTP status code
	 * @param string $contentType The VALUE of the Content-Type HTTP header
	 * @param string $responseBody The body of the HTTP response
	 */
	public function __construct($statusCode, $contentType, $responseBody)
	{
		// set the status code, make sure its an integer
		$this->_statusCode = (int) $statusCode;
		
		// lookup up status message based on code
		$this->_statusMessage = self::getDefaultStatusMessage($this->_statusCode);
		
		// set the response body, it should always be a string
		$this->_responseBody = (string) $responseBody;
		
		// parse the content type header value for mimetype and encoding
		// first set default values that will remain if we can't find
		// what we're looking for in the content type
		$this->_mimeType = "text/plain";
		$this->_encoding = "UTF-8";
		
		if ($contentType)
		{
			// now break apart the header to see if there's character encoding
			$contentTypeParts = explode(';', $contentType, 2);

			if (isset($contentTypeParts[0]))
			{
				$this->_mimeType = trim($contentTypeParts[0]);
			}

			if (isset($contentTypeParts[1]))
			{
				// we have a second part, split it further
				$contentTypeParts = explode('=', $contentTypeParts[1]);

				if (isset($contentTypeParts[1]))
				{
					$this->_encoding = trim($contentTypeParts[1]);
				}
			}
		}
	}
	
	/**
	 * Get the status code of the response
	 *
	 * @return integer
	 */
	public function getStatusCode()
	{
		return $this->_statusCode;
	}
	
	/**
	 * Get the status message of the response
	 *
	 * @return string
	 */
	public function getStatusMessage()
	{
		return $this->_statusMessage;
	}
	
	/**
	 * Get the mimetype of the response body
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->_mimeType;
	}
	
	/**
	 * Get the charset encoding of the response body.
	 *
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->_encoding;
	}
	
	/**
	 * Get the raw response body
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->_responseBody;
	}
}
