<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
// FIXME CSRF !!
if($_SERVER["REQUEST_METHOD"] != 'POST') {
	OCP\JSON::error(array("message" => 'POST required for this action!'));
	exit;
}
try {
	$num = OC_Notify::markReadByUser();
	OCP\JSON::success(array("num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
exit;
