<?php

/**
* ownCloud - Internal Bookmarks plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/**
 * This class manages internal bookmarks within the database. 
 */
class OC_IntBks {

	/**
	 * Get an item by its id and UID
	 * @param $id The id of the item
	 * @return Array or Boolean
	 */
	public static function getItemById($id) {
		$query = OC_DB::prepare("SELECT bktitle, bktarget FROM *PREFIX*internal_bookmarks WHERE bkid = ? AND uid = ?");
		$result = $query->execute(Array($id, OC_User::getUser()))->fetchAll();
		if(count($result) > 0) {
			return $result;
		}
		return FALSE;
	}
	
	/**
	 * Get all items by UID
	 * @return Array
	 */
	public static function getAllItemsByUser() {
		$query = OC_DB::prepare("SELECT bkid, bktitle, bktarget, bkorder FROM *PREFIX*internal_bookmarks WHERE uid = ? ORDER BY bkorder");
		$result = $query->execute(Array(OC_User::getUser()))->fetchAll();
		if(count($result) > 0) {
			return $result;
		}
		return Array();
	}
	
	/**
	 * Get an item by its target and UID
	 * @param $target The target of the item
	 * @return Array
	 */
	public static function getItemByTarget($target) {
		self::cleanTarget($target);
		$query = OC_DB::prepare("SELECT bkid, bktitle, bktarget FROM *PREFIX*internal_bookmarks WHERE bktarget = ? AND uid = ?");
		$result = $query->execute(Array($target, OC_User::getUser()))->fetchAll();
		if(count($result) > 0) {
			return $result[0];
		}
		return Array();
	}
	
	/**
	 * Delete an item by its target and UID
	 * @param $target The target of the item
	 */
	public static function deleteItemByTarget($target) {
		self::cleanTarget($target);
		$query = OC_DB::prepare("DELETE FROM *PREFIX*internal_bookmarks WHERE bktarget = ? AND uid = ?");
		$result = $query->execute(Array($target, OC_User::getUser()));
	}
	
	/**
	 * Insert a new item by its target and define the bookmark name automatically by UID
	 * @param $target The target location of the item
	 * @return Array Complete element just inserted 
	 */
	public static function insertNewItem($target) {
		self::cleanTarget($target);
		$tot = self::getAllItemsByUser();
		$tot = $tot[count($tot)-1]['bkorder'];
		$query = OC_DB::prepare("INSERT INTO *PREFIX*internal_bookmarks (uid, bktitle, bktarget, bkorder) VALUES (?,?,?,?)");
		$query->execute(Array(OC_User::getUser(), substr($target, strrpos($target, '/')+1), $target, $tot+1));
		return self::getItemByTarget($target);
	}
	
	/**
	 * Update the title of an item by UID
	 * @param $id The id of the item
	 * @param $name The new title of the item
	 */
	public static function updateItemNameById($id, $title) {
		$query = OC_DB::prepare("UPDATE *PREFIX*internal_bookmarks SET bktitle = ? WHERE bkid = ?");
		$query->execute(Array($title, $id, OC_User::getUser()));
	}
	
	/**
	 * Update order of the user internal bookmarks
	 * @param $id id
	 * @param $order New order
	 */
	public static function updateItemOrder($id, $order) {
		$query = OC_DB::prepare("UPDATE *PREFIX*internal_bookmarks SET bkorder = ? WHERE bkid = ?");
		$query->execute(Array($order, $id));
	} 
	
	/**
	 * Clean the path of the target
	 * @param $target The target you want to clean
	 * @return String
	 */
	private static function cleanTarget($target) {
		$target = rtrim($target, "/");
		return preg_replace('{(/)\1+}', "/", $target);
	}
	 
}
