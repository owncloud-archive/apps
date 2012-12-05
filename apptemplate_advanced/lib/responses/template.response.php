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


/**
 * Response for a normal template
 */
class TemplateResponse extends Response {

	private $templateName;
	private $params;
	private $appName;
	private $renderAs;

	/**
	 * @param string $appName: the name of your app
	 * @param string $templateName: the name of the template
	 */
	public function __construct($appName, $templateName) {
		parent::__construct();
		$this->templateName = $templateName;
		$this->appName = $appName;
		$this->params = array();
		$this->renderAs = 'user';
	}


	/**
	 * @brief sets template parameters
	 * @param array $params: an array with key => value structure which sets template
	 *                       variables
	 */
	public function setParams($params){
		$this->params = $params;
	}


	/**
	 * @brief sets the template page
	 * @param string $renderAs: admin, user or blank: admin renders the page on
	 *                          the admin settings page, user renders a normal
	 *                          owncloud page, blank renders the template alone
	 */
	public function renderAs($renderAs='user'){
		$this->renderAs = $renderAs;
	}


	/**
	 * Returns the rendered html
	 * @return the rendered html
	 */
	public function render(){
		parent::render();

		if($this->renderAs === 'blank'){
			$template = new \OCP\Template($this->appName, $this->templateName);
		} else {
			$template = new \OCP\Template($this->appName, $this->templateName,
											$this->renderAs);
		}

		foreach($this->params as $key => $value){
			$template->assign($key, $value, false);
		}

		return $template->fetchPage();
	}

}

