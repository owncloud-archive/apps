<?php

namespace OCA\Search;

/**
 * Manage file indices in the Lucene database; these methods are used by app.php
 * to route events to the correct indexer task. Additionally, the added, edited,
 * or deleted items are indexed in the background using ownCloud's built-in 
 * 'cron' system.
 * @author Jörn Dreyer <jfd@butonic.de>
 * @author Andrew Brown
 */
class Hooks {

    public static function indexAlbum(int $id) {
	$parameters = array('album', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexArticle(int $id) {
	$parameters = array('article', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexArtist(int $id) {
	$parameters = array('artist', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexBookmark(int $id) {
	$parameters = array('bookmark', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexContact(int $id) {
	$parameters = array('contact', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexEvent(int $id) {
	$parameters = array('event', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexFeed(int $id) {
	$parameters = array('feed', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function indexFile(array $param) {
	if (isset($param['path'])) {
	    $parameters = array('file', $param['path']);
	    OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
	    // handle images, PDFs
	    if (OCP\App::isEnabled('reader') && $param['mimetype'] == 'application/pdf') {
		$parameters = array('book', $param['path']);
		OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
	    } elseif (OCP\App::isEnabled('gallery') && strpos($param['mimetype'], 'image') !== false) {
		$parameters = array('image', $param['path']);
		OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
	    }
	} else {
	    OC_Log::write('search', 'Missing path parameter.', OC_Log::WARN);
	}
    }

    public static function indexSong(int $id) {
	$parameters = array('song', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
    }

    public static function deleteAlbum(int $id) {
	$parameters = array('album', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteArticle(int $id) {
	$parameters = array('article', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteArtist(int $id) {
	$parameters = array('artist', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteBookmark(int $id) {
	$parameters = array('bookmark', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteContact(int $id) {
	$parameters = array('contact', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteEvent(int $id) {
	$parameters = array('event', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteFeed(int $id) {
	$parameters = array('feed', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

    public static function deleteFile(array $param) {
	if (isset($param['path'])) {
	    $parameters = array('file', $param['path']);
	    OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
	    // handle images, PDFs
	    if (OCP\App::isEnabled('reader') && $param['mimetype'] == 'application/pdf') {
		$parameters = array('book', $param['path']);
		OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
	    } elseif (OCP\App::isEnabled('gallery') && strpos($param['mimetype'], 'image') !== false) {
		$parameters = array('image', $param['path']);
		OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'index', $parameters);
	    }
	} else {
	    OC_Log::write('search', 'Missing path parameter.', OC_Log::WARN);
	}
    }

    public static function deleteSong(int $id) {
	$parameters = array('song', $id);
	OCP\BackgroundJob::addQueuedTask('search', 'OC_Search_Lucene_Indexer', 'delete', $parameters);
    }

}
