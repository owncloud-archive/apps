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

use OCA\AppFramework\Core\API;


/**
 * Response for twig templates. Do not use this directly to render your
 * templates, unless you want a blank page because the owncloud header and
 * footer won't be included
 */
class TwigResponse extends TemplateResponse {

	private $twig;

	/**
	 * Instantiates the Twig Template
	 * @param API $api an api instance
	 * @param string $templateName the name of the twig template
	 * @param Twig_Environment an instance of the twig environment for rendering
	 */
	public function __construct(API $api, $templateName, $twig){
		parent::__construct($api, $templateName . '.php');
		$this->twig = $twig;
	}


	/**
	 * Returns the rendered result
	 * @return string rendered output
	 */
	public function render(){
		return $this->twig->render($this->templateName, $this->params);
	}

}