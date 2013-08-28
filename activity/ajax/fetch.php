<?php

/**
* ownCloud - Activity App
*
* @author Frank Karlitschek
* @copyright 2013 Frank Karlitschek frank@owncloud.org
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


// some housekeeping
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('activity');


// read the next 30 items for the endless scrolling
$activity=OCA\Activity\Data::read(0,30);

// show the next 30 entries including the container that is needed for the endless scrolling
echo('<div id="container" class="transitions-enabled infinite-scroll clearfix">');
foreach($activity as $event) {
	OCA\Activity\Data::show($event);
}
echo('</div>');


// a dummy page navigation that is needed for the endless scrolling
echo('
<nav id="page-nav">
  <a href="'.$_['nextpage'].'">next</a>
</nav>

');


