<?php
/**
 * @author Thomas Tanghus
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts\Controller;

use OCA\Contacts\App;
use OCA\Contacts\JSONResponse;
use OCA\AppFramework\Controller\Controller as BaseController;
use OCA\AppFramework\Core\API;
use Sabre\VObject;

/**
 * Controller importing contacts
 */
class ImportController extends BaseController {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function upload() {
		$request = $this->request;
		$params = $this->request->urlParams;
		$response = new JSONResponse();

		$view = \OCP\Files::getStorage('contacts');
		if(!$view->file_exists('imports')) {
			$view->mkdir('imports');
		}

		if (!isset($request->files['file'])) {
			$response->bailOut(App::$l10n->t('No file was uploaded. Unknown error'));
			return $response;
		}

		$file=$request->files['file'];

		if($file['error'] !== UPLOAD_ERR_OK) {
			$errors = array(
				UPLOAD_ERR_OK			=> App::$l10n->t("There is no error, the file uploaded with success"),
				UPLOAD_ERR_INI_SIZE		=> App::$l10n->t("The uploaded file exceeds the upload_max_filesize directive in php.ini")
					.ini_get('upload_max_filesize'),
				UPLOAD_ERR_FORM_SIZE	=> App::$l10n->t("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
				UPLOAD_ERR_PARTIAL		=> App::$l10n->t("The uploaded file was only partially uploaded"),
				UPLOAD_ERR_NO_FILE		=> App::$l10n->t("No file was uploaded"),
				UPLOAD_ERR_NO_TMP_DIR	=> App::$l10n->t('Missing a temporary folder'),
				UPLOAD_ERR_CANT_WRITE	=> App::$l10n->t('Failed to write to disk'),
			);
			$response->bailOut($errors[$error]);
			return $response;
		}

		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		$maxHumanFilesize = \OCP\Util::humanFileSize($maxUploadFilesize);

		$totalSize = $file['size'];
		if ($maxUploadFilesize >= 0 and $totalSize > $maxUploadFilesize) {
			$response->bailOut(App::$l10n->t('Not enough storage available'));
			return $response;
		}

		$tmpname = $file['tmp_name'];
		$filename = strtr($file['name'], array('/' => '', "\\" => ''));
		if(is_uploaded_file($tmpname)) {
			if(\OC\Files\Filesystem::isFileBlacklisted($filename)) {
				$response->bailOut(App::$l10n->t('Attempt to upload blacklisted file:') . $filename);
			return $response;
			}
			$content = file_get_contents($tmpname);
			if($view->file_put_contents('/imports/'.$filename, $content)) {
				$count = substr_count($content, 'BEGIN:');
				$progresskey = 'contacts-import-' . rand();
				$response->setParams(
					array(
						'filename'=>$filename,
						'count' => $count,
						'progresskey' => $progresskey,
						'backend' => $params['backend'],
						'addressbookid' => $params['addressbookid']
					)
				);
				\OC_Cache::set($progresskey, '10', 300);
			} else {
				$response->bailOut(App::$l10n->t('Error uploading contacts to storage.'));
			return $response;
			}
		} else {
			$response->bailOut('Temporary file: \''.$tmpname.'\' has gone AWOL?');
			return $response;
		}
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function start() {
		$request = $this->request;
		$response = new JSONResponse();
		$params = $this->request->urlParams;
		$app = new App($this->api->getUserId());

		$addressBook = $app->getAddressBook($params['backend'], $params['addressbookid']);

		$filename = isset($request->post['filename']) ? $request->post['filename'] : null;
		$progresskey = isset($request->post['progresskey']) ? $request->post['progresskey'] : null;

		if(is_null($filename)) {
			$response->bailOut(App::$l10n->t('File name missing from request.'));
			return $response;
		}

		if(is_null($progresskey)) {
			$response->bailOut(App::$l10n->t('Progress key missing from request.'));
			return $response;
		}

		$filename = strtr($filename, array('/' => '', "\\" => ''));
		if(\OC\Files\Filesystem::isFileBlacklisted($filename)) {
			$response->bailOut(App::$l10n->t('Attempt to access blacklisted file:') . $filename);
			return $response;
		}
		$view = \OCP\Files::getStorage('contacts');
		$file = $view->file_get_contents('/imports/' . $filename);

		$writeProgress = function($pct) use ($progresskey) {
			\OC_Cache::set($progresskey, $pct, 300);
		};

		$cleanup = function() use ($view, $filename, $progresskey) {
			if(!$view->unlink('/imports/' . $filename)) {
				$response->debug('Unable to unlink /imports/' . $filename);
			}
			\OC_Cache::remove($progresskey);
		};

		$writeProgress('20');
		$nl = "\n";
		$file = str_replace(array("\r","\n\n"), array("\n","\n"), $file);
		$lines = explode($nl, $file);

		$inelement = false;
		$parts = array();
		$card = array();
		foreach($lines as $line) {
			if(strtoupper(trim($line)) == 'BEGIN:VCARD') {
				$inelement = true;
			} elseif (strtoupper(trim($line)) == 'END:VCARD') {
				$card[] = $line;
				$parts[] = implode($nl, $card);
				$card = array();
				$inelement = false;
			}
			if ($inelement === true && trim($line) != '') {
				$card[] = $line;
			}
		}
		if(count($parts) === 0) {
			$response->bailOut(App::$l10n->t('No contacts found in: ') . $filename);
			$cleanup();
			return $response;
		}
		//import the contacts
		$writeProgress('40');
		$imported = 0;
		$failed = 0;
		$partial = 0;

		foreach($parts as $part) {
			try {
				$vcard = VObject\Reader::read($part);
			} catch (VObject\ParseException $e) {
				try {
					$vcard = VObject\Reader::read($part, VObject\Reader::OPTION_IGNORE_INVALID_LINES);
					$partial += 1;
					$response->debug('Import: Retrying reading card. Error parsing VCard: ' . $e->getMessage());
				} catch (\Exception $e) {
					$failed += 1;
					$response->debug('Import: skipping card. Error parsing VCard: ' . $e->getMessage());
					continue; // Ditch cards that can't be parsed by Sabre.
				}
			}
			try {
				if($addressBook->addChild($vcard)) {
					$imported += 1;
					$writeProgress($imported);
				} else {
					$failed += 1;
				}
			} catch (\Exception $e) {
				$response->debug('Error importing vcard: ' . $e->getMessage() . $nl . $vcard->serialize());
				$failed += 1;
			}
		}
		//done the import
		sleep(3); // Give client side a chance to read the progress.
		$response->setParams(
			array(
				'imported' => $imported,
				'failed' => $failed,
			)
		);
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function status() {
		$request = $this->request;
		$response = new JSONResponse();

		$progresskey = isset($request->post['progresskey']) ? $request->post['progresskey'] : null;
		if(is_null($progresskey)) {
			$response->bailOut(App::$l10n->t('Progress key missing from request.'));
			return $response;
		}

		$response->setParams(array('progress' => \OC_Cache::get($progresskey)));
		return $response;
	}
}