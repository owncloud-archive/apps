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
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>, Donovan Jimenez <djimenez@conduit-it.com>
 */

// require Apache_Solr_HttpTransport_Response
require_once(dirname(__FILE__) . '/Response.php');

/**
 * Interface that all Transport (HTTP Requester) implementations must implement. These
 * Implementations can then be plugged into the Service instance in order to user their
 * the desired method for making HTTP requests
 */
interface Apache_Solr_HttpTransport_Interface
{
	/**
	 * Get the current default timeout for all HTTP requests
	 *
	 * @return float
	 */
	public function getDefaultTimeout();
	
	/**
	 * Set the current default timeout for all HTTP requests
	 *
	 * @param float $timeout
	 */
	public function setDefaultTimeout($timeout);
		
	/**
	 * Perform a GET HTTP operation with an optional timeout and return the response
	 * contents, use getLastResponseHeaders to retrieve HTTP headers
	 *
	 * @param string $url
	 * @param float $timeout
	 * @return Apache_Solr_HttpTransport_Response HTTP response
	 */
	public function performGetRequest($url, $timeout = false);
	
	/**
	 * Perform a HEAD HTTP operation with an optional timeout and return the response
	 * headers - NOTE: head requests have no response body
	 *
	 * @param string $url
	 * @param float $timeout
	 * @return Apache_Solr_HttpTransport_Response HTTP response
	 */
	public function performHeadRequest($url, $timeout = false);
	
	/**
	 * Perform a POST HTTP operation with an optional timeout and return the response
	 * contents, use getLastResponseHeaders to retrieve HTTP headers
	 *
	 * @param string $url
	 * @param string $rawPost
	 * @param string $contentType
	 * @param float $timeout
	 * @return Apache_Solr_HttpTransport_Response HTTP response
	 */
	public function performPostRequest($url, $rawPost, $contentType, $timeout = false);
}