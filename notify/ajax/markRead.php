<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
// FIXME CSRF !!
if(isset($_POST["id"])) {
	$id = (int)$_POST["id"];
} else {
	OCP\JSON::error();
}
if(isset($_POST["read"])) {
	$read = (bool)$_POST["read"];
}
try {
	$num = OC_Notify::markReadById(null, $id, $read);
	OCP\JSON::success(array("unread" => OC_Notify::getUnreadNumber(), "num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
exit;
