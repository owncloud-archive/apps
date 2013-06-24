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
 * @version $Id: Response.php 54 2011-02-04 16:29:18Z donovan.jimenez $
 *
 * @package Apache
 * @subpackage Solr
 * @author Donovan Jimenez <djimenez@conduit-it.com>
 */

require_once(dirname(__FILE__) . '/ParserException.php');

/**
 * Represents a Solr response.  Parses the raw response into a set of stdClass objects
 * and associative arrays for easy access.
 *
 * Currently requires json_decode which is bundled with PHP >= 5.2.0, Alternatively can be
 * installed with PECL.  Zend Framework also includes a purely PHP solution.
 */
class Apache_Solr_Response
{
	/**
	 * SVN Revision meta data for this class
	 */
	const SVN_REVISION = '$Revision: 54 $';

	/**
	 * SVN ID meta data for this class
	 */
	const SVN_ID = '$Id: Response.php 54 2011-02-04 16:29:18Z donovan.jimenez $';

	/**
	 * Holds the raw response used in construction
	 *
	 * @var Apache_Solr_HttpTransport_Response HTTP response
	 */
	protected $_response;

	/**
	 * Whether the raw response has been parsed
	 *
	 * @var boolean
	 */
	protected $_isParsed = false;

	/**
	 * Parsed representation of the data
	 *
	 * @var mixed
	 */
	protected $_parsedData;

	/**
	 * Data parsing flags.  Determines what extra processing should be done
	 * after the data is initially converted to a data structure.
	 *
	 * @var boolean
	 */
	protected $_createDocuments = true,
			$_collapseSingleValueArrays = true;

	/**
	 * Constructor. Takes the raw HTTP response body and the exploded HTTP headers
	 *
	 * @return Apache_Solr_HttpTransport_Response HTTP response
	 * @param boolean $createDocuments Whether to convert the documents json_decoded as stdClass instances to Apache_Solr_Document instances
	 * @param boolean $collapseSingleValueArrays Whether to make multivalued fields appear as single values
	 */
	public function __construct(Apache_Solr_HttpTransport_Response $response, $createDocuments = true, $collapseSingleValueArrays = true)
	{
		$this->_response = $response;
		$this->_createDocuments = (bool) $createDocuments;
		$this->_collapseSingleValueArrays = (bool) $collapseSingleValueArrays;
	}

	/**
	 * Get the HTTP status code
	 *
	 * @return integer
	 */
	public function getHttpStatus()
	{
		return $this->_response->getStatusCode();
	}

	/**
	 * Get the HTTP status message of the response
	 *
	 * @return string
	 */
	public function getHttpStatusMessage()
	{
		return $this->_response->getStatusMessage();
	}

	/**
	 * Get content type of this Solr response
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->_response->getMimeType();
	}

	/**
	 * Get character encoding of this response. Should usually be utf-8, but just in case
	 *
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->_response->getEncoding();
	}

	/**
	 * Get the raw response as it was given to this object
	 *
	 * @return string
	 */
	public function getRawResponse()
	{
		return $this->_response->getBody();
	}

	/**
	 * Magic get to expose the parsed data and to lazily load it
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (!$this->_isParsed)
		{
			$this->_parseData();
			$this->_isParsed = true;
		}

		if (isset($this->_parsedData->$key))
		{
			return $this->_parsedData->$key;
		}

		return null;
	}

	/**
	 * Magic function for isset function on parsed data
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		if (!$this->_isParsed)
		{
			$this->_parseData();
			$this->_isParsed = true;
		}

		return isset($this->_parsedData->$key);
	}

	/**
	 * Parse the raw response into the parsed_data array for access
	 *
	 * @throws Apache_Solr_ParserException If the data could not be parsed
	 */
	protected function _parseData()
	{
		//An alternative would be to use Zend_Json::decode(...)
		$data = json_decode($this->_response->getBody());

		// check that we receive a valid JSON response - we should never receive a null
		if ($data === null)
		{
			throw new Apache_Solr_ParserException('Solr response does not appear to be valid JSON, please examine the raw response with getRawResponse() method');
		}

		//if we're configured to collapse single valued arrays or to convert them to Apache_Solr_Document objects
		//and we have response documents, then try to collapse the values and / or convert them now
		if (($this->_createDocuments || $this->_collapseSingleValueArrays) && isset($data->response) && is_array($data->response->docs))
		{
			$documents = array();

			foreach ($data->response->docs as $originalDocument)
			{
				if ($this->_createDocuments)
				{
					$document = new Apache_Solr_Document();
				}
				else
				{
					$document = $originalDocument;
				}

				foreach ($originalDocument as $key => $value)
				{
					//If a result is an array with only a single
					//value then its nice to be able to access
					//it as if it were always a single value
					if ($this->_collapseSingleValueArrays && is_array($value) && count($value) <= 1)
					{
						$value = array_shift($value);
					}

					$document->$key = $value;
				}

				$documents[] = $document;
			}

			$data->response->docs = $documents;
		}

		$this->_parsedData = $data;
	}
}