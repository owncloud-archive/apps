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
    /**
     * Loads all user's accounts, connects to each server and queries all folders
     *
     * @return array Folder list
     */
    public static function getFolders()
    {
        $response = array();

        // get all account configured by the user
        $accounts = OC_Mail::getAccounts();

        // iterate ...
        foreach ($accounts as $account) {
            $folders_out = array();

            // open the imap connection
            $conn = OC_Mail::getImapConnection($account);

            // if successfull -> get all folders of that account
            if ($conn->errornum == rcube_imap_generic::ERROR_OK) {
                $mboxes = $conn->listMailboxes('', '*');

                foreach ($mboxes as $folder) {
                    $status = $conn->status($folder);
                    $folders_out[] = array('id' => $folder, 'name' => end(explode('.', $folder)), 'unseen' => $status['UNSEEN'], 'total' => $status['MESSAGES']);
                }
            }

            $response[]= array('id' => $account['id'], 'name' => $account['id'], 'folders' => $folders_out, 'error' => $conn->error);

            // close the connection
            $conn->closeConnection();
        }

        return $response;
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


    private static function getImapConnection($account)
    {
        //
        // TODO: add singleton pattern - ???
        //
        $host = $account['host'];
        $user = $account['user'];
        $password = $account['passward'];
        $port = $account['port'];
        $ssl_mode = $account['ssl_mode'];

        // connect to
        $conn = new rcube_imap_generic();
        $conn->connect($host, $user, $password, array('port' => $port, 'ssl_mode' => $ssl_mode, 'timeout' => 60));

        return $conn;
    }

    private static function getAccounts()
    {
        //
        // TODO: user config missing
        //
        $a0 = array('id' => 0,
            'name' => 'bob@owncloud.org',
            'host' => 'darwin.rheno-borussia.rwth-aachen.de',
            'user' => 'tom',
            'password' => 'baumhaus',
            'port' => 993,
            'ssl_mode' => 'ssl');

        $a1 = array('id' => 1,
            'name' => 'alice@owncloud.org',
            'host' => 'darwin.rheno-borussia.rwth-aachen.de',
            'user' => 'tom',
            'password' => 'baumhaus',
            'port' => 993,
            'ssl_mode' => 'ssl');
        return array($a0, $a1);
    }

}

