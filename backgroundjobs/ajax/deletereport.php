<?php
/**
* ownCloud - Background Job
*
* @author Jakob Sack
* @copyright 2011 Jakob Sack owncloud@jakobsack.de
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

// Init owncloud
require_once('../../../lib/base.php');

$id = $_POST['id'];
$l10n = new OC_L10N('backgroundjobs');

// Check if we are a user
OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('backgroundjobs');
OC_JSON::checkAdminUser();

OC_Backgroundjobs_Report::delete($id);

OC_JSON::success(array('data' => array( 'id' => $id )));
