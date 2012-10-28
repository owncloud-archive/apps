<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
// FIXME CSRF !!
if(isset($_POST["id"])) {
	$id = (int)$_POST["id"];
} else {
	OCP\JSON::error(array("message" => 'Missing id argument'));
	exit;
}
try {
	$num = OC_Notify::deleteById(null, $id);
	OCP\JSON::success(array("num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
exit;
