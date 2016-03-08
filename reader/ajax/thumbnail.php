<?php

	OCP\JSON::checkLoggedIn();
	OCP\JSON::checkAppEnabled('reader');

	require_once('reader/lib/thumbnail.php');
	$img = $_GET['filepath'];
	$image = thumb($img);
	if ($image) {
		OCP\Response::enableCaching(3600 * 24); // 24 hour
		$image->show();
	}

?>
