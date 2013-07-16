<?php

namespace OCA\Search_Lucene\Document;
/**
 * OpenDocument document.
 */
abstract class OpenDocument extends \Zend_Search_Lucene_Document
{
	const OASIS_XPATH_TITLE    = '//dc:title';
	const OASIS_XPATH_SUBJECT  = '//dc:subject';
	const OASIS_XPATH_CREATOR  = '//meta:initial-creator';
	const OASIS_XPATH_KEYWORDS = '//meta:keyword';
	const OASIS_XPATH_CREATED  = '//meta:creation-date';
	const OASIS_XPATH_MODIFIED = '//dc:date';

    /**
     * Extract metadata from document
     *
     * @param ZipArchive $package ZipArchive OpenDocument package
     * @return array Key-value pairs containing document meta data
     */
    protected function extractMetaData(\ZipArchive $package)
    {
        // Data holders
        $coreProperties = array();

        // Read relations and search for core properties
		$sxe = simplexml_load_string($package->getFromName("meta.xml"));

		if (is_object($sxe) && $sxe instanceof \SimpleXMLElement) {

			$coreProperties['title'] = $this->extractTermsFromMetadata($sxe, $this::OASIS_XPATH_TITLE);

			$coreProperties['subject'] = $this->extractTermsFromMetadata($sxe, $this::OASIS_XPATH_SUBJECT);

			$coreProperties['creator'] = $this->extractTermsFromMetadata($sxe, $this::OASIS_XPATH_CREATOR);

			$coreProperties['keywords'] = $this->extractTermsFromMetadata($sxe, $this::OASIS_XPATH_KEYWORDS);

			//replace T in date string with ' '
			$coreProperties['created'] = str_replace('T', ' ', $this->extractTermsFromMetadata($sxe, $this::OASIS_XPATH_CREATED));

			$coreProperties['modified'] = str_replace('T', ' ', $this->extractTermsFromMetadata($sxe, $this::OASIS_XPATH_MODIFIED));
		}

        return $coreProperties;
    }

	private function extractTermsFromMetadata(\SimpleXMLElement $sxe, $path) {

		$terms = array();

		foreach ($sxe->xpath($path) as $value) {
			$terms[] = (string)$value;
		}

		return (implode(' ', $terms));

	}

    /**
     * Determine absolute zip path
     *
     * @param string $path
     * @return string
     */
    protected function absoluteZipPath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }
}
