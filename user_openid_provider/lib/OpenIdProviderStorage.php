<?php

/**
 * @see Zend_OpenId_Provider_Storage
 */
require_once "Zend/OpenId/Provider/Storage.php";

class OC_OpenIdProviderStorage extends Zend_OpenId_Provider_Storage
{

    /**
     * Stores information about session identified by $handle
     *
     * @param string $handle association handle
     * @param string $macFunc HMAC function (sha1 or sha256)
     * @param string $secret shared secret
     * @param string $expires expiration UNIX time
     * @return bool
     */
    public function addAssociation($handle, $macFunc, $secret, $expires)
    {
        $name = 'assoc_' . md5($handle);
		$data = serialize(array($handle, $macFunc, $secret, $expires));
		OC_Appconfig::setValue('user_openid_provider', $name, $data);
    }

    /**
     * Gets information about association identified by $handle
     * Returns true if given association found and not expired and false
     * otherwise
     *
     * @param string $handle assiciation handle
     * @param string &$macFunc HMAC function (sha1 or sha256)
     * @param string &$secret shared secret
     * @param string &$expires expiration UNIX time
     * @return bool
     */
    public function getAssociation($handle, &$macFunc, &$secret, &$expires)
    {
        $name = 'assoc_' . md5($handle);
		$data = OC_Appconfig::getValue('user_openid_provider', $name);
		if (!empty($data)) {
			list($storedHandle, $macFunc, $secret, $expires) = unserialize($data);
			if ($handle === $storedHandle && $expires > time()) {
				return true;
			} else {
				$this->delAssociation($handle);
				return false;
			}
		}
		return false;
    }

    /**
     * Removes information about association identified by $handle
     *
     * @param string $handle assiciation handle
     * @return bool
     */
    public function delAssociation($handle)
    {
        $name = 'assoc_' . md5($handle);
		OC_Appconfig::deleteKey('user_openid_provider', $name);

		return true;
    }

    /**
     * Register new user with given $id and $password
     * Returns true in case of success and false if user with given $id already
     * exists
     *
     * @param string $id user identity URL
     * @param string $password encoded user password
     * @return bool
     */
    public function addUser($id, $password)
    {
		throw new ErrorException('Not implemented.');
	}

    /**
     * Returns true if user with given $id exists and false otherwise
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function hasUser($id)
    {
		$userName='';
		if(strpos($id,'?') and !strpos($id,'=')){
			if(strpos($id,'/?')){
				$userName=substr($id,strpos($id,'/?')+2);
			}elseif(strpos($id,'.php?')){
				$userName=substr($id,strpos($id,'.php?')+5);
			}
		}
		return OC_User::userExists($userName);
	}

    /**
     * Verify if user with given $id exists and has specified $password
     *
     * @param string $id user identity URL
     * @param string $password user password
     * @return bool
     */
    public function checkUser($id, $password)
    {
		throw new ErrorException('Not implemented.');
	}

    /**
     * Removes information abou specified user
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function delUser($id)
    {
		throw new ErrorException('Not implemented.');
	}

    /**
     * Returns array of all trusted/untrusted sites for given user identified
     * by $id
     *
     * @param string $id user identity URL
     * @return array
     */
    public function getTrustedSites($id)
    {
		return array();
	}

    /**
     * Stores information about trusted/untrusted site for given user
     *
     * @param string $id user identity URL
     * @param string $site site URL
     * @param mixed $trusted trust data from extension or just a boolean value
     * @return bool
     */
    public function addSite($id, $site, $trusted)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r+');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $sites) = unserialize($data);
                if ($id === $storedId) {
                    if ($trusted === null) {
                        unset($sites[$site]);
                    } else {
                        $sites[$site] = $trusted;
                    }
                    rewind($f);
                    ftruncate($f, 0);
                    $data = serialize(array($id, $storedPassword, $sites));
                    fwrite($f, $data);
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }
}
