<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
// FIXME CSRF !!
if(!(isset($_POST['id']) and isset($_POST['block']))) {
	OCP\JSON::error(array('message' => 'Missing arguments'));
	exit;
}
$id = (int)$_POST['id'];
$block = (bool)$_POST['block'];
try {
	OC_Notify::setBlacklist(null, $id, $block);
	OCP\JSON::success();
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
exit;
