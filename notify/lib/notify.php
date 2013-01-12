<?php
/**
* ownCloud - user notifications
*
* @author Florian Hülsmann
* @copyright 2012 Florian Hülsmann <fh@cbix.de>
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

/**
 *   User notifications class
 * 
 * Any app can send notifications with a short text to any ownCloud user.
 * To provide notifications, that app's info.xml has to contain named templates.
 * The name of a template may later be used by an application to manipulate
 * the style of the notifications with CSS or to mark all its notifications
 * as read.
 * Templates may contain placeholders (format: {key}) for dynamic values and may be translatable.
 * Any parameter passed in an array to the sendUserNotification method that
 * is neither "img", "href" nor meant to set a value for a placeholder
 * gets translated to an HTML data-* attribute assigned to the notification
 * element.
 * 
 *   Example usage
 * 
 * Provided we want to implement notifications in the files_sharing app to inform users when someone else shares a file with them.
 * 
 * This is an example info.xml:
 * <info>
 *   ...
 *   <notifications>
 *     <template id="sharedFile" summary="File shared">&lt;strong&gt;{user_from}&lt;/strong&gt; shared a file with you!</template>
 *     <template id="removed" summary="Shared file removed">&lt;strong&gt;{user}&lt;/strong&gt; removed a shared file!</template>
 *   </notifications>
 * </info>
 * 
 * The template may contain encoded (!) HTML tags of the following types: a, b, i, strong, em, span.
 * 
 * When a user shares a file, we use this PHP code to send the notification:
 * 
 * <?php
 * // ...
 * if(class_exists('OC_Notify')) {
 *     $notification_id = OC_Notify::sendUserNotification('files_sharing', 'sharedFile', $target_user, array(
 *         'user_from' => $source_user,
 *         'href' => OCP\Util::linkTo('files', 'index.php', array('dir' => '/Shared')),
 *         'img' => OCP\Util::imagePath('core', 'actions/share.svg')
 *     ));
 * }
 * ?>
 * 
 * The method returns the notification id, if the operation was successful.
 * Please keep in mind that the user may add the requested notification to
 * the personal notification blacklist. In this case the method simply returns
 * false and doesn't send the notification at all.
 * 
 * These parameters are reserved and should not be used as placeholders:
 *  - href: either an absolute or relative URL where the user gets to when clicking on the notification
 *  - img: an image that is included in the notification list. It's highly recommended to provide
 * 			CSS rules along with your app that makes this image look good.
 * 
 * When the user opens the notification list, the notifications' HTML elements
 * get a class of the format "app_templateId" where "app" is the app id and
 * "templateId" the notification's template id given in info.xml and the
 * sendUserNotification() call. This makes it easy for us to give our
 * notifications custom CSS properties, for example:
 * 
 * .notification.files_sharing_sharedFile img.notify-img { float:left; height:2em; width:2em; }
 * 
 * Users can manually mark their notifications as read, but often the application
 * can know that the user has read a specific notification or doesn't need
 * it any more. In this case we can chose from these public methods inside OC_Notify:
 *  - markReadByApp('myapp')
 *  - markReadByApp('myapp', 'mynotification')
 *  - markReadById(1337)
 *  - deleteByApp('myapp')
 *  - deleteByApp('myapp', 'mynotification')
 *  - deleteById(1337)
 */

//\OC_Hook::connect('OC_User', 'post_deleteUser', 'OC_Notify', 'post_deleteUser');
//\OC_Hook::connect('OCP\Share', 'post_shared', 'OC_Notify', 'post_shared');

class OC_Notify {
	// reusable prepared statements:
	private static $classesStmt, $classIdStmt, $classIdsStmt, $classInsertStmt, $notifyStmt, $paramStmt, $readByIdStmt, $readByUserStmt, $readByClassIdStmt, $deleteByIdStmt, $deleteByUserStmt, $deleteByClassIdStmt, $deleteByReadStmt, $deleteParamsByIdStmt, $deleteParamsByUserStmt, $deleteParamsByClassIdStmt, $deleteParamsByReadStmt, $addToBlacklistStmt, $removeFromBlacklistStmt, $unreadNumStmt, $isBlockedStmt;
	
