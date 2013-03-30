<?php
/**
 * ownCloud - Request
 *
 * @author Thomas Tanghus
 * @copyright 2013 Thomas Tanghus (thomas@tanghus.net)
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Contacts;

/**
 * Class for accessing variables in the request.
 * This class provides an immutable object with request variables.
 */

class Request implements \ArrayAccess, \Countable {

	protected $items = array();

	protected $varnames = array('get', 'post', 'files', 'server', 'env', 'session', 'cookies', 'urlParams', 'params', 'parameters', 'method');

	/**
	 * @param array $vars And associative array with the following optional	values:
	 * @param array 'params' the parsed json array
	 * @param array 'urlParams' the parameters which were matched from the URL
	 * @param array 'get' the $_GET array
	 * @param array 'post' the $_POST array
	 * @param array 'files' the $_FILES array
	 * @param array 'server' the $_SERVER array
	 * @param array 'env' the $_ENV array
	 * @param array 'session' the $_SESSION array
	 * @param array 'cookies' the $_COOKIE array
	 * @see http://www.php.net/manual/en/reserved.variables.php
	 */
	public function __construct(array $vars = array()) {

		foreach($this->varnames as $name) {
			switch($name) {
				case 'get':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : $_GET;
					break;
				case 'post':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : $_POST;
					break;
				case 'files':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : $_FILES;
					break;
				case 'server':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : $_SERVER;
					break;
				case 'env':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : $_ENV;
					break;
				case 'session':
					$this->items[$name] = isset($vars[$name])
						? $vars[$name]
						: (isset($_SESSION) ? $_SESSION : array());
					break;
				case 'cookies':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : $_COOKIE;
					break;
				case 'method':
					$this->items[$name] = isset($vars[$name])
						? $vars[$name]
						: (isset($_SERVER) && isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : array());
					break;
				case 'urlParams':
					$this->items[$name] = isset($vars[$name]) ? $vars[$name] : array();
					break;
				case 'params':
					if(isset($vars[$name])) {
						$this->items[$name] = $vars[$name];
					} else {
						$params = json_decode(file_get_contents('php://input'));
						$this->items[$name] = is_null($params) ? array() : $params;
					}
					break;
			}
		}

		$this->items['parameters'] = array_merge(
			$this->items['params'],
			$this->items['get'],
			$this->items['post'],
			$this->items['urlParams']
		);

	}

	// Countable method.
	public function count() {
		return count(array_keys($this->items['parameters']));
	}

	/**
	* ArrayAccess methods
	*
	* Gives access to the combined GET, POST and urlParams arrays
	*
	* Examples:
	*
	* $var = $request['myvar'];
	*
	* or
	*
	* if(!isset($request['myvar']) {
	* 	// Do something
	* }
	*
	* $request['myvar'] = 'something'; // This throws an exception.
	*
	* @param string offset The key to lookup
	* @return string|null
	*/
	public function offsetExists($offset) {
		return isset($this->items['parameters'][$offset]);
	}

	/**
	* @see offsetExists
	*/
	public function offsetGet($offset) {
		return isset($this->items['parameters'][$offset])
			? $this->items['parameters'][$offset]
			: null;
	}

	/**
	* @see offsetExists
	*/
	public function offsetSet($offset, $value) {
		throw new \RuntimeException('You cannot change the contents of the request object');
	}

	/**
	* @see offsetExists
	*/
	public function offsetUnset($offset) {
		throw new \RuntimeException('You cannot change the contents of the request object');
	}

	/**
	* Get a value from a request variable or the default value e.g:
	* $request->get('post', 'some_key', 'default value');
	*
	* @param string $vars Which variables to look in e.g. 'get', 'post, 'session'
	* @param string $name
	* @param string $default
	*/
	public function getVar($vars, $name, $default = null) {
		return isset($this->{$vars}[$name]) ? $this->{$vars}[$name] : $default;
	}

	// Magic property accessors
	public function __set($name, $value) {
		throw new \RuntimeException('You cannot change the contents of the request object');
	}

	/**
	* Access request variables by method and name.
	* Examples:
	*
	* $request->post['myvar']; // Only look for POST variables
	* $request->myvar; or $request->{'myvar'}; or $request->{$myvar}
	* Looks in the combined GET, POST and urlParams array.
	*
	* if($request->method !== 'POST') {
	* 	throw new Exception('This function can only be invoked using POST');
	* }
	*
	* @param string $name The key to look for.
	* @return mixed|null
	*/
	public function __get($name) {
		switch($name) {
			case 'get':
			case 'post':
			case 'files':
			case 'server':
			case 'env':
			case 'session':
			case 'cookies':
			case 'parameters':
			case 'urlParams':
				return isset($this->items[$name])
					? $this->items[$name]
					: null;
				break;
			case 'method':
				return $this->items['method'];
				break;
			default;
				return isset($this[$name]) ? $this[$name] : null;
				break;
		}
	}

	public function __isset($name) {
		return isset($this->items['parameters'][$name]);
	}

	public function __unset($id) {
		throw new \RunTimeException('You cannot change the contents of the request object');
	}

}
