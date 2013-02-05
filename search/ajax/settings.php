<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('search');
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