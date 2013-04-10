<?php

/**
 * ownCloud - internal_messages
 *
 * @author Jorge Rafael García Ramos
 * @copyright 2012 Jorge Rafael García Ramos <kadukeitor@gmail.com>
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

OCP\JSON::checkAppEnabled('internal_messages');
OCP\JSON::checkLoggedIn();

$groupConvId =  isset($_POST['groupConvId']) ? $_POST['groupConvId'] : 0;
$msgto = isset($_POST['msgto']) ? $_POST['msgto'] : NULL;
$msgcontent = isset($_POST['msgcontent']) ? OCP\UTIL::sanitizeHTML($_POST['msgcontent']) : false;


if ($msgcontent) {
    if ( OC_INT_MESSAGES::sendMessage( OCP\USER::getUser() , $msgto , $msgcontent ," ", $groupConvId ) ) {
        OCP\JSON::success(array('data' => array('message' => 'The message has been sent..' )));
    } else {
        OCP\JSON::error(array('data' => array( 'message' => "error when trying to sent the message...\ncheck the sender and the content..")));
    }
} else {
    OCP\JSON::error(array('data' => array( 'message' => "error when trying to sent the message..." )));
}
