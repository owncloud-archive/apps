<?php

/**
 * ownCloud - App Framework
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


namespace OCA\AppFramework\Http;

use OCA\AppFramework\Core\API as API;


/**
 * Response for a normal template
 */
class TemplateResponse extends Response {

	private $templateName;
	private $params;
	private $api;
	private $renderAs;
	private $appName;

	/**
	 * @param string $api: an API instance
	 * @param string $templateName: the name of the template
	 * @param string $appName: optional if you want to include a template from
	 *                         a different app
	 */
	public function __construct(API $api, $templateName, $appName=null) {
		parent::__construct();
		$this->templateName = $templateName;
		$this->appName = $appName;
		$this->api = $api;
		$this->params = array();
		$this->renderAs = 'user';
	}


	/**
	 * @brief sets template parameters
	 * @param array $params: an array with key => value structure which sets template
	 *                       variables
	 */
	public function setParams(array $params){
		$this->params = $params;
	}


	/**
	 * @return the params
	 */
	public function getParams(){
		return $this->params;
	}


	/**
	 * @return the name of the used template
	 */
	public function getTemplateName(){
		return $this->templateName;
	}


	/**
	 * @brief sets the template page
	 * @param string $renderAs: admin, user or blank: admin renders the page on
	 *                          the admin settings page, user renders a normal
	 *                          owncloud page, blank renders the template alone
	 */
	public function renderAs($renderAs){
		$this->renderAs = $renderAs;
	}


	/**
	 * Returns the rendered html
	 * @return the rendered html
	 */
	public function render(){

		if($this->appName !== null){
			$appName = $this->appName;
		} else {
			$appName = $this->api->getAppName();
		}

		$template = $this->api->getTemplate($this->templateName, $this->renderAs, $appName);

		foreach($this->params as $key => $value){
			$template->assign($key, $value, false);
		}

		return $template->fetchPage();
	}

}

