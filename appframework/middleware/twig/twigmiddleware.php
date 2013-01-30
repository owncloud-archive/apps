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

namespace OCA\AppFramework\Middleware\Twig;

use OCA\AppFramework\Controller\Controller;
use OCA\AppFramework\Http\Response;
use OCA\AppFramework\Http\TemplateResponse;
use OCA\AppFramework\Http\TwigResponse;
use OCA\AppFramework\Middleware\Middleware;
use OCA\AppFramework\Core\API;


/**
 * This template is used to add the possibility to add twig templates
 * By default it is only loaded when the templatepath is set
 */
class TwigMiddleware extends Middleware {

	private $twig;
	private $api;
	private $renderAs;

	/**
	 * Sets the twig loader instance
	 * @param API $api an instance of the api
	 * @param Twig_Environment $twig an instance of the twig environment
	 */
	public function __construct(API $api, $twig){
		$this->api = $api;
		$this->twig = $twig;
	}


	/**
	 * Swaps the template response with the twig response and stores if a
	 * template needs to be printed for the user or admin page
	 *
	 * @param Controller $controller the controller that is being called
	 * @param string $methodName the name of the method that will be called on
	 *                           the controller
	 * @param Response $response the generated response from the controller
	 * @return Response a Response object
	 */
	public function afterController($controller, $methodName, Response $response){
		if($response instanceof TemplateResponse){
			$this->renderAs = $response->getRenderAs();
			
			$twigResponse = new TwigResponse(
				$this->api,
				$response->getTemplateName(),
				$this->twig
			);

			foreach($response->getHeaders() as $header){
				$twigResponse->addHeader($header);
			}

			$twigResponse->setParams($response->getParams());
			return $twigResponse;
		} else {
			$this->renderAs = 'blank';
			return $response;
		}
	}


	/**
	 * In case the output is not rendered as blank page, we need to include the
	 * owncloud header and output
	 * @param Controller $controller the controller that is being called
	 * @param string $methodName the name of the method that will be called on
	 *                           the controller
	 * @param string $output the generated output from a response
	 * @return string the output that should be printed
	 */
	public function beforeOutput($controller, $methodName, $output){
		if($this->renderAs === 'blank'){
			return $output;
		} else {
			// FIXME: unfortunately hardcoded appname
			$template = $this->api->getTemplate(
				'twig', 
				$this->renderAs, 
				'appframework'
			);
			
			$template->assign('twig', $output, false);
			$output = $template->fetchPage();

			return $output;
		}
	}

}
