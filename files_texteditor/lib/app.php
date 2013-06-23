<?php

/**
 * ownCloud - Core
 *
 * @author Tom Needham
 * @copyright 2013 Tom Needham tom@owncloud.com
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


namespace OCA\Files_Texteditor;

class App {
	private $l10n;
	private $view;

	public function __construct($view, $l10n) {
		$this->l10n = $l10n;
		$this->view = $view;
	}

	/**
	 * load a file
	 *
	 * @param string $dir
	 * @param string $filename
	 * @return array
	 */
	public function loadFile($dir, $filename) {
		// Check if the filename has been supplied
		if(empty($filename)) {
			return array(
				'success' => false,
				'message' => 'Invalid file path supplied',
				);
		}
		$path = $dir.'/'.$filename;
		$writeable = $this->view->isUpdatable($path);
		$mime = $this->view->getMimeType($path);
		$contents = $this->view->file_get_contents($path);
		if($contents === false) {
			return array(
				'success' => false,
				'message' => 'There was an error while opening the file',
				);
		}
		$encoding = mb_detect_encoding($contents."a", "UTF-8, WINDOWS-1252, ISO-8859-15, ISO-8859-1, ASCII", true);
		if ($encoding == "") {
			// set default encoding if it couldn't be detected
			$encoding = 'ISO-8859-15';
		}
		$contents = iconv($encoding, "UTF-8", $contents);
		return array(
			'success' => true,
			'data' => array(
				'filecontents' => $contents,
				'writeable' => $writeable,
				'mime' => $mime,
				)
			);
	}

	/**
	 * save a file
	 *
	 * @param string $path
	 * @param string $contents
	 * @param string $opened
	 * @return array
	 */
	public function saveFile($path, $contents, $opened) {
		// Check we have everything
		if($path === '') {

		} elseif($opened === '') {

		} elseif($this->view->hasUpdated($path, $opened)) {
			return array(
				'success' => flase,
				'message' => 'File has been modified since opening',
				);
		} else {
			// Try to save
			if($this->view->isUpdatable($path)) {
				// Go
				$contents = iconv(mb_detect_encoding($contents), "UTF-8", $contents);
				$this->view->file_put_contents($path, $contents);
				// Clear statcache
				clearstatcache();
				return array(
					'success' => true,
					);
			} else {
				return array(
					'success' => false,
					'message' => 'File is not writeable',
					);
			}
		}
	}

}
