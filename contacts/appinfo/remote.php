<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
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

OCP\App::checkAppEnabled('contacts');

if(substr(OCP\Util::getRequestUri(), 0, strlen(OC_App::getAppWebPath('contacts').'/carddav.php')) == OC_App::getAppWebPath('contacts').'/carddav.php') {
	$baseuri = OC_App::getAppWebPath('contacts').'/carddav.php';
}

// only need authentication apps
$RUNTIME_APPTYPES=array('authentication');
OC_App::loadApps($RUNTIME_APPTYPES);

// Backends
$authBackend = new OC_Connector_Sabre_Auth();
$principalBackend = new OC_Connector_Sabre_Principal();
$carddavBackend   = new OC_Connector_Sabre_CardDAV();
$requestBackend = new OC_Connector_Sabre_Request();

// Root nodes
$principalCollection = new Sabre_CalDAV_Principal_Collection($principalBackend);
$principalCollection->disableListing = true; // Disable listening

$addressBookRoot = new OC_Connector_Sabre_CardDAV_AddressBookRoot($principalBackend, $carddavBackend);
$addressBookRoot->disableListing = true; // Disable listening

$nodes = array(
	$principalCollection,
	$addressBookRoot,
	);

// Fire up server
$server = new Sabre_DAV_Server($nodes);
$server->httpRequest = $requestBackend;
$server->setBaseUri($baseuri);
// Add plugins
$server->addPlugin(new Sabre_DAV_Auth_Plugin($authBackend, 'ownCloud'));
$server->addPlugin(new Sabre_CardDAV_Plugin());
$server->addPlugin(new Sabre_DAVACL_Plugin());
$server->addPlugin(new Sabre_DAV_Browser_Plugin(false)); // Show something in the Browser, but no upload
$server->addPlugin(new Sabre_CardDAV_VCFExportPlugin());

// And off we go!
$server->exec();
