<?php

// check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('search');

// get index data
$index = OC_Search_Provider_Lucene::getIndex();
$index_created = ($index) ? true : false;
$index_size = $index->count();
$_lucene_enabled = OCP\Config::getUserValue(OCP\User::getUser(), 'search', 'lucene_enabled', 'no');
$lucene_enabled = ($_lucene_enabled == 'yes') ? true : false;

// add JS
OCP\Util::addScript('search', 'settings');

// do templating
$tmpl = new OC_Template('search', 'settings');
$tmpl->assign('lucene_enabled', $lucene_enabled);
$tmpl->assign('index_created', $index_created);
$tmpl->assign('index_size', $index_size);
return $tmpl->fetchPage();