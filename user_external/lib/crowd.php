<?php
/**
 * This is Atlassian Crowd Authentication for ownCloud inspired by
 * the OC_User_IMAP Class written by Robin Appelman
 *
 * Copyright (C) 2015 Christian Bönning <christian.boenning@wmdb.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use Guzzle\Http\Client;

/**
 * User authentication against an Atlassian Crowd Server using REST
 *
 * @category Apps
 * @package  UserExternal
 * @author   Christian Bönning <christian.boenning@wmdb.de>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
class OC_User_AtlasCrowd extends \OCA\user_external\Base
{
    private $host;
    private $secure;
    private $protocol;
    private $crowdApplicationName;
    private $crowdApplicationPassword;
    private $crowdServiceUri;

    /**
     * Create new Atlassian Crowd authentication provider
     *
     * @param string   $host            Hostname or IP of Crowd Server
     * @param boolean  $secure          Set to `true` to enable SSL
     * @param string   $cwdUri          The Crowd Service URI (usually /crowd)
     * @param string   $cwdAppName      The Crowd Application Name as configured on Crowd Console
     * @param string   $cwdAppPassword  The Crowd Application Password
     */
    public function __construct(
        $host = '127.0.0.1:8095',
        $secure = false,
        $cwdUri = '/crowd',
        $cwdAppName = null,
        $cwdAppPassword = null
    ) {
        $this->host = $host;
        $this->secure = $secure;
        $this->protocol = (bool)$this->secure ? 'https' : 'http';
        $this->crowdApplicationName = trim($cwdAppName);
        $this->crowdApplicationPassword = trim($cwdAppPassword);
        $this->crowdServiceUri = $cwdUri;

        parent::__construct($this->protocol . '://' . $this->host . $this->crowdServiceUri);
    }

    /**
     * Check if the password is correct without logging in the user
     *
     * @param  string  $uid         The username
     * @param  string  $password    The password
     *
     * @return mixed   the uid on success, false otherwise
     */
    public function checkPassword($uid, $password)
    {
        $uid = strtolower($uid);

        try {
            $client = new Client();

            $serviceUrl = sprintf(
                '%s://%s%s/rest/usermanagement/1/authentication?username=%s',
                $this->protocol,
                $this->host,
                rtrim($this->crowdServiceUri, '/'),
                $uid
            );

            $request = $client->post(
                $serviceUrl,
                array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ),
                json_encode(array('value' => $password)),
                array(
                    'auth' => array(
                        $this->crowdApplicationName,
                        $this->crowdApplicationPassword
                    )
                )
            );

            $response = $request->send();
            $rspBody = json_decode($response->getBody(true), true);
            if (json_last_error() != JSON_ERROR_NONE) {
                OCP\Util::writeLog(
                    'user_external',
                    'ERROR: Atlassian Crowd returned something which is probably not JSON.',
                    OCP\Util::ERROR
                );

                return false;
            }

            if ($response->getStatusCode() === 200) {
                $this->storeUser($uid);
                // set Display Name (from Crowd) if we've set one
                if (strlen($rspBody['display-name']) > 0) {
                    $this->setDisplayName($uid, $rspBody['display-name']);
                }

                // set User E-Mail (from Crowd) if it's valid
                if (filter_var($rspBody['email'], FILTER_VALIDATE_EMAIL)) {
                    \OC::$server->getConfig()->setUserValue(
                        $uid,
                        'settings',
                        'email',
                        $rspBody['email']
                    );
                }

                OCP\Util::writeLog(
                    'user_external',
                    sprintf(
                        'INFO: User "%s" from Atlassian Crowd Logged in',
                        $rspBody['name']
                    ),
                    OCP\Util::INFO
                );

                return $uid;
            } elseif ($response->getStatusCode() === 400) {
                OCP\Util::writeLog(
                    'user_external',
                    sprintf(
                        'ERROR: Atlassian Crowd returned Status 400 with Message "%s"',
                        $rspBody['message']
                    ),
                    OCP\Util::ERROR
                );

                return false;
            } else {
                OCP\Util::writeLog(
                    'user_external',
                    sprintf(
                        'ERROR: Atlassian Crowd returned Status %s with Message "%s"',
                        $response->getStatusCode(),
                        $response->getBody()
                    ),
                    OCP\Util::ERROR
                );

                return false;
            }
        } catch (Exception $e) {
            OCP\Util::writeLog(
                'user_external',
                sprintf('ERROR: Error talking to Atlassian Crowd: %s', $e->getMessage()),
                OCP\Util::ERROR
            );

            return false;
        }
    }
}
