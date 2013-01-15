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

namespace OCA\AppFramework;

/**
 * Declare your classes and their include path so that they'll be automatically
 * loaded once you instantiate them
 */
\OC::$CLASSPATH['Pimple'] = 'apps/appframework/3rdparty/Pimple/Pimple.php';

\OC::$CLASSPATH['OCA\AppFramework\API'] = 'apps/appframework/lib/api.php';
\OC::$CLASSPATH['OCA\AppFramework\Request'] = 'apps/appframework/lib/request.php';
\OC::$CLASSPATH['OCA\AppFramework\Controller'] = 'apps/appframework/lib/controller.php';
\OC::$CLASSPATH['OCA\AppFramework\Response'] = 'apps/appframework/lib/responses/response.php';
\OC::$CLASSPATH['OCA\AppFramework\TemplateResponse'] = 'apps/appframework/lib/responses/template.response.php';
\OC::$CLASSPATH['OCA\AppFramework\JSONResponse'] = 'apps/appframework/lib/responses/json.response.php';
\OC::$CLASSPATH['OCA\AppFramework\RedirectResponse'] = 'apps/appframework/lib/responses/redirect.response.php';
\OC::$CLASSPATH['OCA\AppFramework\DownloadResponse'] = 'apps/appframework/lib/responses/download.response.php';
\OC::$CLASSPATH['OCA\AppFramework\TextResponse'] = 'apps/appframework/lib/responses/text.response.php';
\OC::$CLASSPATH['OCA\AppFramework\TextDownloadResponse'] = 'apps/appframework/lib/responses/textdownload.response.php';
\OC::$CLASSPATH['OCA\AppFramework\Mapper'] = 'apps/appframework/lib/mapper.php';
\OC::$CLASSPATH['OCA\AppFramework\DoesNotExistException'] = 'apps/appframework/lib/doesnotexist.exception.php';
\OC::$CLASSPATH['OCA\AppFramework\MethodAnnotationReader'] = 'apps/appframework/lib/methodannotationreader.php';
\OC::$CLASSPATH['OCA\AppFramework\Middleware'] = 'apps/appframework/lib/middleware/middleware.php';
\OC::$CLASSPATH['OCA\AppFramework\SecurityMiddleware'] = 'apps/appframework/lib/middleware/security/security.middleware.php';
\OC::$CLASSPATH['OCA\AppFramework\SecurityException'] = 'apps/appframework/lib/middleware/security/security.exception.php';
\OC::$CLASSPATH['OCA\AppFramework\MiddlewareDispatcher'] = 'apps/appframework/lib/middleware/middlewaredispatcher.php';
\OC::$CLASSPATH['OCA\AppFramework\App'] = 'apps/appframework/lib/app.php';
\OC::$CLASSPATH['OCA\AppFramework\DIContainer'] = 'apps/appframework/appinfo/dicontainer.php';




