<?php

namespace OCA\Search_Lucene\Document;

use \OCP\Util;
/**
 * PDF document
 */
class Pdf extends \Zend_Search_Lucene_Document
{

    /**
     * Object constructor
     *
     * @param string  $data
     * @param boolean $storeContent
     */
    private function __construct($data, $storeContent) {

		try {
			$zendpdf = \Zend_Pdf::parse($data);

			// Store meta data properties
			if (isset($zendpdf->properties['Title'])) {
				$this->addField(\Zend_Search_Lucene_Field::UnStored('title', $zendpdf->properties['Title']));
			}
			if (isset($zendpdf->properties['Author'])) {
				$this->addField(\Zend_Search_Lucene_Field::UnStored('author', $zendpdf->properties['Author']));
			}
			if (isset($zendpdf->properties['Subject'])) {
				$this->addField(\Zend_Search_Lucene_Field::UnStored('subject', $zendpdf->properties['Subject']));
			}
			if (isset($zendpdf->properties['Keywords'])) {
				$this->addField(\Zend_Search_Lucene_Field::UnStored('keywords', $zendpdf->properties['Keywords']));
			}
			//TODO handle PDF 1.6 metadata Zend_Pdf::getMetadata()

			//do the content extraction
			$pdfParse = new \App_Search_Helper_PdfParser();
			$body = $pdfParse->pdf2txt($zendpdf->render());


			if ($body != '') {
				// Store contents
				if ($storeContent) {
					$this->addField(\Zend_Search_Lucene_Field::Text('body', $body, 'UTF-8'));
				} else {
					$this->addField(\Zend_Search_Lucene_Field::UnStored('body', $body, 'UTF-8'));
				}
			}

		} catch (\Exception $e) {
			Util::writeLog('search_lucene',
				$e->getMessage() . ' Trace:\n' . $e->getTraceAsString(),
				Util::ERROR);
		}

    }

    /**
     * Load PDF document from a string
     *
     * @param string  $data
     * @param boolean $storeContent
     * @return Pdf
     */
    public static function loadPdf($data, $storeContent = false)
    {
        return new Pdf($data, false, $storeContent);
    }


}

