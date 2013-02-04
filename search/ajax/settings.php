<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//header('Content-type: text/html; charset=UTF-8');
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('search');
//OC_App::loadApp('search');
//var_dump(get_declared_classes());
//OC::autoload('OC_EventSource
OC::autoload('OC_Search_Provider_Lucene');

/**
 * Main control
 */
if ($_GET['operation']) {
    switch ($_GET['operation']) {
	case 'reindex':
	    set_time_limit(0); // scanning can take ages
	    $eventSource = new \OC_EventSource();
	    $total = OC_Search_Provider_Lucene::reindexAll($eventSource);
	    $eventSource->send('success', $total);
	    $eventSource->close();
	    break;
	case 'optimize':
	    OC_Search_Provider_Lucene::optimizeIndex();
	    break;
	case 'enable':
	    OCP\Config::setUserValue(OCP\User::getUser(), 'search', 'lucene_enabled', 'yes');
	    echo 'yes';
	    break;
	case 'disable':
	    OCP\Config::setUserValue(OCP\User::getUser(), 'search', 'lucene_enabled', 'no');
	    echo 'no';
	    break;
	default:
	    OCP\JSON::error(array('cause' => 'Unknown operation'));
    }
}