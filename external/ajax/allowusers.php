<?php
OCP\User::checkAdminUser();

if (isset($_POST['allowUsers']) && $_POST['allowUsers']) {
	OCP\Config::setAppValue('external', 'allowUsers', 'true');
} else{
	OCP\Config::setAppValue('external', 'allowUsers', 'false');
}
echo 'true';
