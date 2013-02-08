<?php

/**
 * ownCloud
 *
 * @author Frank Karlitschek
 * @author Jakob Sack
 * @copyright 2012 Frank Karlitschek frank@owncloud.org
 * @copyright 2011 Jakob Sack kde@jakobsack.de
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
// only need filesystem apps
$RUNTIME_APPTYPES=array('filesystem','authentication');
OC_App::loadApps($RUNTIME_APPTYPES);

$tokenInfoEndpoint = \OC_Config::getValue( "tokenInfoEndpoint", "https://www.googleapis.com/oauth2/v1/tokeninfo" );
$useResourceOwnerId = TRUE;     // FIXME: take this from configuration instead
$userIdAttributeName = "uid";   // FIXME: take this from configuration instead

require_once "oauth.php";

// Backends
$authBackend = new OC_Connector_Sabre_OAuth($tokenInfoEndpoint, $useResourceOwnerId, $userIdAttributeName);
$lockBackend = new OC_Connector_Sabre_Locks();
$requestBackend = new OC_Connector_Sabre_Request();

// Create ownCloud Dir
$publicDir = new OC_Connector_Sabre_Directory('');

// Fire up server
$server = new Sabre_DAV_Server($publicDir);
$server->httpRequest = $requestBackend;
$server->setBaseUri($baseuri);

// Load plugins
$server->addPlugin(new Sabre_DAV_Auth_Plugin($authBackend,'ownCloud'));
$server->addPlugin(new Sabre_DAV_Locks_Plugin($lockBackend));
$server->addPlugin(new Sabre_DAV_Browser_Plugin(false)); // Show something in the Browser, but no upload

// And off we go!
$server->exec();
