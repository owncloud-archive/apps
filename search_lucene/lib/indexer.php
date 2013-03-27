<?php

namespace OCA\Search_Lucene;

use \OC\Files\Filesystem;
use \OCP\Util;

/**
 * @author Jörn Dreyer <jfd@butonic.de>
 */
class Indexer {

	/**
	 * classname which used for hooks handling
	 * used as signalclass in OC_Hooks::emit()
	 */
	const CLASSNAME = 'Indexer';

	/**
	 * index a file
	 *
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param string $path the path of the file
	 *
	 * @return bool
	 */
	static public function indexFile($path = '', $user = null) {

		if (!Filesystem::isValidPath($path)) {
			return;
		}
		if ($path === '') {
			//ignore the empty path element
			return false;
		}

		if (is_null($user)) {
			$view = Filesystem::getView();
			$user = \OCP\User::getUser();
		} else {
			$view = new \OC\Files\View('/' . $user . '/files');
		}

		if ( ! $view ) {
			Util::writeLog('search_lucene',
				'could not resolve filesystem view',
				Util::WARN);
			return false;
		}

		$root = $view->getRoot();
		$pk = md5($root . $path);

		// the cache already knows mime and other basic stuff
		$data = $view->getFileInfo($path);
		if (isset($data['mimetype'])) {
			$mimetype = $data['mimetype'];
			if ('text/html' === $mimetype) {
				$doc = \Zend_Search_Lucene_Document_Html::loadHTML($view->file_get_contents($path));
			} else if ('application/msword' === $mimetype) {
				// FIXME uses ZipArchive ... make compatible with OC\Files\Filesystem
				//$doc = Zend_Search_Lucene_Document_Docx::loadDocxFile(OC\Files\Filesystem::file_get_contents($path));

				//no special treatment yet
				$doc = new \Zend_Search_Lucene_Document();
			} else {
				$doc = new \Zend_Search_Lucene_Document();
			}

			// store fscacheid as unique id to lookup by when deleting
			$doc->addField(\Zend_Search_Lucene_Field::Keyword('pk', $pk));

			// Store document URL to identify it in the search results
			$doc->addField(\Zend_Search_Lucene_Field::Text('path', $path));

			$doc->addField(\Zend_Search_Lucene_Field::unIndexed('size', $data['size']));

			$doc->addField(\Zend_Search_Lucene_Field::unIndexed('mimetype', $mimetype));

			self::extractMetadata($doc, $path, $view, $mimetype);

			Lucene::updateFile($doc, $path, $user);

			return true;

		} else {
            Util::writeLog('search_lucene',
				'need mimetype for content extraction',
                Util::ERROR);
			return false;
		}
	}


