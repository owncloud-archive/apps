<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * User authentication against an IMAP mail server
 *
 * @category Apps
 * @package  UserExternal
 * @author   Robin Appelman <icewind@owncloud.com>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
class OC_User_IMAP extends \OCA\user_external\Base {
	private $mailbox;
	private $domain;

	/**
	 * Create new IMAP authentication provider
	 *
	 * @param string $mailbox PHP imap_open mailbox definition, e.g.
	 *                        {127.0.0.1:143/imap/readonly}
	 * @param string $domain (optional) the domain users are checked against
	 *                        users can then log in only with their local name part (before "@")
	 */
	public function __construct($mailbox, $domain = '') {
		parent::__construct($mailbox);
		$this->mailbox=$mailbox;
		$this->domain=$domain;
	}

	/**
	 * Check if the password is correct without logging in the user
	 *
	 * @param string $uid      The username
	 * @param string $password The password
	 *
	 * @return true/false
	 */
	public function checkPassword($uid, $password) {
		if (!function_exists('imap_open')) {
			OCP\Util::writeLog('user_external', 'ERROR: PHP imap extension is not installed', OCP\Util::ERROR);
			return false;
		}

		// Check if we only want logins from ONE domain and strip the domain part from UID		
		if($this->domain != '') {
			$pieces = explode('@', $uid);
			if(count($pieces) == 1) {
                // user did not enter domain part
				$username = $uid . "@" . $this->domain;
                $displayname=$uid;
                $domain=$this->domain;
			}elseif((count($pieces) == 2) and ($pieces[1] == $this->domain)) {
				// user did enter domain part
				$username = $uid;
				$uid = $pieces[0];
                $displayname=$pieces[0];
                $domain=$pieces[1];
			}else{
				return false;
			}
            
		}else{
            // check for multiple domains
			$username = $uid;

            $pieces = explode('@', $uid);
            if(count($pieces) == 1) {
            	$displayname= $uid;
            	$domain="";            	
            } else {
            	$displayname= $pieces[0];
            	$domain=$pieces[1];
            }
		}

		OCP\Util::writeLog('user_external', "username: $username, displayname $displayname domain: $domain"
				, OCP\Util::INFO);
		
		$mbox = @imap_open($this->mailbox, $username, $password, OP_HALFOPEN, 1);
		imap_errors();
		imap_alerts();
		if($mbox === FALSE) {
			imap_close($mbox);
			return false;			
		}
		
		imap_close($mbox);
		$uid = mb_strtolower($uid);
		$this->storeUser($uid);

        //// store extra information based on the email address

        // set email adress, if not already defined
        $userManager=\OC::$server->getUserManager();
        $user=$userManager->get($uid);
        $currentEmail = $user->getEMailAddress();
        if ($currentEmail == "") {
            $user->setEMailAddress($username);
        }
             
        // set Display name, if not already defined
        $currentDisplayname=$this->getDisplayName($uid) ;
        if ($currentDisplayname == "" || $currentDisplayname == $username) {
        	$displayname=strtr($displayname, ".", " ");
            $this->setDisplayName($uid, $displayname) ;
        }
            
        if($this->domain == '' && $domain != '') {
            // create group from domain name, add user to group
            $groupManager = \OC::$server->getGroupManager();
            // drop TLD from domain
            $domain= preg_replace('/\..*?$/', '', $domain);
            OCP\Util::writeLog('user_external', "domain: $domain"
            		, OCP\Util::ERROR);
            $group = $groupManager->createGroup($domain);
            $group->addUser($user);
        }

        return $uid;		
	}
}
