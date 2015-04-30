<?php

$manager = \OC::$server->getEncryptionManager();
$manager->registerEncryptionModule('OC_DUMMY_MODULE', 'Dummy Encryption Module', function() {
	$module = new \OCA\Encryption_Dummy\DummyModule();
	return $module;
});

