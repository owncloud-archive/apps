<?php

use fkooman\oauth\rs\RemoteResourceServer;
use fkooman\oauth\rs\RemoteResourceServerException;

class OC_Connector_Sabre_OAuth implements Sabre_DAV_Auth_IBackend
{
    private $currentUser;
    private $introspectionEndpoint;

    public function __construct($introspectionEndpoint)
    {
        $this->introspectionEndpoint = $introspectionEndpoint;
        $this->currentUser = null;
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function authenticate(Sabre_DAV_Server $server, $realm)
    {
        $config = array(
            "introspectionEndpoint" => $this->introspectionEndpoint,
            "realm" => $realm
        );

        try {
            $resourceServer = new RemoteResourceServer($config);
            $tokenIntrospection = $resourceServer->verifyRequest(apache_request_headers(), $_GET);
            $this->currentUser = $tokenIntrospection->getSub();

            OC_User::setUserid($this->currentUser);
            OC_Util::setupFS($this->currentUser);

            return true;
        } catch (RemoteResourceServerException $e) {
            switch ($e->getMessage()) {
                case "insufficient_entitlement":
                case "insufficient_scope":
                    $server->httpResponse->setHeader('WWW-Authenticate', $e->getAuthenticateHeader());
                    throw new Sabre_DAV_Exception_Forbidden($e->getDescription());
                case "invalid_request":
                    throw new Sabre_DAV_Exception_NotAuthenticated($e->getDescription());
                case "invalid_token":
                case "no_token":
                    $server->httpResponse->setHeader('WWW-Authenticate', $e->getAuthenticateHeader());
                    throw new Sabre_DAV_Exception_NotAuthenticated($e->getDescription());
                case "internal_server_error":
                    throw new Sabre_DAV_Exception($e->getDescription());
            }
        }
    }
}
