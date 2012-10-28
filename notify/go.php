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
 * redirect to the notification's href
 */

try {
	OCP\App::checkAppEnabled("notify");
	OCP\User::checkLoggedIn();
	$id = (int) $_GET["id"];
	$uid = OCP\User::getUser();
	$notification = OC_Notify::getNotificationById((int) $id);
	if($notification && $notification["uid"] == $uid) {
		OC_Notify::markReadById($uid, $id, true);
		if($notification["app"] == "notify") {
			switch($notification["class"]) {
			case "sharedCalendar":
			case "sharedEvent": $href = OCP\Util::linkTo("calendar", "index.php"); break;
			case "sharedAbook": $href = OCP\Util::linkTo("contacts", "index.php"); break;
			case "sharedFile": $href = OCP\Util::linkTo("files", "index.php", array(
				"dir" => "/Shared" . rtrim(dirname($notification["params"]["name"]), "/")
				)); break;
			case "sharedFolder": $href = OCP\Util::linkTo("files", "index.php", array(
				"dir" => "/Shared" . $notification["params"]["name"]
				)); break;
			default: $href = OCP\Util::linkTo("files", "index.php");
			}
		} else {
			if($notification["href"]) {
				$href = $notification["href"];
			} else {
				$href = OCP\Util::linkTo("files", "index.php");
			}
		}
		OCP\Response::redirect($href);
	} else {
		$tmpl = new OCP\Template("", $notification ? "403" : "404", "guest");
		$tmpl->assign("file", sprintf("ID: %s", $id));
		$tmpl->printPage();
	}
} catch(Exception $e) {
	$tmpl = new OCP\Template("", "404", "guest");
	$tmpl->assign("file", $e->getMessage());
	$tmpl->printPage();
}
