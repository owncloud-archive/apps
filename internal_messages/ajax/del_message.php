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

$id = isset($_POST['id']) ? $_POST['id'] : false;
$folder = isset($_POST['folder']) ? $_POST['folder'] : false;

if ( isset($folder) ) {
    OC_INT_MESSAGES::delMessage( $id , $folder ) ;
    OCP\JSON::success(array('data' => array('status' => 'The message was removed..' )));
}
