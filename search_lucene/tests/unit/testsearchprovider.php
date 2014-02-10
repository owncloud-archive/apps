<?php

/**
 * ownCloud search lucene
 *
 * @author Jörn Dreyer
 * @copyright 2014 Jörn Friedrich Dreyer jfd@butonic.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Search_Lucene\Tests\Unit;

class DummyIndex implements \Zend_Search_Lucene_Interface {
	public function addDocument(\Zend_Search_Lucene_Document $document) {
		
	}

	public function addReference() {
		
	}

	public function closeTermsStream() {
		
	}

	public function commit() {
		
	}

	public function count() {
		
	}

	public function currentTerm() {
		
	}

	public function delete($id) {
		
	}

	public function docFreq(\Zend_Search_Lucene_Index_Term $term) {
		
	}

	public function find($query) {
		
	}

	public function getDirectory() {
		
	}

	public function getDocument($id) {
		
	}

	public function getFieldNames($indexed = false) {
		
	}

	public function getFormatVersion() {
		
	}

	public function getMaxBufferedDocs() {
		
	}

	public function getMaxMergeDocs() {
		
	}

	public function getMergeFactor() {
		
	}

	public function getSimilarity() {
		
	}

	public function hasDeletions() {
		
	}

	public function hasTerm(\Zend_Search_Lucene_Index_Term $term) {
		
	}

	public function isDeleted($id) {
		
	}

	public function maxDoc() {
		
	}

	public function nextTerm() {
		
	}

	public function norm($id, $fieldName) {
		
	}

	public function numDocs() {
		
	}

	public function optimize() {
		
	}

	public function removeReference() {
		
	}

	public function resetTermsStream() {
		
	}

	public function setFormatVersion($formatVersion) {
		
	}

	public function setMaxBufferedDocs($maxBufferedDocs) {
		
	}

	public function setMaxMergeDocs($maxMergeDocs) {
		
	}

	public function setMergeFactor($mergeFactor) {
		
	}

	public function skipTo(\Zend_Search_Lucene_Index_Term $prefix) {
		
	}

	public function termDocs(\Zend_Search_Lucene_Index_Term $term, $docsFilter = null) {
		
	}

	public function termDocsFilter(\Zend_Search_Lucene_Index_Term $term, $docsFilter = null) {
		
	}

	public function termFreqs(\Zend_Search_Lucene_Index_Term $term, $docsFilter = null) {
		
	}

	public function termPositions(\Zend_Search_Lucene_Index_Term $term, $docsFilter = null) {
		
	}

	public function terms() {
		
	}

	public function undeleteAll() {
		
	}

	public static function getActualGeneration(\Zend_Search_Lucene_Storage_Directory $directory) {
		
	}

	public static function getDefaultSearchField() {
		
	}

	public static function getResultSetLimit() {
		
	}

	public static function getSegmentFileName($generation) {
		
	}

	public static function setDefaultSearchField($fieldName) {
		
	}

	public static function setResultSetLimit($limit) {
		
	}

}

class TestSearchProvider extends TestCase {

	/**
	 * @dataProvider searchResultDataProvider
	 */
	function testAsOCSearchResult(\Zend_Search_Lucene_Search_QueryHit $hit, $name, $text, $link, $type, $container) {

		$searchResult = \OCA\Search_Lucene\SearchProvider::asOCSearchResult($hit);

		$this->assertInstanceOf('OC_Search_Result', $searchResult);
		$this->assertEquals($searchResult->name, $name);
		$this->assertEquals($searchResult->text, $text);
		//$this->assertEquals($searchResult->link, $link);
		$this->assertEquals($searchResult->type, $type);
		$this->assertEquals($searchResult->container, $container);
	}
	
	public function searchResultDataProvider() {
		
		$index = new DummyIndex();
		$hit1 = new \Zend_Search_Lucene_Search_QueryHit($index);
		$hit1->mimetype = 'text/plain';
		$hit1->path = 'documents/document.txt';
		$hit1->size = 123;
		$hit1->score = 0.4;
		
		$hit2 = new \Zend_Search_Lucene_Search_QueryHit($index);
		$hit2->mimetype = 'application/pdf';
		$hit2->path = 'documents/document.pdf';
		$hit2->size = 1234;
		$hit2->score = 0.31;
		
		$hit3 = new \Zend_Search_Lucene_Search_QueryHit($index);
		$hit3->mimetype = 'audio/mp3';
		$hit3->path = 'documents/document.mp3';
		$hit3->size = 12341234;
		$hit3->score = 0.299;
		
		$hit4 = new \Zend_Search_Lucene_Search_QueryHit($index);
		$hit4->mimetype = 'image/jpg';
		$hit4->path = 'documents/document.jpg';
		$hit4->size = 1234123;
		$hit4->score = 0.001;
		
		return array(
			// name, text, link, type, container
			//FIXME search result should not contain translated strings
			array($hit1,'document.txt', 'documents, 123 B, Score: 0.40', 'FIXME', 'Text', 'documents'),
			array($hit2,'document.pdf', 'documents, 1 kB, Score: 0.31', 'FIXME', 'Files', 'documents'),
			array($hit3,'document.mp3', 'documents, 11.8 MB, Score: 0.30', 'FIXME', 'Music', 'documents'),
			array($hit4,'document.jpg', 'documents, 1.2 MB, Score: 0.00', 'FIXME', 'Images', 'documents'),
		);
	}
}
