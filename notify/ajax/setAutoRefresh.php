<?php
OCP\JSON::checkLoggedIn();
if(isset($_POST["flag"])) {
	$flag = (bool)$_POST["flag"];
} else {
	OCP\JSON::error(array("message" => "Missing flag argument"));
}
try {
	OCP\Config::setUserValue(OCP\User::getUser(), 'notify', 'autorefresh', $flag);
	OCP\JSON::success();
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
