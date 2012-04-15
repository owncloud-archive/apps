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
     * @static
     * @return array
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


                usort($folders_out, function ($a, $b)
                {
                    return strcmp($a['id'], $b['id']);
                });
            }

            $response[] = array('id' => $account['id'], 'name' => $account['id'], 'folders' => $folders_out, 'error' => $conn->error);

            // close the connection
            $conn->closeConnection();
        }

        return $response;
    }

    /**
     * @static
     * @param $account_id
     * @param $folder_id
     * @param int $from
     * @param int $count
     * @return array
     */
    public static function getMessages($account_id, $folder_id, $from = 0, $count = 20)
    {
        // get the account
        $account = OC_Mail::getAccount($account_id);
        if (!$account) {
            #TODO: i18n
            return array('error' => 'unknown account');
        }

        // connect to the imal server
        $conn = OC_Mail::getImapConnection($account);

        $messages = array();
        if ($conn->errornum == rcube_imap_generic::ERROR_OK) {

            $total = $conn->countMessages($folder_id);

            if (($from + $count) > $total) {
                $count = $total - $from;
            }

            $headers = $conn->fetchHeaders($folder_id, ($from + 1) . ':' . ($from + $count));
            foreach ($headers as $header) {
                //                $flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);

                $flags = array();
                $messages[] = array('id' => $header->id, 'from' => $header->from, 'to' => $header->to, 'subject' => $header->subject, 'date' => $header->timestamp, 'size' => $header->size, 'flags' => $flags);
            }
        }

        return array('account_id' => $account_id, 'folder_id' => $folder_id, 'messages' => $messages, 'error' => $conn->error);
    }

    /**
     * @static
     * @param $account_id
     * @param $folder_id
     * @param $message_id
     * @return array
     */
    public static function getMessage($account_id, $folder_id, $message_id)
    {
        // get the account
        $account = OC_Mail::getAccount($account_id);
        if (!$account) {
            #TODO: i18n
            return array('error' => 'unknown account');
        }

        // connect to the imal server
        $conn = OC_Mail::getImapConnection($account);

        if ($conn->errornum == rcube_imap_generic::ERROR_OK) {

            $flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);

            $message = array(
                'from' => 'alice@owncloud.org', 'to' => 'bob@owncloud.org', 'subject' => 'Hello Bob!', 'date' => time(), 'size' => 123 * 1024, 'flags' => $flags,
                'body' => 'Hi Bob,\n how are you?\n\n Greetings, Alice',
                'attachments' => array(),
                'header' => 'TODO: add the header'
            );
        }

        return array('error' => 'unknown account', 'message' => $message);
    }

    private static function getImapConnection($account)
    {
        //
        // TODO: add singleton pattern - ???
        //
        $host = $account['host'];
        $user = $account['user'];
        $password = $account['password'];
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

    private static function getAccount($account_id)
    {
        $accounts = OC_Mail::getAccounts();
        foreach ($accounts as $account) {
            if ($account['id'] == $account_id) {
                return $account;
            }
        }

        return false;
    }

}
