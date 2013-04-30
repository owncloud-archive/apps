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


/**
 * Controller class for groups/categories
 */
class GroupController extends BaseController {

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function getGroups() {
		$app = new App($this->api->getUserId());
		$catmgr = new \OC_VCategories('contact', $this->api->getUserId());
		$categories = $catmgr->categories(\OC_VCategories::FORMAT_MAP);
		foreach($categories as &$category) {
			$ids = $catmgr->idsForCategory($category['name']);
			$category['contacts'] = $ids;
		}

		$favorites = $catmgr->getFavorites();

		$groups = array(
			'categories' => $categories,
			'favorites' => $favorites,
			'shared' => \OCP\Share::getItemsSharedWith('addressbook', \OCA\Contacts\Share\Addressbook::FORMAT_ADDRESSBOOKS),
			'lastgroup' => \OCP\Config::getUserValue($this->api->getUserId(), 'contacts', 'lastgroup', 'all'),
			'sortorder' => \OCP\Config::getUserValue($this->api->getUserId(), 'contacts', 'groupsort', ''),
			);

		return new JSONResponse($groups);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function addGroup() {
		$name = $this->request->post['name'];

		$response = new JSONResponse();
		if(is_null($name) || $name === "") {
			$response->bailOut(App::$l10n->t('No group name given.'));
		}

		$catman = new \OC_VCategories('contact', $this->api->getUserId());
		$id = $catman->add($name);

		if($id === false) {
			$response->bailOut(App::$l10n->t('Error adding group.'));
		} else {
			$response->setParams(array('id'=>$id, 'name' => $name));
		}
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function deleteGroup() {
		$name = $this->request->post['name'];

		$response = new JSONResponse();
		if(is_null($name) || $name === "") {
			$response->bailOut(App::$l10n->t('No group name given.'));
		}

		$catman = new \OC_VCategories('contact', $this->api->getUserId());
		$catman->delete($name);
		return $response;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function addToGroup() {
		$response = new JSONResponse();
		$categoryid = $this->request['categoryid'];
		$ids = $this->request->post['contactids'];
		$response->debug('request: '.print_r($this->request->post, true));

		if(is_null($categoryid) || $categoryid === '') {
			$response->bailOut(App::$l10n->t('Group ID missing from request.'));
		}

		if(is_null($ids)) {
			$response->bailOut(App::$l10n->t('Contact ID missing from request.'));
		}

		$catman = new \OC_VCategories('contact', $this->api->getUserId());
		foreach($ids as $contactid) {
			$response->debug('contactid: ' . $contactid . ', categoryid: ' . $categoryid);
			$catman->addToCategory($contactid, $categoryid);
		}

		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function removeFromGroup() {
		$response = new JSONResponse();
		$categoryid = $this->request['categoryid'];
		$ids = $this->request->post['contactids'];
		$response->debug('request: '.print_r($this->request->post, true));

		if(is_null($categoryid) || $categoryid === '') {
			$response->bailOut(App::$l10n->t('Group ID missing from request.'));
		}

		if(is_null($ids)) {
			$response->bailOut(App::$l10n->t('Contact ID missing from request.'));
		}

		$catman = new \OC_VCategories('contact', $this->api->getUserId());
		foreach($ids as $contactid) {
				$response->debug('contactid: ' . $contactid . ', categoryid: ' . $categoryid);
				$catman->removeFromCategory($contactid, $categoryid);
		}

		return $response;
	}

}