	/**
	 * @brief get the class id of a given app/class name pair or all class ids of the given app
	 * @param $app app id
	 * @param $class class name defined in the app's info.xml or null to fetch all class IDs of the given app
	 * @return id, array or false, if the class doesn't exist
	 */
	private static function getClassId($app, $class = null) {
		$return = array();
		if($class == null) {
			// get all classes of $app
			if(!isset(self::$classIdsStmt)) {
				self::$classIdsStmt = OCP\DB::prepare("SELECT id FROM *PREFIX*notification_classes WHERE appid = ?");
			}
			$result = self::$classIdsStmt->execute(array($app));
			while(($row = $result->fetchOne()) !== false) {
				$return[] = (int)$row;
			}
		} else {
			if(!isset(self::$classIdStmt)) {
				self::$classIdStmt = OCP\DB::prepare("SELECT id FROM *PREFIX*notification_classes WHERE appid = ? AND name = ?");
			}
			$result = self::$classIdStmt->execute(array($app, $class));
			if(($row = $result->fetchOne()) !== false) {
				$return = (int)$row;
			}
		}
		if(!count($return)) {
			return self::parseAppNotifications($app, $class);
		}
		return $return;
	}
	
	/**
	 * @brief parse the info.xml of the given app and save its notification templates to database
	 * @param $app application id
	 * @param $class optional name of the class to get its ID
	 * @return class id if a name is given and the class exists, array of class IDs if only the app is given
	 */
	public static function parseAppNotifications($app, $class = null) {
		if(!isset(self::$classInsertStmt)) {
			self::$classInsertStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notification_classes (appid, name, summary, content) VALUES (?, ?, ?, ?)");
		}
		$appInfo = @file_get_contents(OC_App::getAppPath($app) . '/appinfo/info.xml');
		if($appInfo) {
			$xml = new SimpleXMLElement($appInfo);
		} else {
			return false;
		}
		$templates = $xml->xpath('notifications/template');
		$return = array();
		foreach($templates as $template) {
			$attr = $template->attributes();
			$name = $attr->id;
			$summary = substr(strip_tags(trim($attr->summary)), 0, 64);
			$content = strip_tags((string)$template, "<a><b><i><strong><em><span>");
			if(empty($name) or empty($content)) {
				//FIXME also require summary??
				continue;
			}
			try {
				self::$classInsertStmt->execute(array($app, $name, $summary, $content));
				$id = OCP\DB::insertid("*PREFIX*notification_classes");
				if($class == null) {
					$return[] = (int)$id;
				} else {
					if($class == $name) {
						$return = (int)$id;
					}
				}
			} catch(Exception $e) {
				//most likely a database conflict
			}
		}
		if($class == null or is_int($return)) {
			return $return;
		} else {
			return false;
		}
	}
	
