<?php
/**
* ownCloud - News app
*
* @author Alessandro Copyright
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com                    
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

\OC::$CLASSPATH['Pimple'] = 'news/3rdparty/Pimple/Pimple.php';

\OC::$CLASSPATH['OC_Search_Provider_News'] = 'news/lib/search.php';
\OC::$CLASSPATH['OCA\News\Backgroundjob'] = 'news/lib/backgroundjob.php';
\OC::$CLASSPATH['OCA\News\Share_Backend_News_Item'] = 'news/lib/share/item.php';
\OC::$CLASSPATH['OCA\News\Utils'] = 'news/lib/utils.php';
\OC::$CLASSPATH['OCA\News\Security'] = 'news/lib/security.php';
\OC::$CLASSPATH['OCA\News\API'] = 'news/lib/api.php';
\OC::$CLASSPATH['OCA\News\Request'] = 'news/lib/request.php';
\OC::$CLASSPATH['OCA\News\TemplateResponse'] = 'news/lib/response.php';
\OC::$CLASSPATH['OCA\News\JSONResponse'] = 'news/lib/response.php';
\OC::$CLASSPATH['OCA\News\TextDownloadResponse'] = 'news/lib/response.php';
\OC::$CLASSPATH['OCA\News\Controller'] = 'news/lib/controller.php';

\OC::$CLASSPATH['OCA\News\OPMLParser'] = 'news/opmlparser.php';
\OC::$CLASSPATH['OCA\News\OPMLExporter'] = 'news/opmlexporter.php';
\OC::$CLASSPATH['OCA\News\OPMLImporter'] = 'news/opmlimporter.php';

\OC::$CLASSPATH['OCA\News\Enclosure'] = 'news/db/enclosure.php';
\OC::$CLASSPATH['OCA\News\FeedMapper'] = 'news/db/feedmapper.php';
\OC::$CLASSPATH['OCA\News\ItemMapper'] = 'news/db/itemmapper.php';
\OC::$CLASSPATH['OCA\News\FolderMapper'] = 'news/db/foldermapper.php';
\OC::$CLASSPATH['OCA\News\Folder'] = 'news/db/folder.php';
\OC::$CLASSPATH['OCA\News\Feed'] = 'news/db/feed.php';
\OC::$CLASSPATH['OCA\News\Item'] = 'news/db/item.php';
\OC::$CLASSPATH['OCA\News\Collection'] = 'news/db/collection.php';
\OC::$CLASSPATH['OCA\News\FeedType'] = 'news/db/feedtype.php';
\OC::$CLASSPATH['OCA\News\StatusFlag'] = 'news/db/statusflag.php';

\OC::$CLASSPATH['OCA\News\NewsController'] = 'news/controller/news.controller.php';
\OC::$CLASSPATH['OCA\News\NewsAjaxController'] = 'news/controller/news.ajax.controller.php';


/**
 * @return a new DI container with prefilled values for the news app
 */
function createDIContainer(){
	$newsContainer = new \Pimple();

	/** 
	 * CONSTANTS
	 */
	$newsContainer['AppName'] = 'news';


	/** 
	 * CLASSES
	 */
	$newsContainer['API'] = $newsContainer->share(function($c){
		return new API($c['AppName']);
	});


	$newsContainer['Request'] = $newsContainer->share(function($c){
		return new Request($_GET, $_POST, $_FILES);
	});


	$newsContainer['Security'] = $newsContainer->share(function($c) {
		return new Security($c['AppName']);	
	});


	/** 
	 * MAPPERS
	 */
	$newsContainer['ItemMapper'] = $newsContainer->share(function($c){
		return new ItemMapper($c['API']->getUserId());
	});

	$newsContainer['FeedMapper'] = $newsContainer->share(function($c){
		return new FeedMapper($c['API']->getUserId());
	});

	$newsContainer['FolderMapper'] = $newsContainer->share(function($c){
		return new FolderMapper($c['API']->getUserId());
	});


	/** 
	 * CONTROLLERS
	 */
	$newsContainer['NewsController'] = function($c){
		return new NewsController($c['Request'], $c['API'], $c['FeedMapper'], 
									$c['FolderMapper']);
	};

	$newsContainer['NewsAjaxController'] = function($c){
		return new NewsAjaxController($c['Request'], $c['API'], $c['FeedMapper'], 
										$c['FolderMapper'], $c['ItemMapper']);
	};

	return $newsContainer;
}
