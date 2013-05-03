<?php
OCP\JSON::checkAdminUser();

if (isset($_POST['allowUsers']) && $_POST['allowUsers']) {
	OCP\Config::setAppValue('external', 'allowUsers', 'true');
} else{
	OCP\Config::setAppValue('external', 'allowUsers', 'false');
}
OCP\JSON::success();
