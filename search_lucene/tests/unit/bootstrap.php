<?php

global $RUNTIME_NOAPPS;
$RUNTIME_NOAPPS = true;

define('PHPUNIT_RUN', 1);

require_once __DIR__.'/../../../../lib/base.php';

if(!class_exists('PHPUnit_Framework_TestCase')) {
	require_once('PHPUnit/Autoload.php');
}

//add 3rdparty folder to include path
$dir = __DIR__.'/../../3rdparty';
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

OC::$CLASSPATH['Zend_Search_Lucene_Interface'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Interface.php';
OC::$CLASSPATH['Zend_Search_Lucene_Search_QueryHit'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Search/QueryHit.php';

OC_Hook::clear();
OC_Log::$enabled = false;
