<?php

// @TODO remove after debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

function pr($thing) {
    echo '<pre>';
    if (is_null($thing))
	echo 'NULL';
    elseif (is_bool($thing))
	echo $thing ? 'TRUE' : 'FALSE';
    else
	print_r($thing);
    echo '</pre>' . "\n";
    return ($thing) ? true : false; // for testing purposes
}

OCP\App::checkAppEnabled('search');

header('Content-type: text/plain');
$hits = OC_Search_Provider_Lucene::search('*');
pr($hits);