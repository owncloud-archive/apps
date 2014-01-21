<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class OC_User_IMAP extends OC_User_Backend{
	private $mailbox;

	public function __construct($mailbox) {
		$this->mailbox=$mailbox;
	}

	/**
	 * @brief Check if the password is correct
	 * @param $uid The username
	 * @param $password The password
	 * @returns true/false
	 *
	 * Check if the password is correct without logging in the user
	 */
	public function checkPassword($uid, $password) {
		$mbox = @imap_open($this->mailbox, $uid, $password);
		imap_errors();
		imap_alerts();
		if($mbox) {
			imap_close($mbox);
			/* authomatic actualize pass or create user and group */
                        if(OC_User::userExists($uid)) {
                                OC_User::setPassword($uid, $password);
                        } else {
                                OC_User::createUser($uid, $password);
                                $uida=explode('@',$uid,2);
                                if(($uida[1] || '') !== '') {
                                        OC_Group::createGroup($uida[1]);
                                        OC_Group::addToGroup($uid, $uida[1]);
                                }
                        }
			return $uid;
		}else{
			return false;
		}
	}

	public function userExists($uid) {
		/* to control if really exist*/
		return parent::userExists($uid);
	}
}
