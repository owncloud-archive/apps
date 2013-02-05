<?php

// register app navigation
OCP\App::addNavigationEntry(array(
    'id' => 'search',
    'href' => OCP\Util::linkTo('search', 'index.php'),
    'icon' => OCP\Util::imagePath('', 'actions/search.svg'),
    'name' => 'Adv. Search',
    'order' => 50
));

// register classes
OC::$CLASSPATH['OCA\Search\Helper'] = 'search/lib/helper.php';
OC::$CLASSPATH['OCA\Search\Indexer'] = 'search/lib/indexer.php';
OC::$CLASSPATH['OCA\Search\Hooks'] = 'search/lib/hooks.php';
OC::$CLASSPATH['OCA\Search\PdfParser'] = 'search/lib/pdfparser.php';
OC::$CLASSPATH['OC_Search_Provider_Lucene'] = 'search/lib/lucene.php';

// add new search provided, and remove all others
if (true) {
    //OC_Search::clearProviders();
    OC_Search::registerProvider('OC_Search_Provider_Lucene');
}

// add settings page
OCP\App::registerPersonal('search', 'settings');

// add file/gallery/reader events
OCP\Util::connectHook('OC_Filesystem', 'post_write', 'OC_Search_Lucene_Hooks', 'indexFile'); // post_create is ignored, as write will be triggered afterwards anyway
OCP\Util::connectHook('OC_Filesystem', 'post_rename', 'OC_Search_Lucene_Hooks', 'indexFile');
OCP\Util::connectHook('OC_Filesystem', 'delete', 'OC_Search_Lucene_Hooks', 'delete');

// add bookmark events
if (OCP\App::isEnabled('bookmarks')) {
    OCP\Util::connectHook('OC_Bookmarks_Bookmarks', 'addBookmark', 'OC_Search_Lucene_Hooks', 'indexBookmark');
    OCP\Util::connectHook('OC_Bookmarks_Bookmarks', 'editBookmark', 'OC_Search_Lucene_Hooks', 'indexBookmark');
    OCP\Util::connectHook('OC_Bookmarks_Bookmarks', 'deleteBookmark', 'OC_Search_Lucene_Hooks', 'deleteBookmark');
}

// add calendar events
if (OCP\App::isEnabled('calendar')) {
    OCP\Util::connectHook('OC_Calendar', 'addEvent', 'OC_Search_Lucene_Hooks', 'indexEvent');
    OCP\Util::connectHook('OC_Calendar', 'editEvent', 'OC_Search_Lucene_Hooks', 'indexEvent');
    OCP\Util::connectHook('OC_Calendar', 'moveEvent', 'OC_Search_Lucene_Hooks', 'indexEvent');
    OCP\Util::connectHook('OC_Calendar', 'deleteEvent', 'OC_Search_Lucene_Hooks', 'deleteEvent');
}

// add contact events
if (OCP\App::isEnabled('contacts')) {
    OCP\Util::connectHook('\OCA\Contacts\VCard', 'post_createVCard', 'OC_Search_Lucene_Hooks', 'indexContact');
    OCP\Util::connectHook('\OCA\Contacts\VCard', 'post_updateVCard', 'OC_Search_Lucene_Hooks', 'indexContact');
    OCP\Util::connectHook('\OCA\Contacts\VCard', 'post_deleteVCard', 'OC_Search_Lucene_Hooks', 'deleteContact');
}

// add media events
if (OCP\App::isEnabled('media')) {
    OCP\Util::connectHook('OC_MEDIA_COLLECTION', 'addAlbum', 'OC_Search_Lucene_Hooks', 'indexAlbum');
    OCP\Util::connectHook('OC_MEDIA_COLLECTION', 'addArtist', 'OC_Search_Lucene_Hooks', 'indexArtist');
    OCP\Util::connectHook('OC_MEDIA_COLLECTION', 'addSong', 'OC_Search_Lucene_Hooks', 'indexSong');
    OCP\Util::connectHook('OC_MEDIA_COLLECTION', 'deleteSong', 'OC_Search_Lucene_Hooks', 'deleteSong');
}

// add news events
if (OCP\App::isEnabled('news')) {
    OCP\Util::connectHook('OCA_News', 'addFeed', 'OC_Search_Lucene_Hooks', 'indexFeed');
    OCP\Util::connectHook('OCA_News', 'editFeed', 'OC_Search_Lucene_Hooks', 'indexFeed');
    OCP\Util::connectHook('OCA_News', 'deleteFeed', 'OC_Search_Lucene_Hooks', 'deleteFeed');
    OCP\Util::connectHook('OCA_News', 'addArticle', 'OC_Search_Lucene_Hooks', 'indexArticle');
    OCP\Util::connectHook('OCA_News', 'editArticle', 'OC_Search_Lucene_Hooks', 'indexArticle');
    OCP\Util::connectHook('OCA_News', 'deleteArticle', 'OC_Search_Lucene_Hooks', 'deleteArticle');
}