<?php

// check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('search');

// add CSS
OCP\Util::addStyle('search', 'list-view');

// add JS
OCP\Util::addScript('search', 'columns');

// activate navigation link
OCP\App::setActiveNavigationEntry('search');

// get query
$query = (isset($_GET['query'])) ? $_GET['query'] : false;
if($query === false && array_key_exists('search_query', $_SESSION)){
    $query = $_SESSION['search_query'];
}
else{
    $_SESSION['search_query'] = $query;
}

// get results
$results = array();
if ($query) {
    // if Lucene is available, only search using it
    if(OCP\Config::getUserValue(OCP\User::getUser(), 'search', 'lucene_enabled', 'no') == 'yes'){
	$results = OC_Search::search($query, 'OC_Search_Provider_Lucene');
    }
    else{
	$results = OC_Search::search($query);
    }
}

// separate results by type
$_results = array();
foreach ($results as $result) {
    $class = get_class($result);
    $_results[$class][] = $result;
}
$results = $_results;

$html = '';
foreach ($results as $class => $class_results) {
    // get columns
    $columns = $class_results[0]->default_columns;

    // create properties <thead> HTML
    $_thead = new OCP\Template('search', 'part.properties');
    $_thead->assign('title', $class_results[0]::TITLE);
    $_thead->assign('properties', $columns);
    $thead = $_thead->fetchPage();

    // create result rows <tr> HTML
    $tbody = '';
    foreach ($class_results as $result) {
	// run HTML formatting
	$result->formatToHtml();
	// do templating
	$tr = new OCP\Template('search', 'part.result');
	$tr->assign('result', $result, false);
	$tr->assign('columns', $columns, true);
	$tbody .= $tr->fetchPage() . "\n\t";
    }

    // add to html
    $type_html = new OCP\Template('search', 'part.list');
    $type_html->assign('title', $class_results[0]::TITLE, false);
    $type_html->assign('properties', $thead, false);
    $type_html->assign('results', $tbody, false);
    $html .= $type_html->fetchPage() . "\n";
}

// create results <tbody> HTML
$template = new OCP\Template('search', 'index', 'user');
$template->assign('query', $query, false);
$template->assign('html', $html, false);
$template->printPage();