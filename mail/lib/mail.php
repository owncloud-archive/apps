<?php

//require_once('3rdparty/rcube_imap.php');
require_once('3rdparty/rcube_imap_generic.php');

/**
 * ownCloud - Mail app
 *
 * @author Thomas Müller
 * @copyright 2012 Thomas Müller thomas.mueller@tmit.eu
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

class OC_Mail
{

    public static function getFolders()
    {
        $conn= OC_Mail::getImapConnection();
        $mboxes= $conn->listMailboxes('', '*');


        $folders_out = array();

        foreach ($mboxes as $folder) {
            $status= $conn->status($folder);
            $folders_out[] = array('id' => $folder, 'name' => end(explode('.', $folder)), 'unseen' => $status['UNSEEN'], 'total' => $status['MESSAGES']);
        }
//        $folders_out[] = array('id' => 'INBOX', 'name' => 'Posteingang', 'unseen' => 5, 'total' => 20);
//        $folders_out[] = array('id' => 'Draft', 'name' => 'Vorlangen', 'unseen' => 0, 'total' => 5);

        $account = array('id' => '0', 'name' => 'alice@owncloud.org', 'folders' => $folders_out);

        return array($account);
    }

    public static function getMessages($account_id, $folder_id, $from = 0, $count = 20)
    {

        $messages = array();
        //OC_Util::formatDate($this->header->udate)
        for ($i = 1; $i <= $count; $i++) {
            $flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);
            $messages[] = array('id' => $from + $i, 'from' => 'alice@owncloud.org', 'to' => 'bob@owncloud.org', 'subject' => 'Hello, World!', 'date' => time(), 'size' => 123 * 1024, 'flags' => $flags);
        }

        return array('account_id' => $account_id, 'folder_id' => $folder_id, 'messages' => $messages);
    }

    public static function getMessage($account_id, $folder_id, $message_id)
    {

        $flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);

        $message = array(
            'from' => 'alice@owncloud.org', 'to' => 'bob@owncloud.org', 'subject' => 'Hello Bob!', 'date' => time(), 'size' => 123 * 1024, 'flags' => $flags,
            'body' => 'Hi Bob,\n how are you?\n\n Greetings, Alice',
            'attachments' => array(),
            'header' => 'TODO: add the header'
        );

        return $message;
    }


    private static function getImapConnection(){
        //
        // TODO: add singleton pattern
        //
        //
        // TODO: user config missing
        //
        $host = 'darwin.rheno-borussia.rwth-aachen.de';
        $user = 'tom';
        $password = 'baumhaus';
        $port = 993;

        // connect to
        $conn = new rcube_imap_generic();
        $conn->connect($host, $user, $password, array('port' => $port, 'ssl_mode' => 'ssl', 'timeout' => 60));

        return $conn;
    }

}

