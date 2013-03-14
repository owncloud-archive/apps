<?php

require_once '3rdparty/php-lib-remote-rs/lib/OAuth/RemoteResourceServer.php';

use \OAuth\RemoteResourceServer as RemoteResourceServer;
use \OAuth\RemoteResourceServerException as RemoteResourceServerException;

class OC_Connector_Sabre_OAuth implements Sabre_DAV_Auth_IBackend
{
    private $currentUser;
    private $tokenInfoEndpoint;
    private $useResourceOwnerId;
    private $userIdAttributeName;

    public function __construct($tokenInfoEndpoint, $useResourceOwnerId = TRUE, $userIdAttributeName = "uid")
    {
        $this->tokenInfoEndpoint = $tokenInfoEndpoint;
        $this->useResourceOwnerId = $useResourceOwnerId;
        $this->userIdAttributeName = $userIdAttributeName;
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function authenticate(Sabre_DAV_Server $server, $realm)
    {
        $config = array(
            "tokenInfoEndpoint" => $this->tokenInfoEndpoint,
            "throwException" => TRUE,
            "resourceServerRealm" => $realm,
        );

        try {
            $resourceServer = new RemoteResourceServer($config);

            $resourceServer->verifyRequest();

            if ($this->useResourceOwnerId) {
                // when using the user_id
                $this->currentUser = $resourceServer->getResourceOwnerId();
            } else {
                // when using a (SAML) attribute
                $attributes = $resourceServer->getAttributes();
                $this->currentUser = $attributes[$this->userIdAttributeName][0];
            }

            OC_Util::setupFS($this->currentUser);

            return true;

        } catch (RemoteResourceServerException $e) {
            $server->httpResponse->setHeader('WWW-Authenticate', $e->getAuthenticateHeader());

            // FIXME: do we need to set the status here explicitly, or does the
            // Exception below take care of this?
            $server->httpResponse->sendStatus($e->getResponseCode());
            if ("403" === $e->getResponseCode()) {
                throw new Sabre_DAV_Exception_Forbidden($e->getDescription());
            } else {
                throw new Sabre_DAV_Exception_NotAuthenticated($e->getDescription());
            }
        }
    }

}