    /**
     * @brief get the number of unread notifications for the logged in user
     * @param $uid user id
     * @return number of unread notifications, 0 if not logged in
     */
    public static function getUnreadNumber($uid = null) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$unreadNumStmt)) {
			self::$unreadNumStmt = OCP\DB::prepare("SELECT COUNT(*) FROM *PREFIX*notifications WHERE read = 0 AND uid = ?");
		}
        return self::$unreadNumStmt->execute(array($uid))->fetchOne();
    }
    
    /**
     * @brief send a new notification to the given user
     * @param $appid app which sends the notification
     * @param $class id relating to a template in the app's info.xml
     * @param $uid receiving user
     * @param $params keys and values for placeholders in the template and href/img
     * @return id of the inserted notification, null if unsuccessful
     */
    public static function sendUserNotification($appid, $class, $uid, $params = array()) {
        try {
			$classId = self::getClassId($appid, $class);
			if($classId === false) {
				throw new Exception("Notification template $appid/$class not found");
			}
			if(self::isBlacklisted($uid, $classId)) {
				return null;
			}
            OCP\DB::beginTransaction();
            if(!isset(self::$notifyStmt)) {
				self::$notifyStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notifications (class, uid, moment) VALUES (?, ?, NOW())");
			}
			OC_Hook::emit("notify", "pre_sendUserNotification", array(
				"classId" => $classId,
				"uid" => $uid,
				"params" => $params
			));
            self::$notifyStmt->execute(array($classId, $uid));
            $id = OCP\DB::insertid("*PREFIX*notifications");
            if(count($params)) {
				if(!isset(self::$paramStmt)) {
					self::$paramStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notification_params (nid, key, value) VALUES ($id, ?, ?)");
				}
                foreach($params as $key => $value) {
                    self::$paramStmt->execute(array($key, $value));
                    OCP\DB::insertid("*PREFIX*notification_params");
                }
            }
			OCP\DB::commit();
			OC_Hook::emit("notify", "post_sendUserNotification", array(
				"id" => $id,
				"classId" => $classId,
				"uid" => $uid,
				"params" => $params
			));
            return (int)$id;
        } catch(Exception $e) {
            OCP\Util::writeLog("notify", "Could not send notification: " . $e->getMessage(), OCP\Util::ERROR);
            throw $e;
            /* TODO: good exception handling and throwing, e.g.:
             * - throw exception when there are errors
             * - return false (or null?) when the app/class is blacklisted
             */
        }
    }
    
    /**
     * @brief get the latest notifications for the logged in user
     * @param $uid user id
     * @param $count limit for number of notifications
     * @return array with notifications
     */
    public static function getNotifications($uid = null, $count = null, $lang = null, $html = true) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return array();
			}
		}
        if(!$count) {
			$notifyStmt = OCP\DB::prepare("SELECT n.id, n.uid, n.read, n.moment, c.appid AS app, c.name AS class, c.summary, c.content FROM *PREFIX*notifications AS n INNER JOIN *PREFIX*notification_classes AS c ON n.class = c.id WHERE n.uid = ? ORDER BY n.read ASC, n.moment DESC");
			$result = $notifyStmt->execute(array($uid));
		} else {
			$notifyStmt = OCP\DB::prepare("SELECT n.id, n.uid, n.read, n.moment, c.appid AS app, c.name AS class, c.summary, c.content FROM *PREFIX*notifications AS n INNER JOIN *PREFIX*notification_classes AS c ON n.class = c.id WHERE n.uid = ? ORDER BY n.read ASC, n.moment DESC LIMIT ?");
			$result = $notifyStmt->execute(array($uid, $count));
		}
        $notifications = $result->fetchAll();
        $paramStmt = OCP\DB::prepare("SELECT key, value FROM *PREFIX*notification_params WHERE nid = ?");
        foreach($notifications as $i => $n) {
            $l = OC_L10N::get($n["app"], $lang);
            $notifications[$i]["summary"] = $l->t($n["summary"]);
            $notifications[$i]["content"] = $l->t($n["content"]);
            $result = $paramStmt->execute(array($n["id"]));
            while($param = $result->fetchRow()) {
				if(in_array($param["key"], array('href', 'img'))) {
					$notifications[$i][$param["key"]] = $param["value"];
				} elseif(strpos($notifications[$i]["content"], "{{$param["key"]}}") !== false) {
					if($html) {
						$notifications[$i]["content"] = str_replace("{{$param["key"]}}", sprintf('<span class="notify_param notify_param_%s">%s</span>', $param["key"], $param["value"]), $notifications[$i]["content"]);
					} else {
						$notifications[$i]["content"] = str_replace("{{$param["key"]}}", $param["value"], $notifications[$i]["content"]);
					}
				} elseif(in_array($param["key"], array('id', 'read'))) {
					// these params aren't allowed
					// FIXME check before writing to db??
					continue;
				} else {
					$notifications[$i]["params"][$param["key"]] = $param["value"];
				}
            }
        }
        return $notifications;
    }
	
    /**
     * @brief get the notification with the given id
     * @param $id notification id
     * @return notification as an associative array
     */
    public static function getNotificationById($id) {
		$stmt = OCP\DB::prepare("SELECT n.id, n.uid, n.read, n.moment, c.appid AS app, c.name AS class, c.summary, c.content FROM *PREFIX*notifications AS n INNER JOIN *PREFIX*notification_classes AS c ON n.class = c.id WHERE n.id = ?");
		$result = $stmt->execute(array((int) $id));
		$notification = $result->fetchRow();
        $paramStmt = OCP\DB::prepare("SELECT key, value FROM *PREFIX*notification_params WHERE nid = ?");
		$result = $paramStmt->execute(array((int) $id));
		while($param = $result->fetchRow()) {
			if(in_array($param["key"], array('href', 'img'))) {
				$notification[$param["key"]] = $param["value"];
			} elseif(strpos($notification["content"], "{{$param["key"]}}") !== false) {
				$notification["content"] = str_replace("{{$param["key"]}}", sprintf('<span class="notify_param notify_param_%s">%s</span>', $param["key"], $param["value"]), $notification["content"]);
			} elseif(in_array($param["key"], array('id', 'read'))) {
				// these params aren't allowed
				// FIXME check before writing to db??
				continue;
			} else {
				$notification["params"][$param["key"]] = $param["value"];
			}
		}
        return $notification;
    }

	/**
	 * @brief mark all notifications of the given user as read
	 * @param $uid
	 * @return number of affected rows
	 */
	public static function markReadByUser($uid = null, $read = true) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$readByUserStmt)) {
			self::$readByUserStmt = OCP\DB::prepare("UPDATE *PREFIX*notifications SET read = ? WHERE uid = ?");
		}
		self::$readByUserStmt->execute(array((int) $read, $uid));
		return self::$readByUserStmt->numRows();
	}
	
	/**
	 * @brief mark all notifications with the given class id as read
	 * @param $uid user id
	 * @param $class class id or array with multiple class ids
	 * @param $read the (boolean) value to set the read column to
	 */
	public static function markReadByClassId($uid = null, $class, $read = true) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$readByClassIdStmt)) {
			self::$readByClassIdStmt = OCP\DB::prepare("UPDATE *PREFIX*notifications SET read = ? WHERE class = ? AND uid = ?");
		}
		if(!is_array($class)) {
			if($class === false) {
				return 0;
			}
			$class = array($class);
		}
		$return = 0;
		foreach($class as $c) {
			self::$readByClassIdStmt->execute(array((int) $read, $c, $uid));
			$return += self::$readByClassIdStmt->numRows();
		}
		return $return;
	}
	
	/**
	 * @brief mark all notifications of the given app (and optional class) as read
	 * @param $app
	 * @param $class
	 * @return number of affected rows
	 */
	public static function markReadByApp($uid = null, $app, $class = null, $read = true) {
		return self::markReadByClassId($uid, self::getClassId($app, $class), $read);
	}
	
	/**
     * @brief mark the notification with the given id as read
     * @param $uid user id
     * @param $id notification id
     * @param $read the (boolean) value to set the read column to
     * @return number of affected rows
     * @fixme do we really need $uid here??
     */
	public static function markReadById($uid = null, $id, $read = true) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$readByIdStmt)) {
			self::$readByIdStmt = OCP\DB::prepare("UPDATE *PREFIX*notifications SET read = ? WHERE id = ? AND uid = ?");
		}
		self::$readByIdStmt->execute(array((int) $read, $id, $uid));
		return self::$readByIdStmt->numRows();
	}
	
	/**
	 * @brief delete all notifications of the given user
	 * @param $uid
	 * @return number of affected rows
	 */
	public static function deleteByUser($uid = null) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$deleteParamsByUserStmt)) {
			self::$deleteParamsByUserStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notification_params WHERE nid IN (SELECT id FROM *PREFIX*notifications WHERE uid = ?)");
		}
		if(!isset(self::$deleteByUserStmt)) {
			self::$deleteByUserStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE uid = ?");
		}
		self::$deleteParamsByUserStmt->execute(array($uid));
		self::$deleteByUserStmt->execute(array($uid));
		return self::$deleteByUserStmt->numRows();
	}
	
	/**
	 * @brief delete all notifications with the given class id
	 * @param $uid user id
	 * @param $class class id or array with multiple class ids
	 */
	public static function deleteByClassId($uid = null, $class) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$deleteParamsByClassIdStmt)) {
			self::$deleteParamsByClassIdStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notification_params WHERE nid IN (SELECT id FROM *PREFIX*notifications WHERE class = ? AND uid = ?)");
		}
		if(!isset(self::$deleteByClassIdStmt)) {
			self::$deleteByClassIdStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE class = ? AND uid = ?");
		}
		if(!is_array($class)) {
			if($class === false) {
				return 0;
			}
			$class = array($class);
		}
		$return = 0;
		foreach($class as $c) {
			self::$deleteParamsByClassIdStmt->execute(array($c, $uid));
			self::$deleteByClassIdStmt->execute(array($c, $uid));
			$return += self::$deleteByClassIdStmt->numRows();
		}
		return $return;
	}
	
	/**
	 * @brief delete all notifications of the given app (and optional class)
	 * @param $app
	 * @param $class
	 * @return number of affected rows
	 */
	public static function deleteByApp($uid = null, $app, $class = null) {
		return self::deleteByClassId($uid, self::getClassId($app, $class));
	}
	
	/**
     * @brief delete the notification with the given id
     * @param $uid user id
     * @param $id notification id
     * @return number of affected rows
     */
	public static function deleteById($uid = null, $id) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$deleteParamsByIdStmt)) {
			self::$deleteParamsByIdStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notification_params WHERE nid IN (SELECT id FROM *PREFIX*notifications WHERE id = ? AND uid = ?)");
		}
		if(!isset(self::$deleteByIdStmt)) {
			self::$deleteByIdStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE id = ? AND uid = ?");
		}
		self::$deleteParamsByIdStmt->execute(array($id, $uid));
		self::$deleteByIdStmt->execute(array($id, $uid));
		return self::$deleteByIdStmt->numRows();
	}
	
	/**
     * @brief delete the notification with the given read flag
     * @param $uid user id
     * @param $read read flag
     * @return number of affected rows
     */
	public static function deleteByRead($uid = null, $read) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(!isset(self::$deleteParamsByReadStmt)) {
			self::$deleteParamsByReadStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notification_params WHERE nid IN (SELECT id FROM *PREFIX*notifications WHERE uid = ? AND read = ?)");
		}
		if(!isset(self::$deleteByReadStmt)) {
			self::$deleteByReadStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE uid = ? AND read = ?");
		}
		self::$deleteParamsByReadStmt->execute(array($uid, $read));
		self::$deleteByReadStmt->execute(array($uid, $read));
		return self::$deleteByReadStmt->numRows();
	}
	
	/**
     * @brief get all notification classes
     * @param $uid user id to get the blacklist flag
     * @return array with notification classes
     */
    public static function getClasses($uid = null) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return array();
			}
		}
		if(!isset(self::$classesStmt)) {
			// b.class + 1 just to be sure that there are no issues if class id is zero
			// additionally, I tried COUNT(b.class) instead of COALESCE(...) but it didn't give me the expected results
			self::$classesStmt = OCP\DB::prepare("SELECT c.id, c.appid, c.name, c.summary, COALESCE(MIN(1, b.class + 1), 0) AS blocked FROM *PREFIX*notification_classes AS c LEFT JOIN *PREFIX*notification_blacklist AS b ON c.id = b.class AND b.uid = ? ORDER BY c.appid ASC, c.name ASC");
		}
		$result = self::$classesStmt->execute(array($uid));
		return $result->fetchAll();
	}
	
	/**
	 * @brief add/remove a notification class to/from the blacklist
	 * @param string $uid user
	 * @param int $class class id
	 * @param boolean $block true to add, false to remove from blacklist
	 */
	public static function setBlacklist($uid = null, $class, $block) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				throw new Exception('Not logged in!');
			}
		}
		$stmt = null;
		if($block) {
			if(!isset(self::$addToBlacklistStmt)) {
				self::$addToBlacklistStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notification_blacklist (uid, class) VALUES (?, ?)");
			}
			$stmt = self::$addToBlacklistStmt;
		} else {
			if(!isset(self::$removeFromBlacklistStmt)) {
				self::$removeFromBlacklistStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notification_blacklist WHERE uid = ? AND class = ?");
			}
			$stmt = self::$removeFromBlacklistStmt;
		}
		$stmt->execute(array($uid, $class));
	}
	
	/**
	 * @brief check if the given class is in the given user's blacklist
	 * @param string $uid user
	 * @param string $class class id
	 * @return true if the class is blocked by the user, otherwise false
	 */
	private static function isBlacklisted($uid, $class) {
		if(!isset(self::$isBlockedStmt)) {
			self::$isBlockedStmt = OCP\DB::prepare("SELECT COUNT(*) FROM *PREFIX*notification_blacklist WHERE uid = ? AND class = ?");
		}
		return (bool)self::$isBlockedStmt->execute(array($uid, $class))->fetchOne();
    }

	/**
	 * Hook listeners
	 */
	public static function post_deleteUser($args) {
		$uid = $args["uid"];
		$stmt = OCP\DB::prepare("DELETE FROM *PREFIX*notification_blacklist WHERE uid = ?");
		$stmt->execute(array($uid));
		self::deleteByUser($uid);
	}

	public static function post_shared($args) {
		// use this to debug sharing hook emits:
		//OCP\Util::writeLog("notify", "shared hook: " . print_r($args, true), OCP\Util::DEBUG);
		if(!$args["shareWith"]) {
			return;
		}
		switch($args["itemType"]) {
		case "event":
			self::sendUserNotification("notify", "sharedEvent", $args["shareWith"], array(
				"user" => $args["uidOwner"],
				"title" => $args["itemTarget"],
				"href" => OCP\Util::linkTo("calendar", "index.php"),
				"img" => OCP\Util::imagePath("calendar", "icon.svg")
			));
			break;
		case "calendar":
			self::sendUserNotification("notify", "sharedCal", $args["shareWith"], array(
				"user" => $args["uidOwner"],
				"name" => $args["itemTarget"],
				"href" => OCP\Util::linkTo("calendar", "index.php"),
				"img" => OCP\Util::imagePath("calendar", "icon.svg")
			));
			break;
		case "file":
			$params = array(
				"user" => $args["uidOwner"],
				"name" => $args["fileTarget"],
				"href" => OCP\Util::linkTo("files", "index.php", array("dir" => "/Shared" . rtrim(dirname($args["fileTarget"]), "/"))),
			);
			// get the mime type icon:
			$fileMimeStmt = OCP\DB::prepare("SELECT mimetype FROM *PREFIX*fscache WHERE id = ?");
			$result = $fileMimeStmt->execute(array($args["fileSource"]));
			if($result) {
				$mime = $result->fetchColumn();
				$mimeParts = explode("/", $mime);
				if(strtolower($mimeParts[0]) == "image") {
					$params["img"] = OCP\Util::linkTo("files", "ajax/download.php", array("files" => "/Shared" . $args["fileTarget"]));
					$params["class"] = "notify_fullImg";
				} else {
					$params["img"] = OC_Helper::mimetypeIcon($mime);
				}
			} else {
				$mimeImg = OCP\Util::imagePath("core", "filetypes/file.png");
			}
			self::sendUserNotification("notify", "sharedFile", $args["shareWith"], $params);
			break;
		case "folder":
			self::sendUserNotification("notify", "sharedFolder", $args["shareWith"], array(
				"user" => $args["uidOwner"],
				"name" => $args["fileTarget"],
				"href" => OCP\Util::linkTo("files", "index.php", array("dir" => "/Shared" . $args["fileTarget"])),
				"img" => OCP\Util::imagePath("core", "filetypes/folder.png")
			));
			break;
		case "addressbook":
			self::sendUserNotification("notify", "sharedAbook", $args["shareWith"], array(
				"user" => $args["uidOwner"],
				"name" => $args["itemTarget"],
				"href" => OCP\Util::linkTo("contacts", "index.php"),
				"img" => OCP\Util::imagePath("settings", "users.svg")
			));
		default:
		}
	}
}
