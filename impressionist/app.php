<?php

/**
 * ownCloud - Impressionist & Impress App
 *
 * @author Raghu Nayyar & Frank Karlitschek
 * @copyright 2012 me@iraghu.com Frank Karlitschek karlitschek@kde.org
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

require_once 'lib/impressionist.php';

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('impressionist');
OCP\App::setActiveNavigationEntry( 'impressionist_index' );

OCP\Util::addStyle('impressionist', 'mainstyle');
OCP\Util::addStyle('impressionist', 'matrices');
OCP\Util::addStyle('impressionist', 'colorpicker');
OCP\Util::addStyle('impressionist', 'layout');
OCP\Util::addStyle('impressionist', 'bootstrap');


OCP\Util::addScript('impressionist', 'keymaster');
OCP\Util::addScript('impressionist', 'impress');
OCP\Util::addScript('impressionist', 'datastore');
OCP\Util::addScript('impressionist', 'appui');
OCP\Util::addScript('impressionist', 'fileops');
OCP\Util::addScript('impressionist', 'templ');
OCP\Util::addScript('impressionist', 'knobdial');
OCP\Util::addScript('impressionist', 'main');
OCP\Util::addScript('impressionist', 'colorpicker');
OCP\Util::addScript('impressionist', 'matrices');
OCP\Util::addScript('impressionist', 'raphael');
OCP\Util::addScript('impressionist', 'freetransform');
OCP\Util::addScript('impressionist', 'jqueryui');
OCP\Util::addScript('impressionist', 'bootstrap');
OCP\Util::addScript('impressionist', 'advanced');
OCP\Util::addScript('impressionist', 'wysihtml5-0.3.0');


$tmpl = new OCP\Template('impressionist', 'app', 'user');
$tmpl->printPage();
