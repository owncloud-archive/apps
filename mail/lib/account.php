<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Mail;

class Account {
	private $info;

	// input $conn = IMAP conn, $folder_id = folder id
	function __construct($info) {
		$this->info = $info;
	}

	public function getId() {
		return $this->info['id'];
	}

	public function getName() {
		return $this->info['name'];
	}

	public function getEMailAddress() {
		return $this->info['email'];
	}

	public function getImapConnection() {
		//
		// TODO: cache connections for / within accounts???
		//
		$host = $this->info['host'];
		$user = $this->info['user'];
		$password = $this->info['password'];
		$port = $this->info['port'];
		$ssl_mode = $this->info['ssl_mode'];

		$client = new \Horde_Imap_Client_Socket(array(
			'username' => $user, 'password' => $password, 'hostspec' => $host, 'port' => $port, 'secure' => $ssl_mode, 'timeout' => 2));
		$client->login();
		return $client;
	}

	/**
	 * @param $pattern
	 * @return Mailbox[]
	 */
	public function listMailboxes($pattern) {
		// open the imap connection
		$conn = $this->getImapConnection();

		// if successful -> get all folders of that account
		$mboxes = $conn->listMailboxes($pattern);
		$mailboxes = array();
		foreach ($mboxes as $mailbox) {
			$mailboxes[] = new Mailbox($conn, $mailbox['mailbox']->utf7imap);
		}
		return $mailboxes;
	}

	/**
	 * @param $folder_id
	 * @return Mailbox
	 */
	public function getMailbox($folder_id) {
		$conn = $this->getImapConnection();
		return new Mailbox($conn, $folder_id);
	}

	/**
	 * @return array
	 */
	public function getListArray() {
		// if successful -> get all folders of that account
		$mboxes = $this->listMailboxes('*');

		$folders = array();
		foreach ($mboxes as $mailbox) {
			$folders[] = $mailbox->getListArray();
		}

		usort($folders, function ($a, $b) {
			return strcmp($a['id'], $b['id']);
		});

		return array('id' => $this->getId(), 'name' => $this->getName(), 'folders' => $folders);
	}
}
