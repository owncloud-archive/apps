<?php
/**
 * ownCloud - Interface for PIM object
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
 * Subclass this class or implement IPIMObject interface for PIM objects
 */

abstract class AbstractPIMObject implements IPIMObject {

	/**
	 * This variable holds the ID of this object.
	 * Depending on the backend, this can be either a string
	 * or an integer, so we treat them all as strings.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * This variable holds the owner of this object.
	 *
	 * @var string
	 */
	protected $owner;

	/**
	 * This variable holds the parent of this object if any.
	 *
	 * @var string|null
	 */
	protected $parent;

	/**
	 * This variable holds the permissions of this object.
	 *
	 * @var integer
	 */
	protected $permissions;

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	function getDisplayName() {
		return $this->displayName;
	}

	/**
	 * @return string|null
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * If this object is part of a collection return a reference
	 * to the parent object, otherwise return null.
	 * @return IPIMObject|null
	 */
	function getParent() {
		return $this->parent;
	}

	/** CRUDS permissions (Create, Read, Update, Delete, Share) using a bitmask of
	 *
	 * \OCP\PERMISSION_CREATE
	 * \OCP\PERMISSION_READ
	 * \OCP\PERMISSION_UPDATE
	 * \OCP\PERMISSION_DELETE
	 * \OCP\PERMISSION_SHARE
	 * or
	 * \OCP\PERMISSION_ALL
	 *
	 * @return integer
	 */
	function getPermissions() {
		return $this->permissions;
	}

	/**
	 * @return AbstractBackend
	 */
	function getBackend() {
		return $this->backend;
	}

	/**
	 * @param integer $permission
	 * @return boolean
	 */
	function hasPermission($permission) {
		return $this->getPermissions() & $permission;
	}

}