	/**
	 * extract the metadata from a file
	 *
	 * uses getid3 to extract metadata.
	 * if possible also adds content (currently only for plain text files)
	 * hint: use OC\Files\Filesystem::getFileInfo($path) to get metadata for the last param
	 *
	 * @author Jörn Dreyer <jfd@butonic.de>
	 *
	 * @param Zend_Search_Lucene_Document $doc      to add the metadata to
	 * @param string                      $path     path of the file to extract metadata from
	 * @param string                      $mimetype depending on the mimetype different extractions are performed
	 *
	 * @return void
	 */
	private static function extractMetadata(\Zend_Search_Lucene_Document $doc, $path, \OC\Files\View $view, $mimetype) {

		$file = $view->getLocalFile($path);
		$getID3 = new \getID3();
		$getID3->encoding = 'UTF-8';
		$data = $getID3->analyze($file);

		// TODO index meta information from media files?

		//show me what you got
		/*foreach ($data as $key => $value) {
			Util::writeLog('search_lucene',
						'getid3 extracted '.$key.': '.$value,
						Util::DEBUG);
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					Util::writeLog('search_lucene',
							'  ' . $value .'-' .$k.': '.$v,
							Util::DEBUG);
				}
			}
		}*/

		// filename _should_ always work, so log if it does not
		if (isset($data['filename'])) {
			$doc->addField(\Zend_Search_Lucene_Field::Text('filename', $data['filename']));
		} else {
            Util::writeLog('search_lucene',
				'failed to extract meta information for ' . $view->getAbsolutePath($path) . ': ' . $data['error']['0'],
                Util::WARN);
		}

		//content

        Util::writeLog('search_lucene',
			'indexer extracting content for ' . $view->getAbsolutePath($path) . ' (' . $mimetype . ')',
            Util::DEBUG);

		$body = '';

		if ('text/plain' === $mimetype) {
			$body = $view->file_get_contents($path);
		} else if ('text/html' === $mimetype) {
			// getid3 does not handle html, but that's not a real error
			unset($data['error']);

		} else if ('httpd/unix-directory' === $mimetype) {
			// getid3 does not handle directories, ignore the error
			unset($data['error']);

		} else if ('application/pdf' === $mimetype) {
			try {
				$zendpdf = \Zend_Pdf::parse($view->file_get_contents($path));

				//we currently only display the filename, so we only index metadata here
				if (isset($zendpdf->properties['Title'])) {
					$doc->addField(\Zend_Search_Lucene_Field::UnStored('title', $zendpdf->properties['Title']));
				}
				if (isset($zendpdf->properties['Author'])) {
					$doc->addField(\Zend_Search_Lucene_Field::UnStored('author', $zendpdf->properties['Author']));
				}
				if (isset($zendpdf->properties['Subject'])) {
					$doc->addField(\Zend_Search_Lucene_Field::UnStored('subject', $zendpdf->properties['Subject']));
				}
				if (isset($zendpdf->properties['Keywords'])) {
					$doc->addField(\Zend_Search_Lucene_Field::UnStored('keywords', $zendpdf->properties['Keywords']));
				}
				//TODO handle PDF 1.6 metadata Zend_Pdf::getMetadata()

				//do the content extraction
				$pdfParse = new \App_Search_Helper_PdfParser();
				$body = $pdfParse->pdf2txt($zendpdf->render());

			} catch (Exception $e) {
				Util::writeLog('search_lucene',
					$e->getMessage() . ' Trace:\n' . $e->getTraceAsString(),
					Util::ERROR);
			}

		}

		if ($body != '') {
			$doc->addField(\Zend_Search_Lucene_Field::UnStored('body', $body));
		}

		if (isset($data['error'])) {
            Util::writeLog('search_lucene',
				'failed to extract meta information for ' . $view->getAbsolutePath($path) . ': ' . $data['error']['0'],
                Util::WARN);

			return;
		}
	}

	/**
	 * get the list of all unindexed files for the user
	 *
	 * @return array
	 */
	static public function getUnindexed() {
		$files = array();
		$absoluteRoot = Filesystem::getView()->getAbsolutePath('/');
		$mounts = \OC\Files\Mount::findIn($absoluteRoot);
		$mount = \OC\Files\Mount::find($absoluteRoot);
		if (!in_array($mount, $mounts)) {
			$mounts[] = $mount;
		}

		$query = \OC_DB::prepare('SELECT `*PREFIX*filecache`.`fileid`'
			. ' FROM `*PREFIX*filecache`'
			. ' LEFT JOIN `*PREFIX*lucene_status`'
			. ' ON `*PREFIX*filecache`.`fileid` = `*PREFIX*lucene_status`.`fileid`'
			. ' WHERE `storage` = ?'
			. ' AND `status` is null OR `status` = "N"');

		foreach ($mounts as $mount) {
			$storage = $mount->getStorage();
			//only index local files for now
			if ($storage instanceof \OC\Files\Storage\Local) {
				$cache = $storage->getCache();
				$numericId = $cache->getNumericStorageId();

				$result = $query->execute(array($numericId));
				if (!$result) {
					return false;
				}
				while ($row = $result->fetchRow()) {
					$files[] = $row['fileid'];
				}
			}
		}
		return $files;
	}

}
