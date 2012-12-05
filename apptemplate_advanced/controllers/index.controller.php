<?php

/**
* ownCloud - App Template Example
*
* @author Bernhard Posselt
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com 
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

namespace OCA\AppTemplateAdvanced;


class IndexController extends Controller {
	

	/**
	 * @param Request $request: an instance of the request
	 * @param API $api: an api wrapper instance
         * @param ItemMapper $itemMapper: an itemwrapper instance
	 */
        public function __construct($api, $request, $itemMapper){
		parent::__construct($api, $request);

		// this will set the current navigation entry of the app, use this only
		// for normal HTML requests and not for AJAX requests
		$this->api->activateNavigationEntry();

                $this->itemMapper = $itemMapper;
	}


	/**
	 * @brief renders the index page
	 * @param array $urlParams: an array with the values, which were matched in 
	 *                          the routes file
		 * @return an instance of a Response implementation
	 */
        public function index($urlParams=array(), $container=null){

		// thirdparty stuff
		$this->api->add3rdPartyScript('angular/angular.min');

		// your own stuff
		$this->api->addStyle('style');
		$this->api->addStyle('animation');

		$this->api->addScript('app');

                // example database access
                // check if an entry with the current user is in the database, if not
                // create a new entry
                try {
                        $item = $this->itemMapper->findByUserId($this->api->getUserId());
                } catch (DoesNotExistException $e) {
                        $item = new Item();
                        $item->setUser($this->api->getUserId());
                        $item->setPath('/home/path');
                        $item->setName('john');
                        $item = $this->itemMapper->save($item);
                }

		$templateName = 'main';
		$params = array(
                        'somesetting' => $this->api->getSystemValue('somesetting'),
                        'item' => $item
		);
		return $this->render($templateName, $params);
	}

}