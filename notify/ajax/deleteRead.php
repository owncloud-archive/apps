<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
// FIXME CSRF !!
if(isset($_POST["read"])) {
	$read = (strtolower($_POST["read"]) != "false" and (bool)$_POST["read"]);
} else {
	OCP\JSON::error(array("message" => "Missing argument"));
	exit;
}
try {
	$num = OC_Notify::deleteByRead(null, $read);
	OCP\JSON::success(array("num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
exit;
