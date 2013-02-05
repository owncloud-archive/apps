<?php

$new_include_path = OC_App::getAppPath('search').DIRECTORY_SEPARATOR.'lib';
set_include_path(get_include_path().PATH_SEPARATOR.$new_include_path);
require_once 'Zend/Search/Lucene.php';
require_once 'Zend/Search/Lucene/Index/Term.php';
require_once 'Zend/Search/Lucene/Search/Query/Term.php';

/**
 * @author Jörn Dreyer <jfd@butonic.de>
 * @author Andrew Brown
 */
class OC_Search_Provider_Lucene extends OC_Search_Provider {

    /**
     * Searches the user's index
     * @author Jörn Dreyer <jfd@butonic.de>
     * @author Andrew Brown
     * @param string $query lucene search query
     * @return array of OC_Search_Result
     */
    public function search($query) {
        $results = array();
        // query modification
        if ($query == '*') {
            $query = 'all:yes';
        }
        // 
        if ($query !== null) {
            $index = self::getIndex();
            try {
                $hits = $index->find($query);
            } catch (Exception $e) {
                //var_dump($e);
                $hits = array();
            }
            foreach ($hits as $hit) {
                //var_dump($hit);
                if (!@$hit->class) {
                    OC_Log::write('search', 'A hit did not have the "class" property; this means that OCA\Search\Indexer is missbehaving.', OC_Log::WARN);
                    continue;
                }
                $class = $hit->class;
                if (!class_exists($class)) {
                    OC_Log::write('search', 'Tried and failed to display result for class: ' . $class, OC_Log::WARN);
                    continue;
                }
                $result = new $class;
                self::fillResultFromHit($result, $hit);
                $results[] = $result;
            }
        }
        return $results;
    }

    /**
     * Return the current user's Lucene index, creating one if necessary.
     * @staticvar Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Interface
     */
    static public function getIndex() {
        static $index = null;
        if ($index === null) {
            try {
                Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                        new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive()
                );
                // build path
                $path = self::getPath();
                // open or create
                if (file_exists($path)) {
                    $index = Zend_Search_Lucene::open($path);
                } else {
                    $index = Zend_Search_Lucene::create($path);
                }
            } catch (Exception $e) {
                OC_Log::write('search', $e->getMessage() . ' Trace:\n' . $e->getTraceAsString(), OC_Log::ERROR);
                return null;
            }
        }
        // return
        return $index;
    }

    /**
     * Return path to Lucene index
     * @return string
     */
    static public function getPath() {
        $path = OC_Config::getValue('datadirectory', OC::$SERVERROOT . '/data');
        $path .= '/' . OC_User::getUser() . '/lucene_index';
        return $path;
    }

    /**
     * Re-indexes all of the results returned by all of the providers; this relies
     * on all providers returning all available results for their type when 
     * queried with the string '*'.
     * @author Andrew Brown
     * @param Zend_Search_Lucene_Interface $index
     * @return int number of results indexed.     * 
     */
    static public function reindexAll(OC_EventSource $event_source = null) {
        // remove all prior documents
        self::deleteAll();
        // remove lucene from search providers
        OC_Search::removeProvider('OC_Search_Provider_Lucene');
        // get results
        $results = OC_Search::search('*'); // every search provider must return all results when given a '*' query
        $count = 0;
        $total = count($results);
        foreach ($results as $result) {
            // check type
            if (!is_a($result, 'OC_Search_Result')) {
                OC_Log::write('search', 'The result returned by a search provider was not an "OC_Search_Result". The result was not indexed.', OC_Log::WARN);
                continue;
            }
            $type = str_ireplace('OC_Search_Result_', '', get_class($result));
            // index
            if (is_a($result, 'OC_Search_Result_File')) {
                $path = \OC\Files\Filesystem::getPath($result->id); // to preserve past behavior; the file hooks return paths, not IDs
                OCA\Search\Indexer::index($type, $path);
            } else {
                OCA\Search\Indexer::index($type, $result->id);
            }
            $count++;
            // send to event source
            if ($event_source !== null) {
                $event_source->send('indexing', array('name' => $result->name, 'count' => $count, 'total' => $total));
            }
        }
        // return
        return $count;
    }

    /**
     * Remove all documents from index
     */
    static public function deleteAll() {
        $path = self::getPath();
        // remove all index files
        self::deleteDir($path);
    }

    /**
     * Remove a directory and all contents; borrowed from
     * http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
     * @param string $dirPath
     * @throws InvalidArgumentException
     */
    static private function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Optimizes the lucene index
     * @author Jörn Dreyer <jfd@butonic.de>
     * @author Andrew Brown
     * @return void
     */
    static public function optimizeIndex() {
        $index = self::getIndex();
        OC_Log::write('search', 'Optimized index.', OC_Log::DEBUG);
        $index->optimize();
    }

    /**
     * Transfers stored properties into OC_Search_Result
     * @author Andrew Brown
     * @param OC_Search_Result $result
     * @param Zend_Search_Lucene_Search_QueryHit $hit
     */
    protected static function fillResultFromHit(OC_Search_Result &$result, Zend_Search_Lucene_Search_QueryHit &$hit) {
        $names = $hit->getDocument()->getFieldNames();
        foreach ($result as $property => $value) {
            if (in_array($property, $names)) {
                $result->$property = $hit->getDocument()->getFieldValue($property);
            }
        }
        $result->score = $hit->score;
    }

}