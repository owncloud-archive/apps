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


namespace OCA\AppFramework\DependencyInjection;

use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Core\API;
use OCA\AppFramework\Middleware\MiddlewareDispatcher;
use OCA\AppFramework\Middleware\Security\SecurityMiddleware;
use OCA\AppFramework\Middleware\Twig\TwigMiddleware;


require_once __DIR__ . '/../3rdparty/Pimple/Pimple.php';
require_once __DIR__ . '/../3rdparty/Twig/lib/Twig/Autoloader.php';
\Twig_Autoloader::register();


/**
 * This class extends Pimple (http://pimple.sensiolabs.org/) for reusability
 * To use this class, extend your own container from this. Should you require it
 * you can overwrite the dependencies with your own classes by simply redefining
 * a dependency
 */
class DIContainer extends \Pimple {


	/**
	 * Put your class dependencies in here
	 * @param string $appName the name of the app
	 */
	public function __construct($appName){
		
		$this['AppName'] = $appName;

		$this['API'] = $this->share(function($c){
			return new API($c['AppName']);
		});

		$this['Request'] = $this->share(function($c){
			return new Request($_GET, $_POST, $_FILES);
		});


		/**
		 * Twig
		 */
		// use this to specify the template directory
		$this['TwigTemplateDirectory'] = null;

		// if you want to cache the template directory, add this path
		$this['TwigTemplateCacheDirectory'] = null;
		
		// enables the l10n function as t() function in twig
		$this['TwigL10N'] = $this->share(function($c){
			$trans = $c['API']->getTrans();;
			return new \Twig_SimpleFunction('trans', function () use ($trans) {
				$args = func_get_args();
				$string = array_shift($args);
				return $trans->t($string, $args);
			});
		});

		// enables the linkToRoute function as url() function in twig
		$this['TwigLinkToRoute'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('url', function () use ($api) {
				return call_user_func_array(array($api, 'linkToRoute'), func_get_args());
			});
		});

		// enables the addScript function as script() function in twig
		$this['TwigAddScript'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('script', function () use ($api) {
				call_user_func_array(array($api, 'addScript'), func_get_args());
			});
		});

		// enables the addScript function as script() function in twig
		$this['TwigAddStyle'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('style', function () use ($api) {
				call_user_func_array(array($api, 'addStyle'), func_get_args());
			});
		});

		// enables the linkToRoute function as url() function in twig
		$this['TwigLinkToAbsoluteRoute'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('abs_url', function () use ($api) {
				$url = call_user_func_array(array($api, 'linkToRoute'), func_get_args());
				return $api->getAbsoluteURL($url);
			});
		});

		// enables the linkTo function as link_to() function in twig
		$this['TwigLinkTo'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('link_to', function () use ($api) {
				return call_user_func_array(array($api, 'linkTo'), func_get_args());
			});
		});

		// enables the linkTo function as link_to() function in twig
		$this['TwigImagePath'] = $this->share(function($c){
			$api = $c['API'];
			return new \Twig_SimpleFunction('image_path', function () use ($api) {
				return call_user_func_array(array($api, 'imagePath'), func_get_args());
			});
		});


		$this['TwigLoader'] = $this->share(function($c){
			return new \Twig_Loader_Filesystem($c['TwigTemplateDirectory']);
		});

		$this['Twig'] = $this->share(function($c){
			$loader = $c['TwigLoader'];
			if($c['TwigTemplateCacheDirectory'] !== null){
				$twig = new \Twig_Environment($loader, array(
					'cache' => $c['TwigTemplateCacheDirectory'],
					'autoescape' => true
				));
			} else {
				$twig = new \Twig_Environment($loader, array(
					'autoescape' => true
				));
			}
			$twig->addFunction($c['TwigAddScript']);
			$twig->addFunction($c['TwigAddStyle']);
			$twig->addFunction($c['TwigL10N']);
			$twig->addFunction($c['TwigImagePath']);
			$twig->addFunction($c['TwigLinkTo']);
			$twig->addFunction($c['TwigLinkToRoute']);
			$twig->addFunction($c['TwigLinkToAbsoluteRoute']);
			return $twig;
		});


		/**
		 * Middleware
		 */
		$this['SecurityMiddleware'] = $this->share(function($c){
			return new SecurityMiddleware($c['API']);
		});

		$this['TwigMiddleware'] = $this->share(function($c){
			return new TwigMiddleware($c['API'], $c['Twig']);
		});

		$this['MiddlewareDispatcher'] = $this->share(function($c){
			$dispatcher = new MiddlewareDispatcher();
			$dispatcher->registerMiddleware($c['SecurityMiddleware']);

			// only add twigmiddleware if the user set the template directory
			if($c['TwigTemplateDirectory'] !== null){
				$dispatcher->registerMiddleware($c['TwigMiddleware']);
			}

			return $dispatcher;
		});

	}

	
}

