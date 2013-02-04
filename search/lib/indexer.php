<?php

namespace OCA\Search;

// add the Zend Lucene library
require_once 'Zend/Search/Lucene.php';

/**
 * Edit the Lucene database by adding or deleting items from the index. The 
 * index is created in OC_Search_Provider_Lucene, as this contains the search()
 * functionality.
 * @author Andrew Brown
 */
class Indexer {

    /**
     * Index a search result in the Lucene database.
     * @param string $type
     * @param mixed $id
     * @return boolean
     */
    static public function index($type, $id = null) {
	if (!$type || !$id) {
	    return false;
	}
	$type = strtolower($type);
	// create search result
	$class = 'OC_Search_Result_' . ucfirst($type);
	if (!class_exists($class)) {
	    \OC_Log::write('search', 'Tried and failed to create an entry for class: ' . $class, \OC_Log::WARN);
	    return false;
	}
	// create result
	$result = $class::fillFromId($id);
	// create document
	$doc = new \Zend_Search_Lucene_Document();
	foreach ($result as $property => $value) {
	    if ($property == 'name') {
		$doc->addField(\Zend_Search_Lucene_Field::text($property, $value));
	    } elseif ($property == 'id') {
		$doc->addField(\Zend_Search_Lucene_Field::keyword($property, self::getId($type, $id)));
	    } elseif ($property == 'default_columns') {
		continue;
	    } else {
		$doc->addField(\Zend_Search_Lucene_Field::text($property, $value));
	    }
	}
	$doc->addField(\Zend_Search_Lucene_Field::keyword('class', $class));
	$doc->addField(\Zend_Search_Lucene_Field::keyword('all', 'yes'));
	// add content if possible
	if ($type == 'file') {
	    $doc->addField(\Zend_Search_Lucene_Field::unstored('content', $result->getContent()));
	}
	// remove prior entries
	$index = \OC_Search_Provider_Lucene::getIndex();
	self::delete($type, $id);
	// add document to index
	$index->addDocument($doc);
	$index->commit();
	// return
	return true;
    }

    /**
     * Delete a search result from the Lucene database.
     * @param string $type
     * @param mixed $id
     * @return boolean
     */
    static public function delete($type, $id = null) {
	if (!$type || !$id) {
	    return false;
	}
	// create search result
	$class = 'OC_Search_Result_' . ucfirst($type);
	if (!class_exists($class)) {
	    \OC_Log::write('search', 'Tried and failed to delete an entry for class: ' . $class, \OC_Log::WARN);
	    return false;
	}
	// remove entries
	$index = \OC_Search_Provider_Lucene::getIndex();
	$hits = $index->find('id:' . self::getId($type, $id));
	foreach ($hits as $hit) {
	    $index->delete($hit);
	}
	$index->commit();
	// return
	return true;
    }

    /**
     * Create ID for a search result, e.g. 'contact/23'
     * @param string $type name of OC_Search_Result descendant
     * @param string $id app-specific identifier
     * @return string
     */
    public static function getId($type, $id) {
	return (is_int($id)) ? strtolower($type) . '/' . $id : strtolower($type) . '/' . md5($id);
    }

}
