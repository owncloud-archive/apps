<?php
/**
 * ownCloud - Collection class for PIM object
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus (thomas@tanghus.net)
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
 * Subclass this for PIM collections
 */

abstract class PIMCollectionAbstract extends PIMObjectAbstract implements \Iterator, \Countable, \ArrayAccess {

	// Iterator properties

	protected $objects = array();

	protected $counter = 0;

	/**
	 * This is a collection so return null.
	 * @return null
	 */
	function getParent() {
		null;
	}

	/**
	* Returns a specific child node, referenced by its id
	*
	* @param string $id
	* @return IPIMObject
	*/
	abstract function getChild($id);

	/**
	* Returns an array with all the child nodes
	*
	* @return IPIMObject[]
	*/
	abstract function getChildren($limit = null, $offset = null);

	/**
	* Checks if a child-node with the specified id exists
	*
	* @param string $id
	* @return bool
	*/
	abstract function childExists($id);

    // Iterator methods

	public function rewind() {
		$this->counter = 0;
	}

	public function next() {
		$this->counter++;
	}

	public function valid() {
		return array_key_exists($this->counter, $this->objects);
	}

	public function current() {
		return $this->objects[$this->counter];
	}

	/** Implementations can choose to return the current objects ID/UUID
	 * to be able to iterate over the collection with ID => Object pairs:
	 * foreach($collection as $id => $object) {}
	 */
	public function key() {
		return $this->counter;
	}

	// Countable method.

	/**
	 * For implementations using a backend where fetching all object at once
	 * would give too much overhead, they can maintain an internal count value
	 * and fetch objects progressively. Simply watch the diffence between
	 * $this->counter, the value of count($this->objects) and the internal
	 * value, and fetch more objects when needed.
	 */
	public function count() {
		return count($this->objects);
	}

	// ArrayAccess methods

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->objects[] = $value;
		} else {
			$this->objects[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->objects[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->objects[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
	}

	// Magic property accessors
	// TODO: They should go in the implementations.

	public function __set($id, $value) {

	}

	public function __get($id) {

	}

	public function __isset($id) {

	}

	public function __unset($id) {

	}


}