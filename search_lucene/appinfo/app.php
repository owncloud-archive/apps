<?php

/**
* ownCloud - search_lucene application
*
* @author    Jörn Dreyer <jfd@butonic.de>
* @copyright 2012 Jörn Dreyer
* @license   http://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License (AGPL) 3.0
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

// --- Register classes -----------------------------------------------

//add 3rdparty folder to include path
$dir = dirname(dirname(__FILE__)).'/3rdparty';
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

OC::$CLASSPATH['OCA\Search_Lucene\Lucene'] = 'search_lucene/lib/lucene.php';
OC::$CLASSPATH['OCA\Search_Lucene\Indexer'] = 'search_lucene/lib/indexer.php';
OC::$CLASSPATH['OCA\Search_Lucene\Hooks'] = 'search_lucene/lib/hooks.php';

OC::$CLASSPATH['OCA\Search_Lucene\Document\Pdf'] = 'search_lucene/document/Pdf.php';
OC::$CLASSPATH['OCA\Search_Lucene\Document\OpenDocument'] = 'search_lucene/document/OpenDocument.php';
OC::$CLASSPATH['OCA\Search_Lucene\Document\Odt'] = 'search_lucene/document/Odt.php';
OC::$CLASSPATH['OCA\Search_Lucene\Document\Ods'] = 'search_lucene/document/Ods.php';

OC::$CLASSPATH['Zend_Search_Lucene'] = 'search_lucene/3rdparty/Zend/Search/Lucene.php';
OC::$CLASSPATH['Zend_Search_Lucene_Index_Term'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Index/Term.php';
OC::$CLASSPATH['Zend_Search_Lucene_Search_Query_Term'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Search/Query/Term.php';
OC::$CLASSPATH['Zend_Search_Lucene_Field'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Field.php';
OC::$CLASSPATH['Zend_Search_Lucene_Document'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Document.php';
OC::$CLASSPATH['Zend_Search_Lucene_Document_Html'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Document/Html.php';
OC::$CLASSPATH['Zend_Search_Lucene_Document_Docx'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Document/Docx.php';
OC::$CLASSPATH['Zend_Search_Lucene_Document_Xlsx'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Document/Xlsx.php';
OC::$CLASSPATH['Zend_Search_Lucene_Document_Pptx'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Document/Pptx.php';
OC::$CLASSPATH['Zend_Search_Lucene_Analysis_Analyzer'] = 'search_lucene/3rdparty/Zend/Search/Lucene/Analysis/Analyzer.php';

OC::$CLASSPATH['getID3'] = 'getid3/getid3.php';

OC::$CLASSPATH['App_Search_Helper_PdfParser'] = 'search_lucene/3rdparty/pdf2text.php';

OC::$CLASSPATH['Zend_Pdf'] = 'search_lucene/3rdparty/Zend/Pdf.php';

// --- always add js & css -----------------------------------------------

OCP\Util::addScript('search_lucene', 'checker');
OCP\Util::addStyle('search_lucene', 'lucene');

// --- replace default file search provider -----------------------------------------------

//remove other providers
OC_Search::removeProvider('OC_Search_Provider_File');
OC_Search::registerProvider('OCA\Search_Lucene\Lucene');

// --- add hooks -----------------------------------------------

//post_create is ignored, as write will be triggered afterwards anyway

//connect to the filesystem for auto updating
OCP\Util::connectHook(
		OC\Files\Filesystem::CLASSNAME,
		OC\Files\Filesystem::signal_post_write,
		'OCA\Search_Lucene\Hooks',
		OCA\Search_Lucene\Hooks::handle_post_write);

//connect to the filesystem for renaming
OCP\Util::connectHook(
		OC\Files\Filesystem::CLASSNAME,
		OC\Files\Filesystem::signal_post_rename,
		'OCA\Search_Lucene\Hooks',
		OCA\Search_Lucene\Hooks::handle_post_rename);

//listen for file deletions to clean the database
OCP\Util::connectHook(
		OC\Files\Filesystem::CLASSNAME,
		OC\Files\Filesystem::signal_delete,
		'OCA\Search_Lucene\Hooks',
		OCA\Search_Lucene\Hooks::handle_delete);
