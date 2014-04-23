<?php

namespace OCA\Search_Lucene\Document;
/**
 * Odt document.
 * @see http://en.wikipedia.org/wiki/OpenDocument_technical_specification
 */
class Odt extends OpenDocument {
    /**
     * Object constructor
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @throws \Zend_Search_Lucene_Exception
     */
    private function __construct($fileName, $storeContent) {
        if (!class_exists('ZipArchive', false)) {
            throw new \Zend_Search_Lucene_Exception('Open Document Text processing functionality requires Zip extension to be loaded');
        }

        // Document data holders
		$documentHeadlines = array();
		$documentParagraphs = array();

        // Open OpenXML package
        $package = new \ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $content = $package->getFromName('content.xml');
        if ($content === false) {
            throw new \Zend_Search_Lucene_Exception('Invalid archive or corrupted .odt file.');
        }
			libxml_disable_entity_loader(true);
		$sxe = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOBLANKS | LIBXML_COMPACT);

		foreach ($sxe->xpath('//text:h') as $headline) {
			$documentHeadlines[] = (string)$headline;
		}

		foreach ($sxe->xpath('//text:p') as $paragraph) {
			$documentParagraphs[] = (string)$paragraph;
		}

        // Read core properties
        $coreProperties = $this->extractMetaData($package);

        // Close file
        $package->close();

        // Store contents
        if ($storeContent) {
			$this->addField(\Zend_Search_Lucene_Field::Text('headlines', implode(' ', $documentHeadlines), 'UTF-8'));
			$this->addField(\Zend_Search_Lucene_Field::Text('body', implode(' ', $documentParagraphs), 'UTF-8'));
        } else {
			$this->addField(\Zend_Search_Lucene_Field::UnStored('headlines', implode(' ', $documentHeadlines), 'UTF-8'));
			$this->addField(\Zend_Search_Lucene_Field::UnStored('body', implode(' ', $documentParagraphs), 'UTF-8'));
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
     * Load Odt document from a file
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @return Odt
     * @throws \Zend_Search_Lucene_Document_Exception
     */
    public static function loadOdtFile($fileName, $storeContent = false) {
        if (!is_readable($fileName)) {
            throw new \Zend_Search_Lucene_Document_Exception('Provided file \'' . $fileName . '\' is not readable.');
        }

        return new Odt($fileName, $storeContent);
    }
}
