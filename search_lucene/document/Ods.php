<?php

namespace OCA\Search_Lucene\Document;
/**
 * Ods document.
 * @see http://en.wikipedia.org/wiki/OpenDocument_technical_specification
 */
class Ods extends OpenDocument {

	const SCHEMA_ODTABLE = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';

    /**
     * Object constructor
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @throws \Zend_Search_Lucene_Exception
     */
    private function __construct($fileName, $storeContent) {
        if (!class_exists('ZipArchive', false)) {
            throw new \Zend_Search_Lucene_Exception('Open Document Spreadsheet processing functionality requires Zip extension to be loaded');
        }

        // Document data holders
		$documentTables = array();
		$documentCells = array();

        // Open OpenXML package
        $package = new \ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $content = $package->getFromName('content.xml');
        if ($content === false) {
            throw new \Zend_Search_Lucene_Exception('Invalid archive or corrupted .ods file.');
        }
		$sxe = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOBLANKS | LIBXML_COMPACT);

		foreach ($sxe->xpath('//table:table[@table:name]') as $table) {
			$documentTables[] = (string)$table->attributes($this::SCHEMA_ODTABLE)->name;
		}
		foreach ($sxe->xpath('//text:p') as $cell) {
			$documentCells[] = (string)$cell;
		}

        // Read core properties
        $coreProperties = $this->extractMetaData($package);

        // Close file
        $package->close();

        // Store contents
        if ($storeContent) {
			$this->addField(\Zend_Search_Lucene_Field::Text('sheets', implode(' ', $documentTables), 'UTF-8'));
			$this->addField(\Zend_Search_Lucene_Field::Text('body', implode(' ', $documentCells), 'UTF-8'));
        } else {
			$this->addField(\Zend_Search_Lucene_Field::UnStored('sheets', implode(' ', $documentTables), 'UTF-8'));
			$this->addField(\Zend_Search_Lucene_Field::UnStored('body', implode(' ', $documentCells), 'UTF-8'));
        }

        // Store meta data properties
        foreach ($coreProperties as $key => $value) {
            $this->addField(\Zend_Search_Lucene_Field::Text($key, $value, 'UTF-8'));
        }

        // Store title (if not present in meta data)
        if (! isset($coreProperties['title'])) {
            $this->addField(\Zend_Search_Lucene_Field::Text('title', $fileName, 'UTF-8'));
        }
    }

    /**
     * Load Ods document from a file
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @return Ods
     * @throws \Zend_Search_Lucene_Document_Exception
     */
    public static function loadOdsFile($fileName, $storeContent = false) {
        if (!is_readable($fileName)) {
            throw new \Zend_Search_Lucene_Document_Exception('Provided file \'' . $fileName . '\' is not readable.');
        }

        return new Ods($fileName, $storeContent);
    }
}
