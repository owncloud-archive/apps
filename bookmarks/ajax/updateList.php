<?php

/**
* ownCloud - bookmarks plugin
*
* @author Arthur Schiwon
* @copyright 2011 Arthur Schiwon blizzz@arthur-schiwon.de
* @copyright 2012 David Iwanowitsch <david at unclouded dot de>
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('bookmarks');

$req_type= isset($_GET['type']) ? $_GET['type'] : 'bookmark';

if($req_type == 'rel_tags') {
	$tags = OC_Bookmarks_Bookmarks::analyzeTagRequest(isset($_GET['tag']) ? $_GET['tag'] : '');
	$qtags = OC_Bookmarks_Bookmarks::findTags($tags);
	OCP\JSON::success(array('data' => $qtags));

}
else { // type == bookmark
	$filterTag = OC_Bookmarks_Bookmarks::analyzeTagRequest(isset($_GET['tag']) ? $_GET['tag'] : '');

	$offset = isset($_GET['page']) ? intval($_GET['page']) * 10 : 0;

	$sort = isset($_GET['sort']) ? ($_GET['sort']) : 'bookmarks_sorting_recent';
	if($sort == 'bookmarks_sorting_clicks') {
		$sqlSortColumn = 'clickcount';
	} else {
		$sqlSortColumn = 'lastmodified';
	}
	$bookmarks = OC_Bookmarks_Bookmarks::findBookmarks($offset, $sqlSortColumn, $filterTag, true);
	OCP\JSON::success(array('data' => $bookmarks));

}
